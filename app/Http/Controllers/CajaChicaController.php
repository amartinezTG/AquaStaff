<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocalTransaction;
use App\Models\GeneralCatalogs;
use App\Models\Client;
use App\Models\CorteCaja;
use App\Models\DetalleArqueo;
use App\Models\CorteCajaComentarios;
use App\Models\FondoDeCaja;
use Illuminate\Support\Facades\Auth;


class CajaChicaController extends Controller
{
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    public function index(Request $request)
    {
        $activePage   = 'corte_caja_sucursal';
        $cortesDeCaja = CorteCaja::orderBy('id', 'desc')->get();

        $FondoDeCaja = FondoDeCaja::orderBy('id', 'desc')->get();

        return view('caja_chica', compact('activePage', 'cortesDeCaja', 'FondoDeCaja'));
    }

    public function SubmitCorte(Request $request)
    {
        $user          = Auth::user();

        $request->validate([
            'fecha_corte'              => 'required',
            'fondo_de_cada_efectivo'   => 'required'
        ], [
            'fecha_corte.required'              => 'Campo requerido',
            'sucursal.required'                 => 'Campo requerido',
        ]);

        $FondoDeCaja = new FondoDeCaja;
        $FondoDeCaja->fecha_corte = $request->fecha_corte;
        $FondoDeCaja->efectivo    = $request->fondo_de_cada_efectivo;
        $FondoDeCaja->sucursal    = $request->sucursal;
        $FondoDeCaja->motivo_1    = $request->concepto_de_egresos_motivo_1;
        $FondoDeCaja->monto_1     = $request->concepto_de_egresos_monto_1;

        $FondoDeCaja->motivo_2    = $request->concepto_de_egresos_motivo_2;
        $FondoDeCaja->monto_2     = $request->concepto_de_egresos_monto_2;

        $FondoDeCaja->motivo_3    = $request->concepto_de_egresos_motivo_3;
        $FondoDeCaja->monto_3     = $request->concepto_de_egresos_monto_3;

        if(!$FondoDeCaja->save()){
            return redirect('caja_chica_sucursal')->with('error', 'Error')->withInput();
        }else{
            return redirect('caja_chica')->with('success', 'Fondo de Caja Agregado')->withInput();
        }

    }

    public function DetalleCorte($caja_id)
    {
        $cortecaja      = FondoDeCaja::where('id', $caja_id)->first();
        $activePage = 'caja_chica';
        return view('detalle_caja_chica', compact('activePage', 'cortecaja', 'caja_id'));

    }

}
