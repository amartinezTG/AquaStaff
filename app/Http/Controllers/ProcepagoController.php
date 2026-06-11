<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcepagoController extends Controller
{
    public function index()
    {  
        $activePage = 'procepago_importacion';
        return view('procepago.importacion', compact('activePage'));
    }

    /**
     * Recibe el archivo .xlsx/.xlsm, lee la hoja indicada y hace upsert por folio.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xlsm,xls',
            'hoja'    => 'required|string|max:50',
        ]);

        $file       = $request->file('archivo');
        $nombreArchivo = $file->getClientOriginalName();
        $hoja       = trim($request->input('hoja'));

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo leer el archivo: ' . $e->getMessage()], 422);
        }

        if (!$spreadsheet->sheetNameExists($hoja)) {
            $disponibles = implode(', ', $spreadsheet->getSheetNames());
            return response()->json([
                'error' => "La hoja \"$hoja\" no existe en el archivo. Hojas disponibles: $disponibles"
            ], 422);
        }

        $ws = $spreadsheet->getSheetByName($hoja);

        // Leer encabezados desde la primera fila con getCalculatedValue() para tipos reales
        $headerRow = [];
        foreach ($ws->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $headerRow[] = strtolower(trim((string)$cell->getCalculatedValue()));
            }
        }

        if (empty($headerRow)) {
            return response()->json(['error' => 'La hoja está vacía.'], 422);
        }

        foreach (['folio', 'servicio', 'fecha', 'importe'] as $col) {
            if (!in_array($col, $headerRow)) {
                return response()->json(['error' => "Columna requerida no encontrada: $col"], 422);
            }
        }

        $colIdx = array_flip($headerRow); // ['folio'=>0, 'servicio'=>1, ...]

        $insertados = 0;
        $duplicados = 0;
        $errores    = [];
        $userId     = auth()->id();
        $ahora      = now();

        foreach ($ws->getRowIterator(2) as $wsRow) {
            // Leer celdas como valores calculados tipados (numérico real, no string con comas)
            $row = [];
            foreach ($wsRow->getCellIterator() as $cell) {
                $row[] = $cell->getCalculatedValue();
            }

            $folio = $row[$colIdx['folio']] ?? null;
            if (!$folio) continue;

            // Fecha: puede venir como número serial Excel o DateTime
            $fechaRaw = $row[$colIdx['fecha']] ?? null;
            if ($fechaRaw instanceof \DateTime) {
                $fecha = Carbon::instance($fechaRaw)->format('Y-m-d H:i:s');
            } elseif (is_numeric($fechaRaw) && $fechaRaw > 1000) {
                $fecha = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$fechaRaw))->format('Y-m-d H:i:s');
            } else {
                $fecha = Carbon::parse((string)$fechaRaw)->format('Y-m-d H:i:s');
            }

            // Normalizar valor numérico: elimina separadores de miles si llegara como string
            $toFloat = fn($v) => is_string($v)
                ? (float)str_replace([',', ' '], '', $v)
                : (float)($v ?? 0);

            $comisionKey = array_key_exists('comisión', $colIdx)
                ? 'comisión'
                : (array_key_exists('comision', $colIdx) ? 'comision' : null);

            $data = [
                'servicio'       => (string)($row[$colIdx['servicio']] ?? ''),
                'referencia'     => isset($colIdx['referencia']) ? (string)($row[$colIdx['referencia']] ?? '') : null,
                'fecha'          => $fecha,
                'importe'        => $toFloat($row[$colIdx['importe']] ?? 0),
                'comision'       => $comisionKey ? $toFloat($row[$colIdx[$comisionKey]] ?? 0) : 0,
                'iva'            => $toFloat($row[$colIdx['iva']] ?? 0),
                'deposito'       => $toFloat($row[$colIdx['deposito']] ?? 0),
                'archivo_origen' => $nombreArchivo,
                'hoja_origen'    => $hoja,
                'importado_en'   => $ahora,
                'importado_por'  => $userId,
            ];

            try {
                $existe = DB::table('procepago_liquidaciones')->where('folio', $folio)->exists();
                if ($existe) {
                    $duplicados++;
                } else {
                    DB::table('procepago_liquidaciones')->insert(array_merge(['folio' => $folio], $data));
                    $insertados++;
                }
            } catch (\Exception $e) {
                $errores[] = "Folio $folio: " . $e->getMessage();
                Log::error('[Procepago] Error insertando folio ' . $folio, ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'message'    => "Importación completada.",
            'insertados' => $insertados,
            'duplicados' => $duplicados,
            'errores'    => count($errores),
            'detalle_errores' => $errores,
        ]);
    }

    /**
     * DataTable con los registros ya importados.
     */
    public function table(Request $request)
    {
        $desde   = $request->input('fecha_inicio');
        $hasta   = $request->input('fecha_final');
        $servicio = $request->input('servicio', '');
        $hoja    = $request->input('hoja_origen', '');

        $query = DB::table('procepago_liquidaciones as p')
            ->leftJoin('users as u', 'p.importado_por', '=', 'u.id')
            ->select(
                'p.id', 'p.folio', 'p.servicio', 'p.referencia',
                'p.fecha', 'p.importe', 'p.comision', 'p.iva', 'p.deposito',
                'p.archivo_origen', 'p.hoja_origen', 'p.importado_en',
                'u.name as importado_por_nombre'
            );

        if ($desde) $query->whereDate('p.fecha', '>=', $desde);
        if ($hasta)  $query->whereDate('p.fecha', '<=', $hasta);
        if ($servicio) $query->where('p.servicio', $servicio);
        if ($hoja)   $query->where('p.hoja_origen', $hoja);

        $rows = $query->orderBy('p.fecha', 'desc')->get();

        return response()->json(['data' => $rows]);
    }

    // ── Domiciliaciones ───────────────────────────────────────────────────────

    public function domiciliacionesIndex()
    {
        $activePage = 'procepago_domiciliaciones';
        return view('procepago.domiciliaciones', compact('activePage'));
    }

    public function importarDomiciliaciones(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xlsm,xls']);

        $file          = $request->file('archivo');
        $nombreArchivo = $file->getClientOriginalName();
        $ahora         = now();
        $userId        = auth()->id();

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo leer el archivo: ' . $e->getMessage()], 422);
        }

        // Tomar la primera hoja disponible
        $ws = $spreadsheet->getActiveSheet();

        // Leer encabezados (fila 1) normalizados
        $headers = [];
        foreach ($ws->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $headers[] = strtolower(trim((string) $cell->getCalculatedValue()));
            }
        }

        $required = ['folio', 'titular', 'importe', 'concepto', 'referencia', 'fecha', 'periodicidad', 'status', 'tipo'];
        foreach ($required as $col) {
            if (!in_array($col, $headers)) {
                return response()->json(['error' => "Columna requerida no encontrada: \"$col\". Columnas detectadas: " . implode(', ', $headers)], 422);
            }
        }

        $idx = array_flip($headers);

        // Parsear importe: "$1,234.00" → 1234.00
        $toFloat = fn($v) => (float) preg_replace('/[^0-9.]/', '', str_replace(',', '', (string) ($v ?? 0)));

        // Parsear fecha: "2026-04-16 al 2026-04-16" → [inicio, fin]
        $parseFecha = function ($v) {
            $v = trim((string) $v);
            if (str_contains($v, ' al ')) {
                [$ini, $fin] = explode(' al ', $v, 2);
                return [trim($ini) ?: null, trim($fin) ?: null];
            }
            $d = trim($v) ?: null;
            return [$d, $d];
        };

        // Leer todas las filas del archivo y construir mapa folio → datos
        $fileRows = [];
        foreach ($ws->getRowIterator(2) as $wsRow) {
            $row = [];
            foreach ($wsRow->getCellIterator() as $cell) {
                $row[] = $cell->getCalculatedValue();
            }

            $folio = isset($row[$idx['folio']]) ? (int) $row[$idx['folio']] : null;
            if (!$folio) continue;

            [$fechaIni, $fechaFin] = $parseFecha($row[$idx['fecha']] ?? '');

            $fileRows[$folio] = [
                'titular'        => (string) ($row[$idx['titular']] ?? ''),
                'importe'        => $toFloat($row[$idx['importe']] ?? 0),
                'concepto'       => (string) ($row[$idx['concepto']] ?? ''),
                'referencia'     => (string) ($row[$idx['referencia']] ?? ''),
                'fecha_inicio'   => $fechaIni,
                'fecha_fin'      => $fechaFin,
                'periodicidad'   => (string) ($row[$idx['periodicidad']] ?? ''),
                'status'         => (string) ($row[$idx['status']] ?? ''),
                'tipo'           => (string) ($row[$idx['tipo']] ?? ''),
                'archivo_origen' => $nombreArchivo,
                'importado_en'   => $ahora,
                'importado_por'  => $userId,
                'updated_at'     => $ahora,
            ];
        }

        if (empty($fileRows)) {
            return response()->json(['error' => 'El archivo no contiene filas de datos.'], 422);
        }

        $foliosArchivo = array_keys($fileRows);

        // Folios activos en BD
        $enBD = DB::table('procepago_domiciliaciones')
            ->whereNull('deleted_at')
            ->pluck('folio')
            ->toArray();

        // Folios eliminados (soft-deleted) en BD — para restaurar si reaparecen
        $eliminados = DB::table('procepago_domiciliaciones')
            ->whereNotNull('deleted_at')
            ->pluck('folio')
            ->toArray();

        $insertados  = 0;
        $actualizados = 0;
        $restaurados  = 0;
        $eliminadosCnt = 0;
        $errores      = [];

        foreach ($fileRows as $folio => $data) {
            try {
                if (in_array($folio, $eliminados)) {
                    // Estaba borrado → restaurar + actualizar
                    DB::table('procepago_domiciliaciones')
                        ->where('folio', $folio)
                        ->update(array_merge($data, ['deleted_at' => null]));
                    $restaurados++;
                } elseif (in_array($folio, $enBD)) {
                    // Ya existe → actualizar datos
                    DB::table('procepago_domiciliaciones')
                        ->where('folio', $folio)
                        ->update($data);
                    $actualizados++;
                } else {
                    // Nuevo → insertar
                    DB::table('procepago_domiciliaciones')
                        ->insert(array_merge(['folio' => $folio, 'created_at' => $ahora], $data));
                    $insertados++;
                }
            } catch (\Exception $e) {
                $errores[] = "Folio $folio: " . $e->getMessage();
                Log::error('[ProcepagoDom] Error en folio ' . $folio, ['error' => $e->getMessage()]);
            }
        }

        // Soft-delete de los que ya no están en el archivo
        $foliosSoftDelete = array_diff($enBD, $foliosArchivo);
        if (!empty($foliosSoftDelete)) {
            $eliminadosCnt = DB::table('procepago_domiciliaciones')
                ->whereIn('folio', $foliosSoftDelete)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $ahora]);
        }

        return response()->json([
            'message'     => 'Sincronización completada.',
            'insertados'  => $insertados,
            'actualizados' => $actualizados,
            'restaurados' => $restaurados,
            'eliminados'  => $eliminadosCnt,
            'errores'     => count($errores),
            'detalle_errores' => $errores,
        ]);
    }

    public function tablaDomiciliaciones(Request $request)
    {
        $status = $request->input('status', '');
        $tipo   = $request->input('tipo', '');
        $desde  = $request->input('fecha_inicio');
        $hasta  = $request->input('fecha_final');

        $query = DB::table('procepago_domiciliaciones as d')
            ->leftJoin('users as u', 'd.importado_por', '=', 'u.id')
            ->whereNull('d.deleted_at')
            ->select(
                'd.id', 'd.folio', 'd.titular', 'd.importe', 'd.concepto',
                'd.referencia', 'd.fecha_inicio', 'd.fecha_fin',
                'd.periodicidad', 'd.status', 'd.tipo',
                'd.archivo_origen', 'd.importado_en',
                'u.name as importado_por_nombre'
            );

        if ($status)  $query->where('d.status', $status);
        if ($tipo)    $query->where('d.tipo', $tipo);
        if ($desde)   $query->where('d.fecha_inicio', '>=', $desde);
        if ($hasta)   $query->where('d.fecha_inicio', '<=', $hasta);

        $rows = $query->orderBy('d.folio', 'desc')->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * Lista hojas disponibles de un archivo subido (sin guardar).
     */
    public function hojas(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xlsm,xls']);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
            return response()->json(['hojas' => $spreadsheet->getSheetNames()]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo leer el archivo.'], 422);
        }
    }
}
