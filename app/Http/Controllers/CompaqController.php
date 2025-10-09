<?php

namespace App\Http\Controllers;

use App\Models\GeneralCatalogs;
use App\Models\LocalTransaction;
use App\Models\CompaqIntegration;
use App\Models\GlobalInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;


class CompaqController extends Controller
{
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    public function index(Request $request){
        $catalogs   = new GeneralCatalogs();
        $activePage = 'compaq';

        // Obtener las fechas de inicio y fin
        $startDate = $this->getStartDate($request->start_date);
        $endDate   = $this->getEndDate($request->end_date);

        /*$Invoices  = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
        ->where('integrate_cp', null)->get();*/


        //$Invoices  = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
        //->where('integrate_cp', null)->get();

        $query = LocalTransaction::whereBetween('TransationDate', [$startDate . ' 00:00:01', $endDate. ' 23:59:59'])
                ->where('integrate_cp', null);

        $Global_Invoice = GlobalInvoice::orderBy('id', 'desc')->get();

        // Agregar la condición para PaymentType si existe la variable
        $paymentType=$request->payment_type;

        $periodicity=$request->periodicity;

        if (isset($paymentType)) {

            $query->where('PaymentType', $paymentType);
        }

        $fiscal_invoice=$request->fiscal_invoice;
        if (isset($fiscal_invoice)) {
            if ($fiscal_invoice=='Factura') {
                $query->whereNotNull('fiscal_invoice');
            }else if($fiscal_invoice=='No Facturado') {
                $query->whereNull('fiscal_invoice');
            }

        }

        // Agregar la condición para facility si existe la variable
        $facility=$request->facility;
        if (isset($facility)) {
            $query->where('facility', $facility);
        }

        //dd($query->toSql(), $query->getBindings());
        //DB::enableQueryLog();
        $Invoices = $query->get();
        //dd(DB::getQueryLog());

        //die();
        $Downloads   = CompaqIntegration::orderBy('id', 'desc')->get();

        $compaqFiles = CompaqIntegration::orderBy('created_at', 'desc')->get();

        return view('compaq.compaq', compact('activePage', 'Invoices', 'startDate', 'endDate', 'Downloads', 'catalogs', 'paymentType', 'facility', 'periodicity', 'Global_Invoice', 'fiscal_invoice', 'compaqFiles'));
    }


/*==============================================================================================================================*/
    public function process_compaq(Request $request)
    {
        $selectedIds = $request->selectedIds;
        $now = now();
        $nombre = strtoupper(substr(md5(uniqid()), 0, 7));

        $compaqIntegration = CompaqIntegration::create(['name' => $nombre]);

        LocalTransaction::whereIn('local_transaction_id', $selectedIds)
            ->whereNull('integrate_cp')
            ->update([
                'integrate_cp' => $compaqIntegration->id,
                'integrate_cp_date' => $now
            ]);

        $transactions = LocalTransaction::whereIn('local_transaction_id', $selectedIds)->get();

        $lineas = array_merge(
            [$this->generarLineaP($compaqIntegration->id)],
            $this->generarLineasM1($transactions)
        );

        $fileName = $this->escribirArchivoPoliza($lineas, $nombre);

        // Guarda el path del archivo generado para histórico
        $compaqIntegration->update(['file_path' => 'public/files/' . $fileName]);

        // Devuelve el archivo para descarga inmediata
        return response()->download(storage_path('app/public/files/' . $fileName));
    }

    private function generarLineaP($id)
    {
        $fecha = now()->format('Ymd');
        $folioPoliza = str_pad($id, 5, '0', STR_PAD_LEFT);
        $concepto = "POLIZA AUTOMATICA DE LAVADOS";

        return sprintf(
            "%-2s%-10s%4s%10s%-2s%2s%-80s%3s%2s%2s%-36s",
            'P', $fecha, '3', $folioPoliza,
            '1', '0', $concepto, '11', '0', '0', ''
        );
    }

