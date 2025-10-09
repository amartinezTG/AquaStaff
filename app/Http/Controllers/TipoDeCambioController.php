<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralCatalogs;
use App\Models\Client;
use App\Models\TipoDeCambio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Importa la clase Session


class TipoDeCambioController extends Controller
{
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    /**
     * Display the form to update the exchange rate.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $activePage   = 'tipo_de_cambio';
        $tipoCambio = TipoDeCambio::first(); // Assuming you only have one record

        if (!$tipoCambio) {
            //If there is no record, create a new one.
            $tipoCambio = new TipoDeCambio();
            $tipoCambio->tipo_cambio = 0; // Or set a default value
            $tipoCambio->save();
        }
        return view('tipo_de_cambio.index', compact('tipoCambio', 'activePage')); // Corrected view path
    }

    /**
     * Update the exchange rate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'tipo_cambio' => 'required|numeric|min:0',
        ]);

        $tipoCambio = TipoDeCambio::first(); // Assuming you only have one record
        if (!$tipoCambio) {
            $tipoCambio = new TipoDeCambio();
        }
        $tipoCambio->tipo_cambio = $request->input('tipo_cambio');
        $tipoCambio->save();

        Session::flash('success', 'Tipo de cambio actualizado correctamente.'); // Use Session

        return redirect()->route('tipo_de_cambio.index'); // Corrected route name
    }

}

