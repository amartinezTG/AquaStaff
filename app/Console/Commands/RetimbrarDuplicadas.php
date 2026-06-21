<?php

namespace App\Console\Commands;

use App\Models\GeneralCatalogs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Re-timbra las 5 transacciones que quedaron sin CFDI tras limpiar los
 * UUID duplicados (causados por el folio fijo AB100). Datos del receptor
 * recuperados de los XML guardados. Un solo uso, controlado y aislado.
 */
class RetimbrarDuplicadas extends Command
{
    protected $signature = 'facturas:retimbrar-duplicadas {--dry-run : Muestra qué haría sin llamar al SAT}';
    protected $description = 'Re-timbra las transacciones afectadas por el UUID duplicado (folio AB100)';

    protected $catalogs;

    // Receptores recuperados de los XML guardados
    private array $cuentas = [
        'DIAZGAS' => [
            'fiscal_account_id' => 105,
            'rfc'               => 'DGA930823KD3',
            'company_name'      => 'DIAZ GAS',
            'tax_regime'        => '601',
            'cfdi_use'          => 'G03',
            'zip_code'          => '32030',
        ],
        'DIVOC' => [
            'fiscal_account_id' => 126,
            'rfc'               => 'CDI2009019T0',
            'company_name'      => 'COMERCIALIZADORA DIVOC',
            'tax_regime'        => '601',
            'cfdi_use'          => 'G03',
            'zip_code'          => '32470',
        ],
    ];

    // local_transaction_id => cliente
    private array $transacciones = [
        247615 => 'DIAZGAS',
        247994 => 'DIAZGAS',
        248028 => 'DIAZGAS',
        191915 => 'DIAZGAS',
        248463 => 'DIVOC',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->catalogs = new GeneralCatalogs();
    }

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        // Folio inicial: continúa la secuencia real existente
        $maxInd    = (int) DB::table('individual_invoices')->where('serie', 'AB')->max(DB::raw('CAST(folio AS UNSIGNED)'));
        $maxGlb    = (int) DB::table('global_invoice')->where('serie', 'AB')->max(DB::raw('CAST(folio AS UNSIGNED)'));
        $nextFolio = max($maxInd, $maxGlb, 100) + 1;

        $this->info("Folio inicial: AB{$nextFolio}");

        if (!$dry) {
            $tokenResult = $this->obtenerToken();
            if (!$tokenResult['token']) {
                $this->error('No se pudo obtener token: ' . json_encode($tokenResult['error']));
                return self::FAILURE;
            }
            $token = $tokenResult['token'];
        }

        $resultados = [];