    private function generarLineasM1($transactions)
    {
        $lineas = [];

        foreach ($transactions as $trx) {
            $fechaAplica = date('Ymd', strtotime($trx->TransationDate));
            $importe = number_format($trx->Total, 2, '.', '');
            $concepto = 'Ingreso de lavado: ' . $trx->local_transaction_id;

            $lineas[] = $this->formatoM1('40116000', $trx->PaymentFolio, '0', $importe, $concepto, $trx->fiscal_invoice, $fechaAplica); // Cargo
            $lineas[] = $this->formatoM1('10501001', $trx->PaymentFolio, '1', $importe, $concepto, $trx->fiscal_invoice, $fechaAplica); // Abono
        }

        return $lineas;
    }

    private function formatoM1($cuenta, $referencia, $tipoMovto, $importe, $concepto, $uuid, $fecha)
    {
        return sprintf(
            "%-2s%-30s%30s%2s%17s%15s%10s%-120s%-36s%-10s",
            'M1',
            $cuenta,
            $referencia,
            $tipoMovto,
            $importe,
            '0',
            '0.0',
            $concepto,
            $uuid,
            $fecha
        );
    }

    private function escribirArchivoPoliza(array $lineas, string $nombre)
    {
        $fileName = 'poliza_' . $nombre . '.txt';
        $filePath = storage_path('app/public/files/' . $fileName);
        $file = fopen($filePath, 'w');

        foreach ($lineas as $linea) {
            fwrite($file, $linea . "\r\n");
        }

        fclose($file);
        return $fileName;
    }

    public function DetailCompaq($id)
    {
        $activePage = 'compaq';
        $CompaqDetalle = CompaqIntegration::find($id);
        $CompaqIntegration = CompaqIntegration::with('localTransactions')->find($id);

        if ($CompaqIntegration) {
            return view('compaq.compaq_integration', compact('activePage', 'CompaqDetalle', 'CompaqIntegration'));
        }
    }


    public function compaqHistory()
    {
        $activePage = 'compaq';
        $compaqList = CompaqIntegration::latest()->get();
        return view('compaq.compaq_history', compact('activePage', 'compaqList'));
    }

    public function downloadCompaqFile($name)
    {
        $path = storage_path('app/public/files/poliza_' . $name . '.txt');
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path);
    }


    public function history()
    {
        $compaqFiles = CompaqIntegration::orderBy('created_at', 'desc')->get();
        return view('compaq.compaq_history', compact('compaqFiles'));
    }


