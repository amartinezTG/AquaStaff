<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionesController extends Controller
{
    public function index()
    {
        $activePage = 'promociones';
        return view('promociones.index', compact('activePage'));
    }

    public function tabla(Request $request)
    {
        $promociones = Promocion::all();///tosdas

        $data = $promociones->map(function ($promo) {
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
                'package'        => (string) ($promo->package ?? '—'),
                'error'          => $promo->error ?? '—',
            ];
        });

        return response()->json(['data' => $data]);
    }
}