        foreach ($this->transacciones as $ltId => $clienteKey) {
            $tx = DB::table('local_transaction')->where('local_transaction_id', $ltId)->first();

            if (!$tx) {
                $resultados[] = [$ltId, $clienteKey, '-', 'NO EXISTE', '-'];
                continue;
            }

            // Salvaguarda: no re-timbrar si ya tiene CFDI
            if (!empty($tx->fiscal_invoice)) {
                $resultados[] = [$ltId, $clienteKey, '-', 'YA FACTURADA (omitida)', $tx->fiscal_invoice];
                continue;
            }

            $cuenta = $this->cuentas[$clienteKey];

            $base  = round($tx->Total / 1.08, 2);
            $iva   = round($tx->Total - $base, 2);
            $total = round($base + $iva, 2);

            $fechaEmision = now()->subMinutes(5)->format('Y-m-d\TH:i:s');

            $formaPago = $this->catalogs->payment_method[
                $this->catalogs->folio_payment_type[$tx->PaymentType] ?? 'Por definir'
            ] ?? '99';

            $fileName = $tx->CadenaFacturacion ?: 'IND_' . $ltId;

            $orderJSON = $this->buildOrderJSON($cuenta, $base, $iva, $total, $fechaEmision, $formaPago, $nextFolio);

            if ($dry) {
                $resultados[] = [$ltId, $clienteKey, 'AB' . $nextFolio, 'DRY-RUN (no enviado)', '$' . number_format($total, 2)];
                $nextFolio++;
                continue;
            }

            $response = $this->callAPIWithToken('/servicios/timbrar/json', $orderJSON, $token);
            $codigo   = $response['estatus']['codigo'] ?? 'sin-codigo';

            if ($codigo === '000') {
                $cfdi = $response['cfdiTimbrado']['respuesta'] ?? [];
                $uuid = $cfdi['uuid'] ?? $cfdi['UUID'] ?? null;
                $pdf  = $cfdi['pdf']     ?? null;
                $xml  = $cfdi['cfdixml'] ?? null;

                DB::transaction(function () use ($ltId, $uuid, $cuenta, $nextFolio, $base, $iva, $total, $fileName, $fechaEmision) {
                    DB::table('local_transaction')->where('local_transaction_id', $ltId)->update([
                        'fiscal_invoice'    => $uuid,
                        'fiscal_account_id' => $cuenta['fiscal_account_id'],
                    ]);

                    DB::table('individual_invoices')->insertOrIgnore([
                        'uuid'                 => $uuid,
                        'local_transaction_id' => $ltId,
                        'fiscal_account_id'    => $cuenta['fiscal_account_id'],
                        'serie'                => 'AB',
                        'folio'                => (string) $nextFolio,
                        'subtotal'             => $base,
                        'iva'                  => $iva,
                        'total'                => $total,
                        'file_name'            => $fileName,
                        'fecha_emision'        => str_replace('T', ' ', $fechaEmision),
                        'status'               => 'vigente',
                        'origen'               => 'retimbrado',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);
                });

                if ($pdf && $xml) {
                    $this->saveInvoiceFile($fileName, $pdf, $xml);
                }

                Log::info('[RetimbrarDuplicadas] OK', ['lt' => $ltId, 'uuid' => $uuid, 'folio' => $nextFolio]);
                $resultados[] = [$ltId, $clienteKey, 'AB' . $nextFolio, 'TIMBRADA OK', $uuid];
                $nextFolio++;
            } else {
                $desc = $response['estatus']['descripcion'] ?? '';
                $tec  = $response['estatus']['informacionTecnica'] ?? '';
                Log::error('[RetimbrarDuplicadas] Error', ['lt' => $ltId, 'codigo' => $codigo, 'desc' => $desc, 'tec' => $tec]);
                $resultados[] = [$ltId, $clienteKey, 'AB' . $nextFolio, "ERROR {$codigo}: {$desc}", substr($tec, 0, 60)];
                // No incrementa folio: el siguiente reutiliza este número
            }
        }

        $this->table(['Transacción', 'Cliente', 'Folio', 'Resultado', 'UUID / Detalle'], $resultados);
        return self::SUCCESS;
    }

