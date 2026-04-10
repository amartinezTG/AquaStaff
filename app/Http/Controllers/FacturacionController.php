<?php

namespace App\Http\Controllers;

use App\Models\GeneralCatalogs;
use App\Models\GlobalInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
                lt.integrate_cp,
                lt.integrate_cp_date,
                gi.name AS factura_global_nombre,
                CASE
                    WHEN lt.fiscal_invoice IS NOT NULL THEN 'individual'
                    WHEN lt.integrate_cp  IS NOT NULL  THEN 'global'
                    ELSE 'pendiente'
                END AS estatus_factura
            FROM local_transaction lt
            LEFT JOIN global_invoice gi ON lt.integrate_cp = gi.id
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
        $ids         = $request->input('ids', []);
        $periodicidad = $request->input('periodicidad', '04');

        if (empty($ids)) {
            return response()->json(['error' => 'No se seleccionaron transacciones.'], 400);
        }

        $transactions = DB::table('local_transaction')
            ->whereIn('local_transaction_id', $ids)
            ->whereNull('integrate_cp')
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

        // Obtener token
        $tokenManager = app(\App\Services\TokenManager::class);
        $token = $tokenManager->getToken();

        if (!$token) {
            return response()->json(['error' => 'No se pudo obtener el token de autenticación.'], 500);
        }

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
                        'Fecha'           => Carbon::now('America/Mexico_City')->format('Y-m-d\TH:i:s'),
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
                            'producto'         => 'VENTA GLOBAL SERVICIOS DE LAVADO',
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

                if (!empty($response['estatus']['detieneEjecucionProveedor'])) {
                    DB::rollBack();
                    return response()->json([
                        'error' => $response['estatus']['descripcion'] ?? 'Error al timbrar.'
                    ], 500);
                }

                if (($response['estatus']['codigo'] ?? '') === '000') {
                    $globalInvoice = GlobalInvoice::create([
                        'name'             => $invoiceName,
                        'total'            => $total,
                        'start_date_group' => $startDateGroup,
                        'end_date_group'   => $endDateGroup,
                        'paymentType'      => $paymentType,
                    ]);

                    $pdf = $response['cfdiTimbrado']['respuesta']['pdf']     ?? null;
                    $xml = $response['cfdiTimbrado']['respuesta']['cfdixml'] ?? null;

                    if ($pdf && $xml) {
                        $this->saveInvoiceFile($invoiceName, $pdf, $xml);
                    }

                    DB::table('local_transaction')
                        ->whereIn('local_transaction_id', $group->pluck('local_transaction_id'))
                        ->whereNull('integrate_cp')
                        ->update([
                            'integrate_cp'      => $globalInvoice->id,
                            'integrate_cp_date' => now(),
                        ]);

                    $generatedInvoices[] = [
                        'id'          => $globalInvoice->id,
                        'name'        => $invoiceName,
                        'total'       => $total,
                        'transacciones' => $group->count(),
                    ];
                } else {
                    DB::rollBack();
                    $errMsg = $response['estatus']['informacionTecnica'] ?? $response['estatus']['descripcion'] ?? 'Error desconocido';
                    return response()->json(['error' => 'Error al timbrar: ' . $errMsg], 500);
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
                gi.total,
                gi.start_date_group,
                gi.end_date_group,
                gi.paymentType,
                gi.created_at,
                COUNT(lt.local_transaction_id) AS num_transacciones
            FROM global_invoice gi
            LEFT JOIN local_transaction lt ON lt.integrate_cp = gi.id
            GROUP BY gi.id
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
                'id'                => $row->id,
                'name'              => $row->name,
                'total'             => $row->total,
                'start_date_group'  => $row->start_date_group,
                'end_date_group'    => $row->end_date_group,
                'payment_type'      => $row->paymentType,
                'payment_type_nombre' => $paymentNames[$row->paymentType] ?? 'Desconocido',
                'num_transacciones' => $row->num_transacciones,
                'created_at'        => $row->created_at,
            ];
        }, $rows);

        return response()->json(['data' => $data]);
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

    private function callAPIWithToken(string $endpoint, array $data, string $token): array
    {
        $url = $this->catalogs->api_data_reference['api'] . $endpoint;

        $headers = [
            'Authorization' => 'Bearer ' . trim($token),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];

        $response = Http::withHeaders($headers)->post($url, $data);

        if ($response->unauthorized()) {
            Log::warning('Token expirado en FacturacionController. Renovando...');
            $tokenManager = app(\App\Services\TokenManager::class);
            $tokenManager->clearCache();
            $newToken = $tokenManager->getToken();

            if (!$newToken) {
                return [
                    'estatus' => [
                        'codigo'                   => '401',
                        'descripcion'              => 'Token expirado y no se pudo renovar.',
                        'detieneEjecucionProveedor' => true,
                    ]
                ];
            }

            $headers['Authorization'] = 'Bearer ' . trim($newToken);
            $response = Http::withHeaders($headers)->post($url, $data);
        }

        if ($response->failed()) {
            Log::error('Error HTTP en FacturacionController', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json() ?? [];
    }
}
