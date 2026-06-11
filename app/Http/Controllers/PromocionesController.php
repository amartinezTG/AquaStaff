<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\PromocionBulk;
use App\Models\ProyectoQr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class PromocionesController extends Controller
{
    const PACKAGES = [
        '612abcd1c4ce4c141237a356' => 'Deluxe',
        '612f057787e473107fda56aa' => 'Express',
        '612f067387e473107fda56b0' => 'Básico',
        '612f1c4f30b90803837e7969' => 'Ultra',
    ];

    public function index()
    {
        $activePage = 'promociones';
        return view('promociones.index', compact('activePage'));
    }

    public function tabla(Request $request)
    {
        $promociones = Promocion::where('promotion_user', new ObjectId('678ab435ee1026a922940d5b'))->get();

        $data = $promociones->map(function ($promo) {
            $packageId = (string) ($promo->package ?? '');
            $packageName = self::PACKAGES[$packageId] ?? $packageId ?: '—';

            return [
                'id'             => (string) $promo->_id,
                'code'           => $promo->code ?? '—',
                'type'           => $promo->type ?? '—',
                'price'          => $promo->price ?? 0,
                'uses'           => $promo->uses ?? 0,
                'status'         => $promo->status ?? 'N/A',
                'expiration'     => $promo->expiration
                    ? (is_object($promo->expiration)
                        ? $promo->expiration->toDateTime()->format('Y-m-d H:i:s')
                        : $promo->expiration)
                    : '—',
                'lastSync'       => $promo->lastSync
                    ? (is_object($promo->lastSync)
                        ? $promo->lastSync->toDateTime()->format('Y-m-d H:i:s')
                        : $promo->lastSync)
                    : '—',
                'IsSync'         => $promo->IsSync ? 'Sí' : 'No',
                'promotion_user' => (string) ($promo->promotion_user ?? '—'),
                'purchase_order' => (string) ($promo->purchase_order ?? '—'),
                'package'        => $packageName,
                'error'          => $promo->error ?? '—',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request){
        $packageId = array_search($request->package, self::PACKAGES);

        if ($packageId === false) {
            return response()->json(['success' => false, 'message' => 'Paquete inválido'], 422);
        }

        $promo = new Promocion([
            'IsSync'         => true,
            'lastSync'       => new UTCDateTime(now()->getTimestampMs()),
            'promotion_user' => new ObjectId('678ab435ee1026a922940d5b'),
            'purchase_order' => new ObjectId('000000000000000000000000'),
            'code'           => (string) \Illuminate\Support\Str::uuid(),
            'expiration'     => new UTCDateTime(strtotime($request->expiration) * 1000),
            'package'        => new ObjectId($packageId),
            'price'          => (float) $request->price,
            'uses'           => (int) $request->uses,
            'type'           => 'BUSINESS',
            'status'         => null,
            'error'          => null,
        ]);

        $promo->save();

        return response()->json(['success' => true, 'code' => $promo->code]);
    }

    public function update(Request $request, $id){
        $packageId = array_search($request->package, self::PACKAGES);

        $fields = [
            'uses'  => (int) $request->uses,
            'price' => (float) $request->price,
        ];

        if ($request->expiration) {
            $fields['expiration'] = new UTCDateTime(strtotime($request->expiration) * 1000);
        }

        if ($packageId !== false) {
            $fields['package'] = new ObjectId($packageId);
        }

        Promocion::where('_id', $id)->update($fields);

        return response()->json(['success' => true]);
    }

    // ── BULK QR ──────────────────────────────────────────────────────────

    public function bulkTabla(Request $request)
    {
        // Mapa promotion_user → nombre desde MySQL
        $proyectos = ProyectoQr::pluck('nombre', 'promotion_user');

        $promos = PromocionBulk::get();

        $data = $promos->map(function ($promo) use ($proyectos) {
            $packageId   = (string) ($promo->package ?? '');
            $packageName = self::PACKAGES[$packageId] ?? ($packageId ?: '—');
            $puStr       = (string) ($promo->promotion_user ?? '');
            $expiration  = $promo->expiration
                ? (is_object($promo->expiration)
                    ? $promo->expiration->toDateTime()->format('Y-m-d')
                    : $promo->expiration)
                : '—';

            return [
                'id'             => (string) $promo->_id,
                'proyecto'       => $proyectos[$puStr] ?? '(sin proyecto)',
                'promotion_user' => $puStr,
                'code'           => $promo->code  ?? '—',
                'package'        => $packageName,
                'price'          => $promo->price ?? 0,
                'uses'           => $promo->uses  ?? 0,
                'expiration'     => $expiration,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:150',
            'package'    => 'required|string',
            'price'      => 'required|numeric|min:0',
            'uses'       => 'required|integer|min:1',
            'expiration' => 'required|date',
            'cantidad'   => 'required|integer|min:1|max:1000',
        ]);

        $packageId = array_search($request->package, self::PACKAGES);
        if ($packageId === false) {
            return response()->json(['success' => false, 'message' => 'Paquete inválido'], 422);
        }

        // Generar un ObjectId nuevo automáticamente para este proyecto
        $pu = new ObjectId();

        // Registrar el proyecto en MySQL con el ObjectId generado
        ProyectoQr::create([
            'nombre'         => trim($request->nombre),
            'promotion_user' => (string) $pu,
            'created_by'     => Auth::id(),
        ]);

        $exp = new UTCDateTime(strtotime($request->expiration) * 1000);
        $pkg = new ObjectId($packageId);
        $now = new UTCDateTime(now()->getTimestampMs());
        $total   = (int) $request->cantidad;
        $created = 0;
        $lote    = [];

        for ($i = 0; $i < $total; $i++) {
            $lote[] = [
                'IsSync'         => true,
                'lastSync'       => $now,
                'promotion_user' => $pu,
                'purchase_order' => new ObjectId('000000000000000000000000'),
                'code'           => (string) Str::uuid(),
                'expiration'     => $exp,
                'package'        => $pkg,
                'price'          => (float) $request->price,
                'uses'           => (int) $request->uses,
                'type'           => 'BUSINESS',
                'status'         => null,
                'error'          => null,
            ];

            if (count($lote) >= 100) {
                DB::connection('mongodb')->collection('specialorders')->insert($lote);
                $created += count($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            DB::connection('mongodb')->collection('specialorders')->insert($lote);
            $created += count($lote);
        }

        return response()->json(['success' => true, 'created' => $created]);
    }

    public function bulkProyectos()
    {
        // Conteos desde MongoDB agrupados por promotion_user
        $conteos = DB::connection('mongodb')
            ->collection('specialorders')
            ->raw(fn ($col) => $col->aggregate([
                ['$match' => ['promotion_user' => ['$ne' => new ObjectId('678ab435ee1026a922940d5b')]]],
                ['$group' => ['_id' => '$promotion_user', 'total' => ['$sum' => 1]]],
            ]));

        // Convertir a mapa promotion_user_string → total
        $totales = [];
        foreach ($conteos as $doc) {
            $key = (string) ($doc['_id'] ?? '');
            $totales[$key] = $doc['total'] ?? 0;
        }

        // Leer proyectos de MySQL y enriquecer con el conteo
        $proyectos = ProyectoQr::orderBy('nombre')->get()->map(fn ($p) => [
            'id'             => $p->id,
            'nombre'         => $p->nombre,
            'promotion_user' => $p->promotion_user,
            'total'          => $totales[$p->promotion_user] ?? 0,
        ]);

        return response()->json($proyectos);
    }

    public function bulkDownload(Request $request)
    {
        $proyectoId = $request->input('proyecto_id');
        if (!$proyectoId) {
            abort(400, 'Proyecto requerido');
        }

        $proyecto = ProyectoQr::findOrFail($proyectoId);
        $promos   = PromocionBulk::where('promotion_user', new ObjectId($proyecto->promotion_user))->get();

        if ($promos->isEmpty()) {
            abort(404, 'Sin QR para ese proyecto');
        }

        ini_set('memory_limit', '512M');
        set_time_limit(600);

        $writer = new PngWriter();
        $items  = $promos->map(function ($promo) use ($writer, $proyecto) {
            $packageId   = (string) ($promo->package ?? '');
            $packageName = self::PACKAGES[$packageId] ?? ($packageId ?: '—');

            $qr = new QrCode(
                data: $promo->code,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 350,   // alta resolución para impresión a 3.5 cm
                margin: 4,
            );
            $qrB64 = base64_encode($writer->write($qr)->getString());

            $exp = $promo->expiration
                ? (is_object($promo->expiration)
                    ? $promo->expiration->toDateTime()->format('d/m/Y')
                    : $promo->expiration)
                : '—';

            return [
                'code'       => $promo->code,
                'qr_b64'     => $qrB64,
                'package'    => $packageName,
                'price'      => $promo->price,
                'uses'       => $promo->uses,
                'expiration' => $exp,
                'proyecto'   => $proyecto->nombre,
            ];
        });

        $pdf = Pdf::loadView('promociones.bulk_pdf', [
            'items'    => $items,
            'proyecto' => $proyecto->nombre,
        ])->setPaper('letter', 'portrait');

        $filename = 'qr-' . Str::slug($proyecto->nombre) . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    public function bulkDeleteProyecto(Request $request)
    {
        $proyectoId = $request->input('proyecto_id');
        if (!$proyectoId) {
            return response()->json(['success' => false, 'message' => 'Proyecto requerido'], 400);
        }

        $proyecto = ProyectoQr::findOrFail($proyectoId);

        // Eliminar documentos en MongoDB por promotion_user
        $deleted = PromocionBulk::where('promotion_user', new ObjectId($proyecto->promotion_user))->delete();

        // Eliminar registro en MySQL
        $proyecto->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    // ── / BULK QR ────────────────────────────────────────────────────────

    public function pdf($id)
    {
        $promo = Promocion::findOrFail($id);

        $packageId   = (string) ($promo->package ?? '');
        $packageName = self::PACKAGES[$packageId] ?? $packageId;

        $expiration = $promo->expiration
            ? (is_object($promo->expiration)
                ? $promo->expiration->toDateTime()->format('Y-m-d')
                : $promo->expiration)
            : '—';

        $pdf = Pdf::loadView('promociones.pdf', [
            'code'        => $promo->code,
            'packageName' => $packageName,
            'price'       => $promo->price,
            'uses'        => $promo->uses,
            'expiration'  => $expiration,
            'type'        => $promo->type,
        ])->setPaper('a5', 'portrait');

        return $pdf->download('promocion-' . $promo->code . '.pdf');
    }
}
