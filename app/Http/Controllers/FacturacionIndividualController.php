<?php

namespace App\Http\Controllers;

use App\Models\GeneralCatalogs;
use App\Models\FiscalAccounts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
 
class FacturacionIndividualController extends Controller
{ 
    protected $catalogs;

    public function __construct()
    {
        $this->catalogs = new GeneralCatalogs();
    }

    public function index()
    {
        $activePage = 'facturacion_individual';
        return view('facturacion.individual', compact('activePage'));
    }

    /**
     * Devuelve transacciones para el DataTable (sin factura individual ni global cancelada)
     */
    public function transacciones(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->subDays(1)->format('Y-m-d');
        $fechaFinal  = $request->fecha_final  ?? Carbon::now()->format('Y-m-d');

        $sql = "
            SELECT
                lt.local_transaction_id,
                lt._id,
                DATE(lt.TransationDate) AS fecha,
                TIME(lt.TransationDate) AS hora,
                lt.Atm AS cajero,
                lt.PaymentType,
                lt.TransactionType,
                lt.Total,
                lt.CadenaFacturacion,
                lt.fiscal_invoice,
                lt.global_invoice_id,
                gi.cancelada_at AS global_cancelada_at,
                fa.rfc AS facturado_rfc,
                fa.company_name AS facturado_nombre,
                CASE
                    WHEN lt.fiscal_invoice IS NOT NULL THEN 'individual'
                    WHEN lt.global_invoice_id IS NOT NULL AND gi.cancelada_at IS NULL THEN 'global'
                    ELSE 'pendiente'
                END AS estatus_factura
            FROM local_transaction lt
            LEFT JOIN global_invoice gi ON lt.global_invoice_id = gi.id
            LEFT JOIN fiscal_accounts fa ON lt.fiscal_account_id = fa.id
            WHERE lt.TransationDate BETWEEN ? AND ?
              AND lt.deleted_at IS NULL
              AND lt.Total > 0
              AND lt.PaymentType != 3
        ";

        $params = [
            $fechaInicio . ' 00:00:00',
            $fechaFinal  . ' 23:59:59',
        ];

        if ($request->filled('transaction_type') && $request->transaction_type !== '') {
            $sql .= " AND lt.TransactionType = ?";
            $params[] = $request->transaction_type;
        }

        if ($request->filled('payment_type') && $request->payment_type !== '') {
            $sql .= " AND lt.PaymentType = ?";
            $params[] = $request->payment_type;
        }

        if ($request->filled('cajero')) {
            $sql .= " AND lt.Atm = ?";
            $params[] = $request->cajero;
        }

        $sql .= " ORDER BY lt.TransationDate DESC";

        $rows = DB::select($sql, $params);

        $paymentNames = [
            0 => 'Efectivo',
            1 => 'Tarjeta de Débito',
            2 => 'Tarjeta de Crédito',
        ];

        $txNames = [
            0 => 'Compra Membresía',
            1 => 'Renovación Membresía',
            2 => 'Compra Paquete',
        ];

        $data = array_map(function ($row) use ($paymentNames, $txNames) {
            return [
                'local_transaction_id'    => $row->local_transaction_id,
                '_id'                     => $row->_id,
                'fecha'                   => $row->fecha,
                'hora'                    => $row->hora,
                'cajero'                  => $row->cajero,
                'payment_type'            => $row->PaymentType,
                'payment_type_nombre'     => $paymentNames[$row->PaymentType] ?? 'Desconocido',
                'transaction_type'        => $row->TransactionType,
                'transaction_type_nombre' => $txNames[$row->TransactionType] ?? 'Desconocido',
                'total'                   => $row->Total,
                'cadena_facturacion'      => $row->CadenaFacturacion,
                'estatus_factura'         => $row->estatus_factura,
                'facturado_rfc'           => $row->facturado_rfc,
                'facturado_nombre'        => $row->facturado_nombre,
                'bloqueada'               => ($row->estatus_factura === 'individual'),
            ];
        }, $rows);

        return response()->json(['data' => $data]);
    }

