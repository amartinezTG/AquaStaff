<?php

namespace App\Http\Controllers;
 
use App\Models\GeneralCatalogs;
use App\Models\GlobalInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
 
class FacturacionController extends Controller
{
    protected $catalogs;

    public function __construct()
    {
        $this->catalogs = new GeneralCatalogs();
    }
  
    public function index()
    {
        $activePage = 'facturacion';
        return view('facturacion.index', compact('activePage'));
    }
  
    /**
     * Devuelve transacciones para el DataTable via AJAX POST
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
                lt.global_invoice_date,
                gi.name AS factura_global_nombre,
                fa.rfc AS facturado_rfc,
                fa.company_name AS facturado_nombre,
                CASE
                    WHEN lt.fiscal_invoice IS NOT NULL                                  THEN 'individual'
                    WHEN lt.global_invoice_id IS NOT NULL AND gi.cancelada_at IS NULL   THEN 'global'
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

        if ($request->filled('payment_type')) {
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
                'local_transaction_id'  => $row->local_transaction_id,
                '_id'                   => $row->_id,
                'fecha'                 => $row->fecha,
                'hora'                  => $row->hora,
                'cajero'                => $row->cajero,
                'payment_type'          => $row->PaymentType,
                'payment_type_nombre'   => $paymentNames[$row->PaymentType] ?? 'Desconocido',
                'transaction_type'      => $row->TransactionType,
                'transaction_type_nombre' => $txNames[$row->TransactionType] ?? 'Desconocido',
                'total'                 => $row->Total,
                'cadena_facturacion'    => $row->CadenaFacturacion,
                'estatus_factura'       => $row->estatus_factura,
                'factura_global_nombre' => $row->factura_global_nombre,
                'facturado_rfc'         => $row->facturado_rfc,
                'facturado_nombre'      => $row->facturado_nombre,
                'bloqueada'             => ($row->estatus_factura !== 'pendiente'),
            ];
        }, $rows);

        return response()->json(['data' => $data]);
    }

    /**
     * Genera factura(s) global(es) para las transacciones seleccionadas
     */
    public function generarFactura(Request $request)
    {
        $ids          = $request->input('ids', []);
        $periodicidad = $request->input('periodicidad', '04');
        $concepto     = strtoupper(trim($request->input('concepto', 'VENTA GLOBAL SERVICIOS DE LAVADO')));
        $fechaEmision = $request->input('fecha_emision')
            ? Carbon::parse($request->input('fecha_emision'), 'America/Mexico_City')
            : Carbon::now('America/Mexico_City');

        Log::error('[FacturacionController] generarFactura iniciado', ['ids_count' => count($ids), 'periodicidad' => $periodicidad]);

        if (empty($ids)) {
            return response()->json(['error' => 'No se seleccionaron transacciones.'], 400);
        }

        $transactions = DB::table('local_transaction')
            ->whereIn('local_transaction_id', $ids)
            ->whereNull('global_invoice_id')
            ->whereNull('fiscal_invoice')
            ->whereNull('deleted_at')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json(['error' => 'No hay transacciones válidas para facturar (puede que ya estén facturadas).'], 400);
        }

        // Agrupar: tarjeta (1 o 2) por Atm, efectivo (0) en un solo grupo
        $groupedTransactions = [];

        $byPayment = $transactions->groupBy('PaymentType');

        foreach ($byPayment as $paymentType => $paymentGroup) {
            if ((int)$paymentType === 1 || (int)$paymentType === 2) {
                foreach ($paymentGroup->groupBy('Atm') as $cajero => $cajeroGroup) {
                    $groupedTransactions[] = $cajeroGroup;
                }
            } else {
                $groupedTransactions[] = $paymentGroup;
            }
        }

        // Obtener token desde FacturoPorTi producción
        $tokenResult = $this->obtenerTokenProduccion();

        if (!$tokenResult['token']) {
            return response()->json([
                'error'       => 'No se pudo obtener el token de autenticación.',
                'debug_token' => $tokenResult['error'],
            ], 500);
        }

        $token = $tokenResult['token'];

        $generatedInvoices = [];

