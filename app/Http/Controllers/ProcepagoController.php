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