    /**
     * Busca cuentas fiscales por RFC o nombre para el autocomplete
     */
    public function buscarCuentaFiscal(Request $request)
    {
        $q = $request->q ?? '';

        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $rows = DB::table('fiscal_accounts')
            ->where(function ($query) use ($q) {
                $query->where('rfc', 'LIKE', "%{$q}%")
                      ->orWhere('company_name', 'LIKE', "%{$q}%");
            })
            ->whereNotNull('rfc')
            ->orderBy('rfc')
            ->limit(20)
            ->get(['id', 'rfc', 'company_name', 'tax_regime', 'cfdi_use', 'zip_code', 'email']);

        return response()->json($rows);
    }

    /**
     * Genera CFDI individuales para las transacciones seleccionadas
     */
    public function generarFactura(Request $request)
    {
        $ids = $request->input('ids', []);

        $rfc         = strtoupper(trim($request->input('rfc', '')));
        $companyName = strtoupper(trim($request->input('company_name', '')));
        $taxRegime   = $request->input('tax_regime', '601');
        $cfdiUse     = $request->input('cfdi_use', 'G03');
        $zipCode     = $request->input('zip_code', '32030');
        $email       = $request->input('email', '');
        $concepto      = strtoupper(trim($request->input('concepto', ''))) ?: null;
        $fechaEmision  = $request->input('fecha_emision')
            ? Carbon::parse($request->input('fecha_emision'), 'America/Mexico_City')->format('Y-m-d\TH:i:s')
            : Carbon::now('America/Mexico_City')->subMinutes(3)->format('Y-m-d\TH:i:s');

        Log::info('[FacturacionIndividual] generarFactura iniciado', [
            'ids_count' => count($ids),
            'rfc'       => $rfc,
        ]);

        if (empty($ids)) {
            return response()->json(['error' => 'No se seleccionaron transacciones.'], 400);
        }

        if (!$rfc || !$companyName || !$zipCode) {
            return response()->json(['error' => 'RFC, razón social y código postal son obligatorios.'], 400);
        }

        $transactions = DB::table('local_transaction')
            ->whereIn('local_transaction_id', $ids)
            ->whereNull('fiscal_invoice')
            ->whereNull('deleted_at')
            ->where('Total', '>', 0)
            ->where('PaymentType', '!=', 3)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json(['error' => 'No hay transacciones válidas (ya facturadas o no encontradas).'], 400);
        }

        // Upsert cuenta fiscal
        $fiscalAccount = DB::table('fiscal_accounts')
            ->where('rfc', $rfc)
            ->whereNull('account_id')
            ->first();