        DB::beginTransaction();
        try {
            foreach ($groupedTransactions as $group) {
                $totalGroup = $group->sum('Total');

                if ($totalGroup <= 0) {
                    continue;
                }

                $base  = round($totalGroup / 1.08, 2);
                $iva   = round($totalGroup - $base, 2);
                $total = round($base + $iva, 2);

                $startDateGroup  = $group->min('TransationDate');
                $endDateGroup    = $group->max('TransationDate');
                $paymentType     = $group->first()->PaymentType;
                $paymentTypeName = $this->catalogs->folio_payment_type[$paymentType] ?? 'Desconocido';
                $cajeroInfo      = (((int)$paymentType === 1 || (int)$paymentType === 2) && $group->first()->Atm)
                    ? '_' . $group->first()->Atm
                    : '';

                $invoiceName = substr(
                    'GLOBAL_' .
                    Carbon::parse($startDateGroup)->format('Ymd') . '_' .
                    Carbon::parse($endDateGroup)->format('Ymd') . '_' .
                    str_replace(' ', '_', $paymentTypeName) .
                    $cajeroInfo,
                    0, 60
                );

                $orderJSON = [
                    'DatosGenerales' => [
                        'Version'          => '4.0',
                        'CSD'              => $this->catalogs->api_data_reference['CSD'],
                        'LlavePrivada'     => $this->catalogs->api_data_reference['CSDKey'],
                        'CSDPassword'      => $this->catalogs->api_data_reference['CSDPassword'],
                        'GeneraPDF'        => true,
                        'Logotipo'         => '',
                        'CFDI'             => 'Factura',
                        'OpcionDecimales'  => 2,
                        'NumeroDecimales'  => 2,
                        'TipoCFDI'         => 'Ingreso',
                        'EnviaEmail'       => false,
                        'ReceptorEmail'    => '',
                        'ReceptorEmailCC'  => '',
                        'ReceptorEmailCCO' => '',
                        'EmailMensaje'     => 'Factura global de servicios de lavado del ' .
                            Carbon::parse($startDateGroup)->format('Y-m-d') .
                            ' al ' .
                            Carbon::parse($endDateGroup)->format('Y-m-d'),
                    ],
                    'Encabezado' => [
                        'CFDIsRelacionados' => null,
                        'TipoRelacion'      => null,
                        'Emisor' => [
                            'RFC'               => $this->catalogs->api_data_reference['RFC'],
                            'NombreRazonSocial' => $this->catalogs->api_data_reference['NombreRazonSocial'],
                            'RegimenFiscal'     => '601',
                            'Direccion' => [
                                [
                                    'Calle'          => 'RAFAEL PEREZ SERNA',
                                    'NumeroExterior' => '755',
                                    'NumeroInterior' => '',
                                    'Colonia'        => 'PARTIDO ROMERO',
                                    'Localidad'      => 'JUAREZ',
                                    'Municipio'      => 'JUAREZ',
                                    'Estado'         => 'Chihuahua',
                                    'Pais'           => 'México',
                                    'CodigoPostal'   => '32030',
                                ]
                            ]
                        ],
                        'Receptor' => [
                            'RFC'               => 'XAXX010101000',
                            'NombreRazonSocial' => 'PUBLICO EN GENERAL',
                            'UsoCFDI'           => 'S01',
                            'RegimenFiscal'     => '616',
                            'Direccion' => [
                                'CodigoPostal' => '32030',
                            ]
                        ],
                        'InformacionFacturaGlobal' => [
                            'Periodicidad' => $periodicidad,
                            'Meses'        => Carbon::parse($startDateGroup)->format('m'),
                            'Año'          => intval(Carbon::parse($startDateGroup)->format('Y')),
                        ],
                        'Fecha'           => $fechaEmision->format('Y-m-d\TH:i:s'),
                        'Serie'           => 'AB',
                        'Folio'           => '100',
                        'MetodoPago'      => 'PUE',
                        'FormaPago'       => $this->catalogs->payment_method[$paymentTypeName] ?? '99',
                        'Moneda'          => 'MXN',
                        'LugarExpedicion' => $this->catalogs->api_data_reference['CodigoPostal'],
                        'SubTotal'        => $base,
                        'Total'           => $total,
                        'Observaciones'   => 'Factura generada automáticamente.',
                    ],
                    'Conceptos' => [
                        [
                            'Cantidad'         => 1,
                            'CodigoUnidad'     => 'E48',
                            'Serie'            => '0000012345',
                            'Unidad'           => 'Servicio',
                            'CodigoProducto'   => '84111506',
                            'producto'         => $concepto,
                            'PrecioUnitario'   => $base,
                            'Importe'          => $base,
                            'ObjetoDeImpuesto' => '02',
                            'Impuestos' => [
                                [
                                    'TipoImpuesto'    => 1,
                                    'Impuesto'        => 2,
                                    'Factor'          => 1,
                                    'Base'            => $base,
                                    'Tasa'            => '0.080000',
                                    'ImpuestoImporte' => $iva,
                                ]
                            ]
                        ]
                    ]
                ];

                $response = $this->callAPIWithToken('/servicios/timbrar/json', $orderJSON, $token);

                Log::error('[FacturacionController] Respuesta API timbrado', [
                    'invoiceName' => $invoiceName,
                    'base'        => $base,
                    'iva'         => $iva,
                    'total'       => $total,
                    'response'    => $response,
                ]);

                if (!empty($response['estatus']['detieneEjecucionProveedor'])) {
                    DB::rollBack();
                    return response()->json([
                        'error' => $response['estatus']['descripcion'] ?? 'Error al timbrar.'
                    ], 500);
                }

                if (($response['estatus']['codigo'] ?? '') === '000') {
                    $cfdi     = $response['cfdiTimbrado']['respuesta'] ?? [];
                    $uuid     = $cfdi['uuid']   ?? $cfdi['UUID']   ?? null;
                    $serieCfdi = $cfdi['serie'] ?? $cfdi['Serie']  ?? 'AB';
                    $folioCfdi = $cfdi['folio'] ?? $cfdi['Folio']  ?? null;
                    $pdf      = $cfdi['pdf']     ?? null;
                    $xml      = $cfdi['cfdixml'] ?? null;

                    $globalInvoice = GlobalInvoice::create([
                        'name'             => $invoiceName,
                        'uuid'             => $uuid,
                        'serie'            => $serieCfdi,
                        'folio'            => $folioCfdi,
                        'file_name'        => $invoiceName,
                        'total'            => $total,
                        'start_date_group' => $startDateGroup,
                        'end_date_group'   => $endDateGroup,
                        'paymentType'      => $paymentType,
                        'periodicidad'     => $periodicidad,
                    ]);

                    if ($pdf && $xml) {
                        $this->saveInvoiceFile($invoiceName, $pdf, $xml);
                    }

                    DB::table('local_transaction')
                        ->whereIn('local_transaction_id', $group->pluck('local_transaction_id'))
                        ->whereNull('global_invoice_id')
                        ->update([
                            'global_invoice_id'   => $globalInvoice->id,
                            'global_invoice_date' => now(),
                        ]);

                    $generatedInvoices[] = [
                        'id'          => $globalInvoice->id,
                        'name'        => $invoiceName,
                        'total'       => $total,
                        'transacciones' => $group->count(),
                    ];
                } else {
                    DB::rollBack();
                    return response()->json([
                        'error'            => 'Error al timbrar',
                        'estatus'          => $response['estatus'] ?? null,
                        'respuesta_completa' => $response,
                    ], 500);
                }
            }

            DB::commit();
            return response()->json([
                'message'  => 'Factura(s) global(es) generada(s) con éxito.',
                'invoices' => $generatedInvoices,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar factura global', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Historial de facturas globales
     */
    public function historial()
    {
        $sql = "
            SELECT
                gi.id,
                gi.name,
                gi.uuid,
                gi.serie,
                gi.folio,
                gi.file_name,
                gi.total,
                gi.start_date_group,
                gi.end_date_group,
                gi.paymentType,
                gi.periodicidad,
                gi.cancelada_at,
                gi.created_at,
                COUNT(lt.local_transaction_id) AS num_transacciones
            FROM global_invoice gi
            LEFT JOIN local_transaction lt ON lt.global_invoice_id = gi.id
            GROUP BY gi.id, gi.name, gi.uuid, gi.serie, gi.folio, gi.file_name, gi.total, gi.start_date_group, gi.end_date_group, gi.paymentType, gi.periodicidad, gi.cancelada_at, gi.cancel_motivo, gi.created_at
            ORDER BY gi.created_at DESC
        ";

        $rows = DB::select($sql);

        $paymentNames = [
            0 => 'Efectivo',
            1 => 'Tarjeta de Débito',
            2 => 'Tarjeta de Crédito',
        ];

        $data = array_map(function ($row) use ($paymentNames) {
            return [
                'id'                  => $row->id,
                'name'                => $row->name,
                'uuid'                => $row->uuid,
                'serie'               => $row->serie,
                'folio'               => $row->folio,
                'file_name'           => $row->file_name ?? $row->name,
                'total'               => $row->total,
                'start_date_group'    => $row->start_date_group,
                'end_date_group'      => $row->end_date_group,
                'payment_type'        => $row->paymentType,
                'payment_type_nombre' => $paymentNames[$row->paymentType] ?? 'Desconocido',
                'periodicidad'        => $row->periodicidad,
                'num_transacciones'   => $row->num_transacciones,
                'created_at'          => $row->created_at,
                'cancelada_at'        => $row->cancelada_at ?? null,
            ];
        }, $rows);

        return response()->json(['data' => $data]);
    }     

    /**
     * Cancela una factura global — solo role 1
     */
    public function cancelarFactura(Request $request, $id)
    {
        if (auth()->user()->role !== 1) {
            return response()->json(['error' => 'No tienes permisos para cancelar facturas.'], 403);
        }

        $globalInvoice = GlobalInvoice::find($id);

        if (!$globalInvoice) {
            return response()->json(['error' => 'Factura no encontrada.'], 404);
        }

        if ($globalInvoice->cancelada_at) {
            return response()->json(['error' => 'Esta factura ya fue cancelada.'], 400);
        }

        if (!$globalInvoice->uuid) {
            return response()->json(['error' => 'Esta factura no tiene UUID registrado y no puede cancelarse vía API.'], 400);
        }

        // Construir payload de cancelación para FacturoPorTi (/servicios/cancelar/csd)
        $cancelJSON = [
            'rfcEmisor'   => $this->catalogs->api_data_reference['RFC'],
            'rfcReceptor' => 'XAXX010101000',
            'uuid'        => $globalInvoice->uuid,
            'total'       => (float) $globalInvoice->total,
            'motivo'      => '02',
            'certificado' => $this->catalogs->api_data_reference['CSD'],
            'llavePrivada'=> $this->catalogs->api_data_reference['CSDKey'],
            'password'    => $this->catalogs->api_data_reference['CSDPassword'],
        ];

        $tokenResult = $this->obtenerTokenProduccion();

        if (!$tokenResult['token']) {
            return response()->json([
                'error'       => 'No se pudo obtener el token de autenticación.',
                'debug_token' => $tokenResult['error'],
            ], 500);
        }

        $token = $tokenResult['token'];

        $response = $this->callAPIWithToken('/servicios/cancelar/csd', $cancelJSON, $token);

        Log::info('[FacturacionController] Respuesta cancelación', ['id' => $id, 'response' => $response]);

        // FacturoPorTi /servicios/cancelar/csd responde con: codigo, mensaje, acuse, estatusCancelacion
        $codigo = $response['codigo'] ?? null;
        // '000' = ok, o puede que ya esté en proceso de cancelación (acuse presente)
        $exito  = $codigo === '000' || isset($response['acuse']);

        if (!$exito) {
            $msg = $response['mensaje'] ?? 'Error desconocido al cancelar.';
            return response()->json([
                'error'   => 'Error al cancelar: ' . $msg,
                'detalle' => $response,
            ], 500);
        }

        DB::beginTransaction();
        try {
            // Marcar factura como cancelada
            $globalInvoice->update([
                'cancelada_at'  => now(),
                'cancel_motivo' => '02',
            ]);

            // Liberar transacciones para que puedan refacturarse
            DB::table('local_transaction')
                ->where('global_invoice_id', $globalInvoice->id)
                ->update([
                    'global_invoice_id'   => null,
                    'global_invoice_date' => null,
                ]);

            DB::commit();
            return response()->json([
                'message'          => 'Factura cancelada correctamente.',
                'num_liberadas'    => DB::table('local_transaction')->whereNull('global_invoice_id')->count(), // informativo
                'cancelada_at'     => $globalInvoice->cancelada_at,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[FacturacionController] Error al actualizar BD tras cancelación', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Factura cancelada en SAT pero error al actualizar BD: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descarga XML de factura global
     */  
    public function downloadXml($name)
    {
        $path = storage_path('app/public/invoices/' . $name . '.xml');
        if (!file_exists($path)) {
            abort(404, 'Archivo XML no encontrado.');
        }
        return response()->download($path, $name . '.xml', ['Content-Type' => 'text/xml']);
    }
 
    /**
     * Descarga PDF de factura global
     */
    public function downloadPdf($name)
    {
        $path = storage_path('app/public/invoices/' . $name . '.pdf');
        if (!file_exists($path)) {
            abort(404, 'Archivo PDF no encontrado.');
        }
        return response()->download($path, $name . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    // ─────────────────────────────────────────────
    // Métodos privados auxiliares
    // ─────────────────────────────────────────────

    private function saveInvoiceFile(string $name, string $pdf, string $xml): void
    {
        $pdfPath = storage_path('app/public/invoices/' . $name . '.pdf');
        $xmlPath = storage_path('app/public/invoices/' . $name . '.xml');
        file_put_contents($pdfPath, base64_decode($pdf));
        file_put_contents($xmlPath, $xml);
    }

    private function obtenerTokenProduccion(): array
    {
        $cacheKey = 'facturoporti_prod_token';

        if (Cache::has($cacheKey)) {
            return ['token' => Cache::get($cacheKey), 'error' => null];
        }

        $api      = $this->catalogs->api_data_reference['api'];
        $username = $this->catalogs->api_data_reference['username'];
        $password = $this->catalogs->api_data_reference['password'];

        // Usar curl con SSL_VERIFYPEER false (igual que AquaFacturacion)
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $api . '/token/crear?Usuario=' . $username . '&Password=' . $password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
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
            // Sin tiempo de expiración — el token de FacturoPorTi no expira
            Cache::forever($cacheKey, $data['token']);
            return ['token' => $data['token'], 'error' => null];
        }

        return [
            'token' => null,
            'error' => [
                'api'      => $api,
                'username' => $username,
                'status'   => $httpCode,
                'body'     => $body,
                'json'     => $data,
            ]
        ];
    }

    private function callAPIWithToken(string $endpoint, array $data, string $token): array
    {
        $url  = $this->catalogs->api_data_reference['api'] . $endpoint;
        $json = json_encode($data);

        $result = $this->curlPost($url, $json, $token);

        // Si token rechazado (401), renovar y reintentar una vez
        if (($result['status'] ?? 0) === 401) {
            Log::warning('[FacturacionController] Token rechazado (401). Renovando...');
            Cache::forget('facturoporti_prod_token');
            $newTokenResult = $this->obtenerTokenProduccion();
            $newToken = $newTokenResult['token'];

            if (!$newToken) {
                return [
                    'estatus' => [
                        'codigo'                   => '401',
                        'descripcion'              => 'Token expirado y no se pudo renovar.',
                        'detieneEjecucionProveedor' => true,
                    ]
                ];
            }

            $result = $this->curlPost($url, $json, $newToken);
        }

        $decoded = json_decode($result['body'], true);

        if ($decoded === null) {
            Log::error('[FacturacionController] Respuesta no es JSON', ['body' => $result['body']]);
        }

        return $decoded ?? [];
    }

    private function curlPost(string $url, string $jsonBody, string $token): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
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
