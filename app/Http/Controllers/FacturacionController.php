<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\FiscalAccounts;
use App\Models\User;
use Illuminate\Http\Request;

class FacturacionController extends Controller
{
    public function index()
    {
        $activePage = 'facturacion';
        $facturas = Factura::with('fiscalAccount')
        ->where('fiscal_invoice', '!=', null)
        ->orderBy('updated_at', 'desc')
        ->get();


        return view('facturacion', compact('activePage', 'facturas'));
    }

}


