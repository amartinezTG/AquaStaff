<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

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
        $promociones = Promocion::all();//promoocioness

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