        if ($fiscalAccount) {
            DB::table('fiscal_accounts')->where('id', $fiscalAccount->id)->update([
                'company_name' => $companyName,
                'tax_regime'   => $taxRegime,
                'cfdi_use'     => $cfdiUse,
                'zip_code'     => $zipCode,
                'email'        => $email ?: $fiscalAccount->email,
                'updated_at'   => now(),
            ]);
            $fiscalAccountId = $fiscalAccount->id;
        } else {
            $fiscalAccountId = DB::table('fiscal_accounts')->insertGetId([
                'rfc'          => $rfc,
                'company_name' => $companyName,
                'tax_regime'   => $taxRegime,
                'cfdi_use'     => $cfdiUse,
                'zip_code'     => $zipCode,
                'email'        => $email,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $tokenResult = $this->obtenerToken();
        if (!$tokenResult['token']) {
            return response()->json([
                'error'       => 'No se pudo obtener el token de autenticación.',
                'debug_token' => $tokenResult['error'],
            ], 500);
        }
        $token = $tokenResult['token'];

        $txTypes = [
            0 => 'COMPRA DE MEMBRESIA',
            1 => 'RENOVACION DE MEMBRESIA',
            2 => 'SERVICIO DE LAVADO',
        ];

        $generadas  = [];
        $errores    = [];

        DB::beginTransaction();
        try {
            foreach ($transactions as $tx) {
                $base  = round($tx->Total / 1.08, 2);
                $iva   = round($tx->Total - $base, 2);
                $total = round($base + $iva, 2);

                $conceptoTx = $concepto ?: ($txTypes[$tx->TransactionType] ?? 'SERVICIO DE LAVADO');

                $formaPago = $this->catalogs->payment_method[
                    $this->catalogs->folio_payment_type[$tx->PaymentType] ?? 'Por definir'
                ] ?? '99';

                // Fecha de emisión: viene del request (validada en JS), fallback a now-3min

                $orderJSON = [
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
                            'RFC'               => $rfc,
                            'NombreRazonSocial' => $companyName,
                            'UsoCFDI'           => $cfdiUse,
                            'RegimenFiscal'     => $taxRegime,
                            'Direccion'         => ['CodigoPostal' => $zipCode],
                        ],
                        'Fecha'           => $fechaEmision,
                        'Serie'           => 'AB',
                        'Folio'           => '100',
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
                            'Producto'         => $conceptoTx,
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

                $response = $this->callAPIWithToken('/servicios/timbrar/json', $orderJSON, $token);

                Log::info('[FacturacionIndividual] Respuesta timbrado', [
                    'local_transaction_id' => $tx->local_transaction_id,
                    'codigo'               => $response['estatus']['codigo'] ?? null,
                ]);

                if (!empty($response['estatus']['detieneEjecucionProveedor'])) {
                    DB::rollBack();
                    return response()->json([
                        'error' => $response['estatus']['descripcion'] ?? 'Error al timbrar.',
                    ], 500);
                }

                if (($response['estatus']['codigo'] ?? '') === '000') {
                    $cfdi   = $response['cfdiTimbrado']['respuesta'] ?? [];
                    $uuid   = $cfdi['uuid']    ?? $cfdi['UUID']    ?? null;
                    $pdf    = $cfdi['pdf']     ?? null;
                    $xml    = $cfdi['cfdixml'] ?? null;

                    // Nombre de archivo: usa CadenaFacturacion si existe, si no usa local_transaction_id
                    $fileName = $tx->CadenaFacturacion
                        ? $tx->CadenaFacturacion
                        : 'IND_' . $tx->local_transaction_id;

                    if ($pdf && $xml) {
                        $this->saveInvoiceFile($fileName, $pdf, $xml);
                    }

                    DB::table('local_transaction')
                        ->where('local_transaction_id', $tx->local_transaction_id)
                        ->update([
                            'fiscal_invoice'    => $uuid,
                            'fiscal_account_id' => $fiscalAccountId,
                        ]);

                    $generadas[] = [
                        'local_transaction_id' => $tx->local_transaction_id,
                        '_id'                  => $tx->_id,
                        'uuid'                 => $uuid,
                        'total'                => $total,
                        'file_name'            => $fileName,
                    ];
                } else {
                    DB::rollBack();
                    return response()->json([
                        'error'   => 'Error al timbrar transacción #' . $tx->local_transaction_id,
                        'estatus' => $response['estatus'] ?? null,
                    ], 500);
                }
            }

            DB::commit();

            // Enviar email si se proporcionó
            if ($email && count($generadas) > 0) {
                try {
                    $this->enviarEmail($email, $generadas);
                } catch (\Exception $e) {
                    Log::warning('[FacturacionIndividual] Error al enviar email', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'message'  => count($generadas) . ' factura(s) generada(s) con éxito.',
                'generadas' => $generadas,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[FacturacionIndividual] Excepción', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descarga PDF de factura individual
     */
    public function downloadPdf($fileName)
    {
        // Buscar en pdfs/ (AquaFacturacion) y en invoices/ (AquaStaff global)
        $paths = [
            storage_path('app/public/pdfs/' . $fileName . '.pdf'),
            storage_path('app/public/invoices/' . $fileName . '.pdf'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return response()->download($path, $fileName . '.pdf', ['Content-Type' => 'application/pdf']);
            }
        }

        abort(404, 'PDF no encontrado.');
    }

    /**
     * Descarga XML de factura individual
     */
    public function downloadXml($fileName)
    {
        $paths = [
            storage_path('app/public/xmls/' . $fileName . '.xml'),
            storage_path('app/public/invoices/' . $fileName . '.xml'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return response()->download($path, $fileName . '.xml', ['Content-Type' => 'text/xml']);
            }
        }

        abort(404, 'XML no encontrado.');
    }

    // ─────────────────────────────────────────────
    // Métodos privados
    // ─────────────────────────────────────────────

    private function saveInvoiceFile(string $name, string $pdf, string $xml): void
    {
        $pdfContent = base64_decode($pdf);

        $dirs = [
            'pdf' => [
                storage_path('app/public/pdfs'),
                base_path('../AquaFacturacion/storage/app/public/pdfs'),
            ],
            'xml' => [
                storage_path('app/public/xmls'),
                base_path('../AquaFacturacion/storage/app/public/xmls'),
            ],
        ];

        foreach ($dirs['pdf'] as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            file_put_contents($dir . '/' . $name . '.pdf', $pdfContent);
        }

        foreach ($dirs['xml'] as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            file_put_contents($dir . '/' . $name . '.xml', $xml);
        }
    }

    private function enviarEmail(string $email, array $generadas): void
    {
        \Illuminate\Support\Facades\Mail::send(
            'emails.invoice_individual',
            ['generadas' => $generadas],
            function ($message) use ($email, $generadas) {
                $message->to($email)->subject('Factura(s) AquaCarClub');
                foreach ($generadas as $g) {
                    $pdfPath = storage_path('app/public/pdfs/' . $g['file_name'] . '.pdf');
                    $xmlPath = storage_path('app/public/xmls/' . $g['file_name'] . '.xml');
                    if (file_exists($pdfPath)) $message->attach($pdfPath);
                    if (file_exists($xmlPath)) $message->attach($xmlPath);
                }
            }
        );
    }

    private function obtenerToken(): array
    {
        $cacheKey = 'facturoporti_prod_token';

        if (Cache::has($cacheKey)) {
            return ['token' => Cache::get($cacheKey), 'error' => null];
        }

        $api      = $this->catalogs->api_data_reference['api'];
        $username = $this->catalogs->api_data_reference['username'];
        $password = $this->catalogs->api_data_reference['password'];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $api . '/token/crear?Usuario=' . $username . '&Password=' . $password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body     = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $data = json_decode($body, true);

        if (!empty($data['token'])) {
            Cache::forever($cacheKey, $data['token']);
            return ['token' => $data['token'], 'error' => null];
        }

        return [
            'token' => null,
            'error' => ['status' => $httpCode, 'body' => $body],
        ];
    }

    private function callAPIWithToken(string $endpoint, array $data, string $token): array
    {
        $url  = $this->catalogs->api_data_reference['api'] . $endpoint;
        $json = json_encode($data);

        $result = $this->curlPost($url, $json, $token);

        if (($result['status'] ?? 0) === 401) {
            Cache::forget('facturoporti_prod_token');
            $newTokenResult = $this->obtenerToken();
            if (!$newTokenResult['token']) {
                return ['estatus' => ['codigo' => '401', 'descripcion' => 'Token expirado y no renovable.', 'detieneEjecucionProveedor' => true]];
            }
            $result = $this->curlPost($url, $json, $newTokenResult['token']);
        }

        return json_decode($result['body'], true) ?? [];
    }

    private function curlPost(string $url, string $jsonBody, string $token): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $jsonBody,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . trim($token),
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);
        $body   = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ['status' => $status, 'body' => $body];
    }
}