    private function buildOrderJSON(array $cuenta, $base, $iva, $total, $fechaEmision, $formaPago, $folio): array
    {
        return [
            'DatosGenerales' => [
                'Version'          => '4.0',
                'CSD'              => $this->catalogs->api_data_reference['CSD'],
                'LlavePrivada'     => $this->catalogs->api_data_reference['CSDKey'],
                'CSDPassword'      => $this->catalogs->api_data_reference['CSDPassword'],
                'GeneraPDF'        => true,
                'Logotipo'         => $this->catalogs->api_data_reference['aqua_logo'] ?? '',
                'CFDI'             => 'Factura',
                'OpcionDecimales'  => 2,
                'NumeroDecimales'  => 2,
                'TipoCFDI'         => 'Ingreso',
                'EnviaEmail'       => false,
                'ReceptorEmail'    => '',
                'ReceptorEmailCC'  => '',
                'ReceptorEmailCCO' => '',
                'EmailMensaje'     => '',
            ],
            'Encabezado' => [
                'CFDIsRelacionados' => null,
                'TipoRelacion'      => null,
                'Emisor' => [
                    'RFC'               => $this->catalogs->api_data_reference['RFC'],
                    'NombreRazonSocial' => $this->catalogs->api_data_reference['NombreRazonSocial'],
                    'RegimenFiscal'     => $this->catalogs->api_data_reference['RegimenFiscal'],
                    'Direccion'         => [
                        ['CodigoPostal' => $this->catalogs->api_data_reference['CodigoPostal']]
                    ],
                ],
                'Receptor' => [
                    'RFC'               => $cuenta['rfc'],
                    'NombreRazonSocial' => $cuenta['company_name'],
                    'UsoCFDI'           => $cuenta['cfdi_use'],
                    'RegimenFiscal'     => $cuenta['tax_regime'],
                    'Direccion'         => ['CodigoPostal' => $cuenta['zip_code']],
                ],
                'Fecha'           => $fechaEmision,
                'Serie'           => 'AB',
                'Folio'           => (string) $folio,
                'MetodoPago'      => 'PUE',
                'FormaPago'       => $formaPago,
                'Moneda'          => 'MXN',
                'LugarExpedicion' => $this->catalogs->api_data_reference['CodigoPostal'],
                'SubTotal'        => $base,
                'Total'           => $total,
                'Observaciones'   => '',
            ],
            'Conceptos' => [
                [
                    'Cantidad'         => 1,
                    'CodigoUnidad'     => 'E48',
                    'Unidad'           => 'Servicio',
                    'CodigoProducto'   => '84111506',
                    'Producto'         => 'RENOVACION DE MEMBRESIA',
                    'PrecioUnitario'   => $base,
                    'Importe'          => $base,
                    'ObjetoDeImpuesto' => '02',
                    'Impuestos'        => [
                        [
                            'TipoImpuesto'    => 1,
                            'Impuesto'        => 2,
                            'Factor'          => 1,
                            'Base'            => $base,
                            'Tasa'            => '0.080000',
                            'ImpuestoImporte' => $iva,
                        ]
                    ],
                ]
            ],
        ];
    }

    private function saveInvoiceFile(string $name, string $pdf, string $xml): void
    {
        $pdfContent = base64_decode($pdf);
        $targets = [
            ['dir' => storage_path('app/public/pdfs'), 'ext' => 'pdf', 'data' => $pdfContent],
            ['dir' => base_path('../facturacion_aqua/storage/app/public/pdfs'), 'ext' => 'pdf', 'data' => $pdfContent],
            ['dir' => storage_path('app/public/xmls'), 'ext' => 'xml', 'data' => $xml],
            ['dir' => base_path('../facturacion_aqua/storage/app/public/xmls'), 'ext' => 'xml', 'data' => $xml],
        ];
        foreach ($targets as $t) {
            if (!is_dir($t['dir'])) mkdir($t['dir'], 0775, true);
            file_put_contents($t['dir'] . '/' . $name . '.' . $t['ext'], $t['data']);
        }
    }

    private function obtenerToken(): array
    {
        $api      = $this->catalogs->api_data_reference['api'];
        $username = $this->catalogs->api_data_reference['username'];
        $password = $this->catalogs->api_data_reference['password'];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $api . '/token/crear?Usuario=' . $username . '&Password=' . $password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $data = json_decode($body, true);
        if (!empty($data['token'])) {
            return ['token' => $data['token'], 'error' => null];
        }
        return ['token' => null, 'error' => ['status' => $code, 'body' => $body]];
    }

    private function callAPIWithToken(string $endpoint, array $data, string $token): array
    {
        $url  = $this->catalogs->api_data_reference['api'] . $endpoint;
        $json = json_encode($data);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . trim($token),
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);
        $body = curl_exec($curl);
        curl_close($curl);
        return json_decode($body, true) ?? [];
    }
}
