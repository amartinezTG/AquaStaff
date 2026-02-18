<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffUserController;
use App\Http\Controllers\CajeroController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\FiscalAccountsController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\CajaChicaController;
use App\Http\Controllers\CompaqController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\TipoDeCambioController;
use App\Http\Controllers\IndicadoresController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\PromocionesController;

use App\Models\TipoDeCambio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;


// Artisan::call('view:clear');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "wsb" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('login');
});

#################
# DASHBOARD
Route::middleware('auth')->controller(DashboardController::class)->group(function(){
    Route::get('/dashboard',  'dashboard')->name('dashboard');
    Route::post('/dashboard/info_dashboard',  'info_dashboard')->name('dashboard.info_dashboard');

    Route::post('/dashboard/active_memberships',  'active_memberships')->name('dashboard.active_memberships');

});


#################
# Administracion
#################

// Route::middleware('auth')->controller(AdministracionController::class)->group(function(){
//     Route::get('/administracion/index',  'index')->name('administracion.index');


// });
/*
|--------------------------------------------------------------------------
| Rutas de Administración - Auditoría de Transacciones
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('administracion')->controller(AdministracionController::class)->group(function() {

    // Vista principal
    Route::get('/', 'index')->name('administracion.index');
    // Obtener resumen de transacciones faltantes por día
    Route::post('/transaction-gaps-summary', 'getTransactionGapsSummary')->name('administracion.gaps.summary');
    // Obtener detalle de huecos específicos
    Route::post('/transaction-gaps-detail', 'getTransactionGapsDetail')->name('administracion.gaps.detail');
    // Exportar reporte a CSV
    Route::get('/export-transaction-gaps', 'exportTransactionGaps')->name('administracion.gaps.export');
    // Estadísticas rápidas (últimos 7 días)
    Route::get('/quick-stats', 'getQuickStats')->name('administracion.quick.stats');

});


#################
# /INDICADORES MENSUALES
#################
Route::middleware('auth')->controller(IndicadoresController::class)->group(function () {
   
    Route::get('indicadores',  'indicadores2')->name('indicadores2');
    Route::post('/indicadores/indicadores_table',  'indicadores_table')->name('indicadores.indicadores_table');
    Route::get('indicadores_cajero', 'indicadores_cajero')->name('indicadores_cajero');
    Route::post('indicadores/indicadores_pagos_table',  'indicadores_pagos_table')->name('indicadores.indicadores_pagos_table');
    Route::post('indicadores/indicadores_membresias_table',  'indicadores_membresias_table')->name('indicadores.indicadores_membresias_table');
    Route::match(['get', 'post'], 'indicadores-membresias',  'membresias')->name('indicadores-membresias');
    Route::match(['get', 'post'], 'indicadores_membresias',  'indicadores_membresias')->name('indicadores_membresias');
});

#################
# CAJERO
Route::get('cajero', [CajeroController::class, 'index'])->middleware('auth');
Route::post('cajero', [CajeroController::class, 'index'])->middleware('auth')->name('cajero');
Route::get('cajero_transacciones', [CajeroController::class, 'cajero_transacciones'])->middleware('auth')->name('cajero_transacciones');
Route::post('cajero/CajerosTable', [CajeroController::class, 'CajerosTable'])->middleware('auth')->name('cajero.CajerosTable');
Route::get('cajero/membership-packages', [CajeroController::class, 'membershipPackages'])->middleware('auth')->name('cajero.membership.packages');

Route::get('/exportar-csv/{startDate}/{endDate}', [CajeroController::class, 'exportCsv'])->middleware('auth')->name('exportar-csv');
Route::get('/exportar-trafico-ventas/{startDate}/{endDate}', [CajeroController::class, 'exportSalesTraffic'])->middleware('auth')->name('exportar-trafico-ventas');
Route::get('/exportar-listado-transacciones/{startDate}/{endDate}', [CajeroController::class, 'exportTransactionsList'])->middleware('auth')->name('exportar-trafico-ventas');


#################
# MEMBRESIAS
Route::get('/membresias', [MembershipController::class, 'index'])->middleware('auth')->name('membresias');
Route::post('/membresias', [MembershipController::class, 'index'])->middleware('auth')->name('membresias');
Route::get('/exportar-membresias-ventas/{startDate}/{endDate}', [MembershipController::class, 'exportMembershipTraffic'])->middleware('auth')->name('exportar-membresias-ventas');
// Rutas para membresías en cajero
Route::get('/membresias/cajero', [MembershipController::class, 'membresia_cajero'])->name('membresias.cajero');
Route::post('/membresias/membresias_cajero_table', [MembershipController::class, 'membresias_cajero_table'])->name('membresias.cajero.table');





#################
# COMPAQ
Route::get('/compaq', [CompaqController::class, 'index'])->middleware('auth');
Route::post('/compaq', [CompaqController::class, 'index'])->middleware('auth')->name('compaq');
Route::post('/process_compaq', [CompaqController::class, 'process_compaq'])->middleware('auth')->name('process_compaq');
Route::get('/download/txt/{name}', [CompaqController::class, 'downloadTXT'])->name('download.txt');
Route::get('/compaq_detalle/{id}', [CompaqController::class, 'DetailCompaq'])->middleware('auth');
Route::get('/compaq_archivo/{name}', [CompaqController::class, 'CompaqFile'])->middleware('auth');
Route::get('/compaq/history', [CompaqController::class, 'history'])->name('compaq.history');
#################
# / COMPAQ


#################
# GLOBAL INVOICE
Route::post('/process_global_invoice', [CompaqController::class, 'process_global_invoice'])->name('process_global_invoice')->middleware('auth');
Route::get('/global_invoice_download/xml/{name}', [CompaqController::class, 'downloadInvoiceXML'])->name('global_invoice_download.xml');
Route::get('/global_invoice_download/pdf/{name}', [CompaqController::class, 'downloadInvoicePDF'])->name('global_invoice_download.pdf');
Route::get('/global_invoice_detalle/{id}', [CompaqController::class, 'DetailGlobalInvoice'])->middleware('auth');

#################
# / GLOBAL INVOICE


#################
# CORTE CAJA
Route::get('/corte_caja', [CorteCajaController::class, 'index'])->middleware('auth')->name('corte_caja');
Route::get('/detalle_corte/{corte_id}', [CorteCajaController::class, 'DetalleCorte'])->name('detalle_corte');
Route::post('/corte_caja_sucursal', [CorteCajaController::class, 'SubmitCorte'])->name('corte_caja_sucursal');
Route::post('/validar-fecha-corte', [CorteCajaController::class, 'validarFechaCorte'])->name('validar.fecha.corte');
Route::get('/corte_caja_sucursal', function () {
    $activePage = 'corte_caja';
    $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 19.5;
    return view('corte_caja.corte_caja_sucursal', compact('activePage','tipoCambio'));
})->middleware('auth');

Route::get('/editar_corte/{corte_id}', [CorteCajaController::class, 'edit'])->name('editar_corte');
Route::put('/actualizar_corte/{corte_id}', [CorteCajaController::class, 'update'])->name('actualizar_corte');
Route::get('/exportar_corte/{corte_id}', [CorteCajaController::class, 'exportCorteToExcel'])->name('exportar_corte_excel');


Route::get('/detalle_corte_export/{corte_id}', [CorteCajaController::class, 'exportExcel'])->name('detalle_corte_export');
Route::get('/exportar-corte-pdf/{corte_id}', [CorteCajaController::class, 'exportPDF'])->name('exportar_corte_pdf');
Route::get('/detalle_corte_pdf/{corte_id}', [CorteCajaController::class, 'generarPDF'])->name('detalle_corte_pdf');

#################
# / CORTE CAJA

#################
# CAJA CHICA
Route::get('/caja_chica', [CajaChicaController::class, 'index'])->middleware('auth');
Route::post('/caja_chica_sucursal', [CajaChicaController::class, 'SubmitCorte'])->name('caja_chica_sucursal');
Route::get('/caja_chica_sucursal', function () {
    $activePage = 'caja_chica';
    return view('caja_chica_sucursal', compact('activePage'));
})->middleware('auth');
Route::get('/detalle_caja_chica/{caja_id}', [CajaChicaController::class, 'DetalleCorte'])->name('detalle_corte');
#################
# / CAJA CHICA


#################
# PRODUCTS
Route::get('/inventarios', function () {
    $activePage = 'inventarios';
    return view('inventarios', compact('activePage'));
})->middleware('auth');

Route::get('/productos', [ProductosController::class, 'index'])->middleware('auth');

Route::get('/agregar_producto', [ProductosController::class, 'ProductAdd'])->middleware('auth');
Route::get('/editar_producto/{product_id}', [ProductosController::class, 'ProductAdd'])->middleware('auth');
Route::get('/eliminar_producto/{product_id}', [ProductosController::class, 'eliminar_producto'])->middleware('auth');

Route::post('/producto_agregar', [ProductosController::class, 'SubmitProduct'])->name('producto_agregar')->middleware('auth');
#################
# / PRODUCTS

#################
# TRANSFERENCIAS
Route::get('/transferencias', [ProductosController::class, 'transferencias'])->middleware('auth');
Route::get('/crear_transferencia', [ProductosController::class, 'CreateTransfer'])->middleware('auth');
Route::post('/submit_transfer_form', [ProductosController::class, 'SubmitTransfer'])->name('submit_transfer_form')->middleware('auth');
Route::get('/transfer_detail/{transfer_id}', [ProductosController::class, 'TransferDetail'])->middleware('auth');
Route::post('/submit_transfer_logs', [ProductosController::class, 'SubmitTransferLogs'])->name('submit_transfer_logs')->middleware('auth');

Route::post('/submit_transfer_update', [ProductosController::class, 'SubmitTransferUpdate'])->name('submit_transfer_update')->middleware('auth');

Route::get('/search/products', [ProductosController::class, 'searchProducts'])->name('search.products');
#################
# / TRANSFERENCIAS 

#################
# USERS
Route::get('/usuarios', [StaffUserController::class, 'index'])->middleware('auth');
Route::get('/login', [StaffUserController::class, 'showLoginForm'])->name('login');
//Route::post('/', 'UserController@login');
Route::post('/login', [StaffUserController::class, 'login'])->name('login');
Route::get('/usuario', [StaffUserController::class, 'user_form'])->name('usuario')->middleware('auth');
Route::post('/usuario', [StaffUserController::class, 'user_submit_form'])->name('crear_usuario');
Route::get('/editar_usuario/{usuario_id}', [StaffUserController::class, 'user_form'])->middleware('auth');
Route::get('/eliminar_usuario/{user_id}', [StaffUserController::class, 'delete_user'])->middleware('auth');
Route::get('/logout', [StaffUserController::class, 'logout'])->name('logout');

#################
# /USERS
#################
# Tipo de Cambio
Route::get('/tipo_de_cambio', [TipoDeCambioController::class, 'index'])->name('tipo_de_cambio.index');
Route::post('/tipo_de_cambio/actualizar', [TipoDeCambioController::class, 'update'])->name('tipo_de_cambio.update');


// Route::post('indicadores', [IndicadoresController::class, 'indicadores'])
//      ->name('indicadores');

Route::get('/exportar-indicadores/', [IndicadoresController::class, 'exportarIndicadores'])->middleware('auth')->name('exportar-indicadores');




Route::get('/exportar-membresias', [IndicadoresController::class, 'exportMembresias'])->middleware('auth')->name('exportar-membresias');

Route::get('/indicadores_operativos_pdf/', [IndicadoresController::class, 'generarOperativosPDF'])->name('indicadores_operativos_pdf');


Route::get('/exportar_membresias_pdf/', [IndicadoresController::class, 'generarMembresiasPDF'])->name('exportar_membresias_pdf');


#################
# PROMOCIONES
Route::middleware('auth')->controller(PromocionesController::class)->group(function () {
    Route::get('/promociones', 'index')->name('promociones.index');
    Route::post('/promociones/tabla', 'tabla')->name('promociones.tabla');
});
#################
# / PROMOCIONES

Route::get('/vending', function () {
    $activePage = 'vending';
    return view('vending', compact('activePage'));
})->middleware('auth');





/*Route::get('/facturacion', function () {
    $activePage = 'facturacion';
    return view('facturacion', compact('activePage'));
})->middleware('auth');*/

Route::get('/facturacion', [FacturacionController::class, 'index'])->middleware('auth');



// Route::get('/weather/store', [WeatherController::class, 'store']);