/*==============================================================================================================================*/


    public function DetailGlobalInvoice($id){
        $activePage = 'compaq';
        $catalogs   = new GeneralCatalogs();
        $GlobalInvoice            = GlobalInvoice::where('id', $id)->first();
     
        $LocalTransaction = LocalTransaction::where('global_invoice', $GlobalInvoice->id)->get();
            if ($GlobalInvoice) {
                return view('global_invoice_integration', compact('activePage', 'GlobalInvoice', 'LocalTransaction', 'catalogs')); 
            }

    }

    public function downloadInvoiceXML($folio){
        $path = storage_path('app/public/invoices/' . $folio . '.xml');
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: text/xml'); // Especifica el MIME type para XML
            header('Content-Disposition: attachment; filename=' . basename($path));

            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            readfile($path);
            exit;
        } else {
            // Manejar el caso en que el archivo no existe
        }
    }

    public function downloadInvoicePDF($folio){
        $path = storage_path('app/public/invoices/' . $folio . '.pdf');
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($path));

            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            readfile($path);
            exit;
        } else {
            // Manejar el caso en que el archivo no existe
        }
    }




    public function CompaqFile($folio){
        $path = storage_path('app/public/files/poliza_' . $folio . '.txt');
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($path));

            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            readfile($path);
            exit;
        } else {
            // Manejar el caso en que el archivo no existe
        }
    }



    public function downloadTXT($name){
        $path = storage_path('app/public/files/poliza_' . $name . '.txt'); // Ajusta la ruta si es necesario

            if (file_exists($path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: text/plain'); // Especifica el MIME type para XML
                header('Content-Disposition: attachment; filename=' . basename($path));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate,  post-check=0, pre-check=0');
                header('Pragma: public');

                readfile($path);
                exit;
            } else {
                die('<h5>Files does no exits</h5>');
                // Manejar el caso en que el archivo no existe
            }

    }

    private function getStartDate($start_date){
        if($start_date){
            return $start_date;
        }else{
            return Carbon::now()->subDays(1)->format('Y-m-d');
        }
    }

    private function getEndDate($end_date){
        if($end_date){
            return $end_date;
        }else{
            return Carbon::now()->format('Y-m-d');
        }
    }

    public function SaveInvoiceFile( $reference_id, $pdf, $xml) {
        $response = array(
            'success' => true,
            'errors' => array(
                'friendly' => ''
            )
        );

        $pdf_data = base64_decode($pdf);
        //$pdf_path = $folder_path.'/'.$reference_id.'.pdf';
        $pdf_path = storage_path('app/public/invoices/' . $reference_id . '.pdf');

        file_put_contents($pdf_path, $pdf_data);

        //$xml_path = $folder_path.'/'.$reference_id.'.xml';
        $xml_path = storage_path('app/public/invoices/' . $reference_id . '.xml');
        file_put_contents($xml_path, $xml);

        return $response;
    }

    public function CallAPI($endpoint, $data)
    {
        $tokenManager = app(\App\Services\TokenManager::class);
        $token = $tokenManager->getToken();

        if (!$token) {
            Log::error('❌ Token no obtenido desde TokenManager.');
            return [
                'success' => false,
                'error' => 'No se pudo obtener el token de autenticación.'
            ];
        }

        //Log::info("📡 Llamando a endpoint {$endpoint} con token:", ['token' => $token]);

        $url = $this->catalogs->api_data_reference['api'] . $endpoint;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . trim($token),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post($url, $data);

        /*Log::info('📨 HTTP Response Info:', [
            'url'     => $url,
            'status'  => $response->status(),
            'headers' => $response->headers(),
        ]);*/

        //Log::info('📥 Respuesta cruda de API:', ['response' => $response->body()]);

        // Si el token fue rechazado (401), intenta 1 vez más
        if ($response->unauthorized()) {
            //Log::warning('⚠️ Token posiblemente expirado. Eliminando de cache.');
            $tokenManager->clearCache();

            // Reintento
            $newToken = $tokenManager->getToken();
            if (!$newToken) {
                return [
                    'success' => false,
                    'error'   => 'No se pudo renovar el token tras recibir 401.'
                ];
            }

            //Log::info("🔁 Reintentando llamada a {$endpoint} con nuevo token:", ['token' => $newToken]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($newToken),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($url, $data);

            /*Log::info('📨 HTTP Retry Response Info:', [
                'url'     => $url,
                'status'  => $response->status(),
                'headers' => $response->headers(),
            ]);

            Log::info('📥 Retry - Respuesta cruda de API:', ['response' => $response->body()]);*/
        }

        // Evaluar si aún fue 401
        if ($response->unauthorized()) {
            return [
                'success'  => false,
                'error'    => 'Error de autenticación persistente tras reintento.',
                'status'   => 401,
                'response' => json_decode($response->body(), true)
            ];
        }

        return $response->json();
    }



    public function process_global_invoice(Request $request)
    {
        $catalogs = new GeneralCatalogs();
        $selectedIds = $request->selectedIds;
        $periodicity = $request->periodicity;

        if (empty($selectedIds)) {
            return response()->json(['error' => 'No se seleccionaron transacciones.'], 400);
        }

        $transactions = LocalTransaction::whereIn('local_transaction_id', $selectedIds)
            ->whereNull('integrate_cp')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json(['error' => 'No hay transacciones válidas para facturar.'], 400);
        }

        if (!$this->validateGlobalInvoiceDates($transactions)) {
            return response()->json(['error' => 'Las transacciones seleccionadas no cumplen con los requisitos de fecha para la factura global (primer día del mes anterior o misma semana).'], 400);
        }

        if (!$this->validatePeriodicity($periodicity, $transactions)) {
            return response()->json(['error' => 'Las transacciones no coinciden con la periodicidad seleccionada.'], 400);
        }

        $groupedTransactions = [];

        $transactions->groupBy('PaymentType')->each(function ($paymentGroup, $paymentType) use (&$groupedTransactions) {
            if ((int)$paymentType === 2) {
                // Agrupar por cajero para tarjeta
                foreach ($paymentGroup->groupBy('cashier_id') as $cashierGroup) {
                    $groupedTransactions[] = $cashierGroup;
                }
            } else {
                // Solo efectivo, sin subagrupación
                $groupedTransactions[] = $paymentGroup;
            }
        });

        /*dd([
            'selectedIds' => $selectedIds,
            'groupedTransactionsKeys' => $groupedTransactions->keys(),
            'groupedTransactionsCount' => $groupedTransactions->count(),
            'groupedTransactions' => $groupedTransactions->toArray(),
        ]);*/

        $generatedInvoices = [];
        $response = null;

        // Obtener token solo una vez
        /*$tokenManager = app(\App\Services\TokenManager::class);
        $token = $tokenManager->getToken();*/

        $token = "eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoialYrdVVUYmtWNmUxRmNZb2cvNWtGQT09IiwibmJmIjoxNzQ0MzMyMDQ0LCJleHAiOjE3NDY5MjQwNDQsImlzcyI6IlNjYWZhbmRyYVNlcnZpY2lvcyIsImF1ZCI6IlNjYWZhbmRyYSBTZXJ2aWNpb3MiLCJJZEVtcHJlc2EiOiJqVit1VVRia1Y2ZTFGY1lvZy81a0ZBPT0iLCJJZFVzdWFyaW8iOiJidXlaYzFMWUl5VURaSGhGR3NqaGdRPT0ifQ.41TJf_JJldjs-s12TrrI3bkv2R1eDe1xUPC4C0sx_7w";

        if (!$token) {
            return response()->json(['error' => 'No se pudo obtener el token de autenticación.'], 500);
        }

        DB::beginTransaction();
        try {
            foreach ($groupedTransactions as $group) {
                $totalGroup = $group->sum('Total');

                $base  = round($totalGroup / 1.08, 2);
                $iva   = round($totalGroup - $base, 2);
                $total = round($base + $iva, 2);

                Log::error('Error al timbrar la factura global - TOTAL: '.$base . ' - ' .$iva. '- '.$total, array() );


                if ($totalGroup > 0) {
                    $now = now();
                    $startDateGroup  = $group->min('TransationDate');
                    $endDateGroup    = $group->max('TransationDate');
                    $paymentType     = $group->first()->PaymentType;
                    $paymentTypeName = $catalogs->folio_payment_type[$paymentType] ?? 'Desconocido';
                    $cashierInfo     = ($paymentType === 2 && $group->first()->cashier_id) ? ' - Cajero ' . $group->first()->cashier_id : '';
                    $invoiceName = substr('GLOBAL_' . Carbon::parse($startDateGroup)->format('Ymd') . '_' . Carbon::parse($endDateGroup)->format('Ymd') . '_' . str_replace(' ', '_', $paymentTypeName) . $cashierInfo, 0,  60      );

                    $orderJSON = [
                    'DatosGenerales' => [
                        'Version'           => '4.0',
                        'CSD'               => $this->catalogs->api_data_reference['CSD'],
                        'LlavePrivada'      => $this->catalogs->api_data_reference['CSDKey'],
                        'CSDPassword'       => $this->catalogs->api_data_reference['CSDPassword'],
                        'GeneraPDF'         => true,
                        'Logotipo'          => '',
                        'CFDI'              => 'Factura',
                        'OpcionDecimales'   => 2,
                        'NumeroDecimales'   => 2,
                        'TipoCFDI'          => 'Ingreso',
                        'EnviaEmail'        => false,
                        'ReceptorEmail'     => '',
                        'ReceptorEmailCC'   => '',
                        'ReceptorEmailCCO'  => '',
                        'EmailMensaje'      => 'Factura global de servicios de lavado del ' . Carbon::parse($startDateGroup)->format('Y-m-d') . ' al ' . Carbon::parse($endDateGroup)->format('Y-m-d'),
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
                            'NombreRazonSocial'=> 'PUBLICO EN GENERAL',
                            'UsoCFDI'           => 'S01',
                            'RegimenFiscal'     => '616',
                            'Direccion' => [
                                'CodigoPostal' => '32030',
                            ]
                        ],
                        'InformacionFacturaGlobal' => [
                            'Periodicidad' => $periodicity,
                            'Meses'        => Carbon::parse($startDateGroup)->format('m'),
                            'Año'          => intval(Carbon::parse($startDateGroup)->format('Y')),
                        ],
                        'Fecha'           => Carbon::now('America/Mexico_City')->format('Y-m-d\TH:i:s'),//now()->format('Y-m-d\TH:i:s'),
                        'Serie'           => 'AB',
                        'Folio'           => '100',
                        'MetodoPago'      => 'PUE',
                        'FormaPago'       => $this->catalogs->payment_method[$paymentTypeName],//'01',
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
                            "Serie"=> "0000012345",
                            'Unidad'           => 'Servicio',
                            'CodigoProducto'   => '84111506',
                            'producto'      => 'VENTA GLOBAL SERVICIOS DE LAVADO',
                            'PrecioUnitario'   => $base,
                            'Importe'          => $base,
                            'ObjetoDeImpuesto' => '02',
                            'Impuestos' => [
                                [
                                    'TipoImpuesto'    => 1,
                                    'Impuesto'        => 2, // 002 = IVA
                                    'Factor'          => 1,
                                    'Base'            => $base,
                                    'Tasa'            => '0.080000',
                                    'ImpuestoImporte' => $iva,
                                ]
                            ]
                        ]
                    ]
                ];



                //Log::info('Solicitud a la API de timbrado:', $orderJSON);
                $response = $this->CallAPIWithToken('/servicios/timbrar/json', $orderJSON, $token);

                if ($response['estatus']['detieneEjecucionProveedor']) {
                    //Log::error('Error al timbrar la factura global - Respuesta de la API:'. $base . ' - ' .$iva. '- '.$total, $response);
                    DB::rollBack();
                    return response()->json(['error' => $response['estatus']['descripcion']], 500);
                }

                if ($response['estatus']['codigo'] === '000') {
                    $GlobalInvoice = GlobalInvoice::create([
                        'name'             => $invoiceName,
                        'total'            => $total,
                        'start_date_group' => $startDateGroup,
                        'end_date_group'   => $endDateGroup,
                        'paymentType'      => $paymentType 
                    ]);

                    $pdf = $response['cfdiTimbrado']['respuesta']['pdf'];
                    $xml = $response['cfdiTimbrado']['respuesta']['cfdixml'];
                    $this->SaveInvoiceFile($invoiceName, $pdf, $xml);

                     // ✅ Marca las transacciones como integradas
                    LocalTransaction::whereIn('local_transaction_id', $group->pluck('local_transaction_id'))
                    ->whereNull('integrate_cp')
                    ->update([
                        'integrate_cp' => $GlobalInvoice->id,
                        'integrate_cp_date' => now(), // o $now si ya lo tienes declarado
                    ]);

                    $generatedInvoices[] = $GlobalInvoice;
                } else {
                    //Log::error('Error al timbrar la factura global - Respuesta de la API:', $response);
                    DB::rollBack();
                    return response()->json(['error' => 'Error al timbrar la factura global: ' . ($response['estatus']['informacionTecnica'] ?? 'Error desconocido')], 500);
                }
            }
        }

        DB::commit();
        return response()->json([
                'message' => 'Facturas globales generadas con éxito.', 
                'invoices' => $generatedInvoices]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('1. Ocurrió un error al procesar la factura global', ['response' => $response]);
        return response()->json(['error' => 'Ocurrió un error al procesar la factura global: ' . $e->getMessage()], 500);
    }
}

