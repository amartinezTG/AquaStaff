<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdministracionController extends Controller
{
    /**
     * Mostrar vista principal de administración
     */
    public function index()
    {
        $activePage = 'administracion';
        return view('administracion.index', compact('activePage'));
    }

    /**
     * Obtener resumen de transacciones faltantes por día
     */
    public function getTransactionGapsSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'atm' => 'required|in:AQUA01,AQUA02,TODOS'
        ]);

        $startDate = $request->input('start_date') . ' 00:00:01';
        $endDate = $request->input('end_date') . ' 23:59:59';
        $atm = $request->input('atm');

        try {
            $results = [];
            $cajeros = $atm === 'TODOS' ? ['AQUA01', 'AQUA02'] : [$atm];

            foreach ($cajeros as $cajero) {
                $query = DB::select("
                    WITH por_dia AS (
                        SELECT
                            DATE(TransationDate) AS dia,
                            MIN(`_id`) AS lo,
                            MAX(`_id`) AS hi,
                            COUNT(*) AS cnt
                        FROM local_transaction
                        WHERE TransationDate BETWEEN ? AND ?
                            AND atm = ?
                        GROUP BY DATE(TransationDate)
                    )
                    SELECT
                        dia,
                        lo, 
                        hi, 
                        cnt,
                        (hi - lo + 1) AS esperados,
                        (hi - lo + 1) - cnt AS faltantes,
                        CASE WHEN (hi - lo + 1) - cnt > 0 THEN 1 ELSE 0 END AS hay_huecos,
                        ROUND(((hi - lo + 1) - cnt) / (hi - lo + 1) * 100, 2) AS porcentaje_faltante
                    FROM por_dia
                    ORDER BY dia DESC
                ", [$startDate, $endDate, $cajero]);

                $results[$cajero] = $query;
            }

            // Calcular estadísticas generales
            $estadisticas = $this->calculateStatistics($results);

            return response()->json([
                'success' => true,
                'data' => $results,
                'estadisticas' => $estadisticas,
                'periodo' => [
                    'inicio' => $request->input('start_date'),
                    'fin' => $request->input('end_date')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener datos de transacciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle de transacciones faltantes (huecos específicos)
     */
    public function getTransactionGapsDetail(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'atm' => 'required|in:AQUA01,AQUA02',
            'limit' => 'nullable|integer|min:10|max:1000'
        ]);

        $startDate = $request->input('start_date') . ' 00:00:01';
        $endDate = $request->input('end_date') . ' 23:59:59';
        $atm = $request->input('atm');
        $limit = $request->input('limit', 100);

        try {
            $query = DB::select("
                WITH base AS (
                    SELECT
                        `_id`,
                        local_transaction_id,
                        TransationDate
                    FROM local_transaction
                    WHERE TransationDate BETWEEN ? AND ?
                        AND atm = ?
                ),
                ord AS (
                    SELECT
                        `_id` AS id_actual,
                        LAG(`_id`) OVER (ORDER BY TransationDate, `_id`) AS id_anterior,
                        TransationDate
                    FROM base
                )
                SELECT
                    id_actual,
                    id_anterior,
                    (id_anterior + 1) AS id_esperado,
                    (id_actual - (id_anterior + 1)) AS diferencia,
                    DATE_FORMAT(TransationDate, '%d/%m/%Y %H:%i') AS fecha_hora,
                    TransationDate as fecha_original
                FROM ord
                WHERE (id_actual - (id_anterior + 1)) > 0
                ORDER BY TransationDate DESC, id_actual DESC
                LIMIT ?
            ", [$startDate, $endDate, $atm, $limit]);

            return response()->json([
                'success' => true,
                'data' => $query,
                'total_huecos' => count($query),
                'atm' => $atm,
                'limite' => $limit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener detalle de huecos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar reporte de transacciones faltantes a CSV
     */
    public function exportTransactionGaps(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'atm' => 'required|in:AQUA01,AQUA02,TODOS'
        ]);

        $startDate = $request->input('start_date') . ' 00:00:01';
        $endDate = $request->input('end_date') . ' 23:59:59';
        $atm = $request->input('atm');

        $cajeros = $atm === 'TODOS' ? ['AQUA01', 'AQUA02'] : [$atm];
        $allData = [];

        foreach ($cajeros as $cajero) {
            $query = DB::select("
                WITH por_dia AS (
                    SELECT
                        DATE(TransationDate) AS dia,
                        MIN(`_id`) AS lo,
                        MAX(`_id`) AS hi,
                        COUNT(*) AS cnt
                    FROM local_transaction
                    WHERE TransationDate BETWEEN ? AND ?
                        AND atm = ?
                    GROUP BY DATE(TransationDate)
                )
                SELECT
                    ? as cajero,
                    dia,
                    lo, 
                    hi, 
                    cnt,
                    (hi - lo + 1) AS esperados,
                    (hi - lo + 1) - cnt AS faltantes,
                    ROUND(((hi - lo + 1) - cnt) / (hi - lo + 1) * 100, 2) AS porcentaje_faltante
                FROM por_dia
                ORDER BY dia DESC
            ", [$startDate, $endDate, $cajero, $cajero]);

            $allData = array_merge($allData, $query);
        }

        // Generar CSV
        $filename = "transacciones_faltantes_{$atm}_" . date('Y-m-d_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'Cajero', 'Fecha', 'ID Mínimo', 'ID Máximo', 'Transacciones Reales',
            'Transacciones Esperadas', 'Transacciones Faltantes', '% Faltante'
        ]);
        
        // Datos
        foreach ($allData as $row) {
            fputcsv($output, [
                $row->cajero,
                $row->dia,
                $row->lo,
                $row->hi,
                $row->cnt,
                $row->esperados,
                $row->faltantes,
                $row->porcentaje_faltante . '%'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Calcular estadísticas generales
     */
    private function calculateStatistics($results)
    {
        $stats = [];

        foreach ($results as $cajero => $data) {
            $totalDias = count($data);
            $diasConHuecos = array_reduce($data, function($carry, $item) {
                return $carry + ($item->hay_huecos ? 1 : 0);
            }, 0);
            
            $totalFaltantes = array_reduce($data, function($carry, $item) {
                return $carry + $item->faltantes;
            }, 0);
            
            $totalEsperadas = array_reduce($data, function($carry, $item) {
                return $carry + $item->esperados;
            }, 0);

            $stats[$cajero] = [
                'total_dias' => $totalDias,
                'dias_con_huecos' => $diasConHuecos,
                'dias_sin_huecos' => $totalDias - $diasConHuecos,
                'total_transacciones_faltantes' => $totalFaltantes,
                'total_transacciones_esperadas' => $totalEsperadas,
                'porcentaje_dias_con_problemas' => $totalDias > 0 
                    ? round(($diasConHuecos / $totalDias) * 100, 2) 
                    : 0,
                'porcentaje_transacciones_faltantes' => $totalEsperadas > 0
                    ? round(($totalFaltantes / $totalEsperadas) * 100, 2)
                    : 0
            ];
        }

        return $stats;
    }

    /**
     * Obtener estadísticas rápidas para dashboard
     */
    public function getQuickStats()
    {
        try {
            $hoy = date('Y-m-d');
            $hace7Dias = date('Y-m-d', strtotime('-7 days'));

            $statsAQUA01 = $this->getATMQuickStats('AQUA01', $hace7Dias, $hoy);
            $statsAQUA02 = $this->getATMQuickStats('AQUA02', $hace7Dias, $hoy);

            return response()->json([
                'success' => true,
                'AQUA01' => $statsAQUA01,
                'AQUA02' => $statsAQUA02,
                'periodo' => 'Últimos 7 días'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas rápidas por cajero
     */
    private function getATMQuickStats($atm, $startDate, $endDate)
    {
        $result = DB::selectOne("
            SELECT
                COUNT(DISTINCT DATE(TransationDate)) as dias_con_transacciones,
                COUNT(*) as total_transacciones,
                MIN(`_id`) as min_id,
                MAX(`_id`) as max_id,
                ((MAX(`_id`) - MIN(`_id`) + 1) - COUNT(*)) as transacciones_faltantes
            FROM local_transaction
            WHERE TransationDate BETWEEN ? AND ?
                AND atm = ?
        ", [$startDate . ' 00:00:01', $endDate . ' 23:59:59', $atm]);

        return $result;
    }
}