public function CallAPIWithToken(string $endpoint, array $data, string $token)
{
    $url = $this->catalogs->api_data_reference['api'] . $endpoint;
    $headers = [
        'Authorization' => 'Bearer ' . trim($token),
        'Accept'        => 'application/json',
        'Content-Type'  => 'application/json',
    ];

    Log::info("📡 Enviando solicitud a {$url} con token inicial");

    $response = Http::withHeaders($headers)->post($url, $data);

    Log::info("📥 Respuesta inicial (status {$response->status()})", [
        'body' => $response->body(),
    ]);

    // Si token es inválido, intenta renovarlo UNA VEZ
    if ($response->unauthorized()) {
        Log::warning("🔁 Token expirado o inválido. Intentando renovar...");

        // Borrar caché del token y obtener uno nuevo
        $tokenManager = app(\App\Services\TokenManager::class);
        $tokenManager->clearCache();
        $newToken = $tokenManager->getToken();

        if (!$newToken) {
            Log::error("❌ No se pudo renovar el token tras 401");
            return [
                'estatus' => [
                    'codigo' => '401',
                    'descripcion' => 'Token expirado y no se pudo renovar',
                    'detieneEjecucionProveedor' => true,
                ]
            ];
        }

        Log::info("✅ Nuevo token obtenido. Reintentando...");

        $headers['Authorization'] = 'Bearer ' . trim($newToken);
        $response = Http::withHeaders($headers)->post($url, $data);

        Log::info("📥 Respuesta tras reintento (status {$response->status()})", [
            'body' => $response->body(),
        ]);
    }

    if ($response->failed()) {
        Log::error("❌ Error HTTP al llamar a la API", [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
    }

    return $response->json();
}


    protected function validateGlobalInvoiceDates($transactions)
    {
        if ($transactions->isEmpty()) {
            return false;
        }

        $today = Carbon::today();
        $firstFourDays = range(1, 4);

        $allDatesLastMonth = $transactions->every(function ($transaction) use ($today) {
            $txDate = Carbon::parse($transaction->TransationDate);
            return $txDate->month === $today->copy()->subMonth()->month &&
                   $txDate->year === $today->copy()->subMonth()->year;
        });

        // Opción 1: dentro de los primeros 4 días del mes, y transacciones del mes anterior
        if (in_array($today->day, $firstFourDays) && $allDatesLastMonth) {
            return true;
        }

        // Opción 2: todas las transacciones en la misma semana calendario
        $firstTxWeek = null;
        foreach ($transactions as $transaction) {
            $txDate = Carbon::parse($transaction->TransationDate);
            $week = $txDate->copy()->startOfWeek()->format('W-Y');
            if ($firstTxWeek === null) {
                $firstTxWeek = $week;
            } elseif ($week !== $firstTxWeek) {
                return false;
            }
        }

        return true;
    }

    private function getFormaPagoForType($paymentType)
    {
        // Mapea tus PaymentType a las claves de $catalogs->folio_payment_type
        // y luego a las claves de FormaPago del SAT (Catálogo c_FormaPago)
        // Este es un ejemplo, debes ajustarlo según tus catálogos
        switch ($paymentType) {
            case 1: // Efectivo (asumiendo que 1 es efectivo)
                return '01'; // Efectivo
            case 2: // Tarjeta (asumiendo que 2 es tarjeta)
                return '04'; // Tarjeta de Crédito (o '03' para Débito, según tu necesidad)
            default:
                return '99'; // Otros
        }
    }


    protected function validatePeriodicity($periodicity, $transactions)
{
    if ($transactions->isEmpty() || !$periodicity) {
        return false;
    }

    $dates = $transactions->pluck('TransationDate')->map(function ($date) {
        return \Carbon\Carbon::parse($date)->startOfDay();
    })->unique();

    switch ($periodicity) {
        case '01': // Diaria
            return $dates->count() === 1;

        case '02': // Semanal
            $weeks = $dates->map(function ($date) {
                return $date->copy()->startOfWeek()->format('W-Y');
            })->unique();
            return $weeks->count() === 1;

        case '03': // Quincenal
            $firstHalf = $dates->every(function ($date) {
                return $date->day <= 15;
            });
            $secondHalf = $dates->every(function ($date) {
                return $date->day > 15;
            });
            return $firstHalf || $secondHalf;

        case '04': // Mensual
            $months = $dates->map(function ($date) {
                return $date->format('Y-m');
            })->unique();
            return $months->count() === 1;

        default:
            return false;
    }
}


}
