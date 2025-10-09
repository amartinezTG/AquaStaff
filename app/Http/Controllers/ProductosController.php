<?php

namespace App\Http\Controllers;

use App\Models\GeneralCatalogs;
use App\Models\LocalTransaction;
use App\Models\Products;
use App\Models\Transfers;
use App\Models\ProductTransfers;
use App\Models\TransfersLogs;
use App\Models\FacilityInventory;
use App\Models\InventoryLog;
use App\Models\Facilities;
use Carbon\Carbon;
use App\Rules\UniqueSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductosController extends Controller
{
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    public function index(Request $request){

        $catalogs = new GeneralCatalogs();
        $activePage = 'productos';
        $productos = Products::where('active', 1)
            ->orderBy('product_id', 'desc')
            ->get();
        $facilityInventories = FacilityInventory::all();
        $facilities = Facilities::all();

        return view('productos', compact('activePage', 'catalogs', 'productos', 'facilityInventories', 'facilities'));
    }

    public function ProductAdd(Request $request)
    {
        $activePage = 'producto_agregar';
        $catalogs = new GeneralCatalogs();
        $facilities = Facilities::all();

        if ($request->product_id) {
            $productos = Products::where('product_id', $request->product_id)
                ->orderBy('product_id', 'desc')
                ->first();
            $facilityInventories = FacilityInventory::where('product_id',$request->product_id)->get();
        } else {
            $productos = null;
            $facilityInventories = null;
        }

        return view('producto_agregar', compact('activePage', 'catalogs', 'productos','facilities', 'facilityInventories'));
    }

    public function SubmitProduct(Request $request)
{
    $user = Auth::user();

    if ($request->product_id) {
        $validatedData = $request->validate([
            'name' => 'required',
            'inventory' => 'required|numeric',
            'unit_measurement' => 'required'
        ]);
    } else {
        $validatedData = $request->validate([
            'name' => 'required',
            'sku' => ['required', new UniqueSku],
            'inventory' => 'required|numeric',
            'unit_measurement' => 'required'
        ]);
    }

    if ($request->product_id) {
        $Products = Products::where('product_id', $request->product_id)
            ->orderBy('product_id', 'desc')
            ->first();
    } else {
        $Products = new Products;
    }

    $Products->name = $request->name;
    $Products->sku = $request->sku;
    $Products->inventory = $request->inventory;
    $Products->reorder = $request->reorder;
    $Products->active = 1;
    $Products->unit_measurement = $request->unit_measurement;

    if (!$Products->save()) {
        return redirect('productos')->with('error', 'Error')->withInput();
    } else {
        if ($request->product_id) {
            // Edición de un producto existente
            foreach ($request->reorder_facility as $facilityId => $reorderQty) {
                // Verificar si el registro existe
                $facilityInventory = FacilityInventory::where('product_id', $request->product_id)
                    ->where('facility_id', $facilityId)
                    ->first();

                if ($facilityInventory) {
                    // Actualizar el registro existente
                    $facilityInventory->reorder = $reorderQty;
                    $facilityInventory->save();
                } else {
                    // Crear un nuevo registro si no existe
                    $newFacilityInventory = new FacilityInventory();
                    $newFacilityInventory->product_id = $request->product_id;
                    $newFacilityInventory->facility_id = $facilityId;
                    $newFacilityInventory->reorder = $reorderQty;
                    $newFacilityInventory->qty = 0; // O el valor que necesites
                    $newFacilityInventory->save();
                }
            }

            // Actualizar el inventario por sucursal
            if ($request->inventory_facility) {
                foreach ($request->inventory_facility as $facilityId => $inventoryQty) {
                    $facilityInventory = FacilityInventory::where('product_id', $request->product_id)
                        ->where('facility_id', $facilityId)
                        ->first();

                    if ($facilityInventory) {
                        $facilityInventory->qty = $inventoryQty;
                        $facilityInventory->save();
                    } else {
                        $newFacilityInventory = new FacilityInventory();
                        $newFacilityInventory->product_id = $request->product_id;
                        $newFacilityInventory->facility_id = $facilityId;
                        $newFacilityInventory->qty = $inventoryQty;
                        $newFacilityInventory->save();
                    }
                }
            }
        } else {
            // Creación de un nuevo producto
            $newProductId = $Products->product_id;

            foreach ($request->reorder_facility as $facilityId => $reorderQty) {
                $facilityInventory = new FacilityInventory();
                $facilityInventory->product_id = $newProductId;
                $facilityInventory->facility_id = $facilityId;
                $facilityInventory->reorder = $reorderQty;
                $facilityInventory->qty = 0; // O el valor que necesites
                $facilityInventory->save();
            }

            // Crear el inventario por sucursal
            if ($request->inventory_facility) {
                foreach ($request->inventory_facility as $facilityId => $inventoryQty) {
                    $facilityInventory = new FacilityInventory();
                    $facilityInventory->product_id = $newProductId;
                    $facilityInventory->facility_id = $facilityId;
                    $facilityInventory->qty = $inventoryQty;
                    $facilityInventory->save();
                }
            }
        }

        return redirect('productos')->with('success', 'Producto Agregado')->withInput();
    }
}

    public function eliminar_producto( $product_id){ 
        $catalogs        = new GeneralCatalogs();
        $activePage      = 'productos';
        $product         = Products::where('product_id', $product_id)->first();
        $product->active = null;

        $product->save();

        $productos       = Products::where('active', 1)
                            ->orderBy('product_id', 'desc')
                            ->get();
        $facilities          = Facilities::orderBy('facility_id', 'desc')->get();
        $facilityInventories = FacilityInventory::with(['facility', 'product'])->get();

        return view('productos', compact('activePage', 'catalogs', 'productos', 'facilities', 'facilityInventories'));
    }

    

    #################
    # TRANSFERENCIAS
    public function transferencias(Request $request){
        $catalogs          = new GeneralCatalogs();
        $activePage        = 'transferencias';
        $transferencias = Transfers::with(['facilityDeparture', 'facilityArrive'])
        ->orderBy('transfer_id', 'desc')
        ->get();
        $products          = Products::with('facilityInventories')->get();
        $inventoryLogs     = InventoryLog::with(['product', 'user'])->get();
        $facilityInventory = FacilityInventory::with('product')->get();
        $balances          = FacilityInventory::with('product')->get(); // Agregar esta línea
        $facilities        = Facilities::orderBy('facility_id', 'desc')->get();
        $facilityInventories = FacilityInventory::with(['facility', 'product'])->get();

        return view('transferencias', compact('activePage', 'catalogs', 'transferencias', 'products', 'inventoryLogs', 'facilityInventory', 'balances', 'facilities', 'facilityInventories')); // Agregar 'balances' al compact

    }

    public function CreateTransfer(Request $request){
        $catalogs    = new GeneralCatalogs();
        $activePage  = 'transferencias';
        
        $productos   = Products::where('active', 1)
                            ->orderBy('product_id', 'desc')
                            ->get();
        $facilities = Facilities::orderBy('facility_id', 'desc')->get();


        return view('submit_transfer', compact('activePage', 'catalogs', 'productos', 'facilities'));
    }

    public function searchProducts(Request $request){ 
        $searchTerm = $request->search;

        $products   = Products::where('name', 'like', '%' . $searchTerm . '%')
                           ->where('active', 1)
                           ->get();

        return response()->json(['products' => $products]);
    }
    
    public function SubmitTransfer(Request $request)
    {
        $user = Auth::user();
        $validatedData = $request->validate([
            'name' => 'required',
            'facility_departure' => 'required',
            'facility_arrive' => 'required',
            'status' => 'required',
        ]);

        $Transfers = new Transfers;
        $Transfers->name = $request->name;
        $Transfers->facility_departure = $request->facility_departure;
        $Transfers->facility_arrive = $request->facility_arrive;
        $Transfers->status = $request->status;
        $Transfers->invoice = $request->invoice;
        $Transfers->comments = $request->comments;

        if (!$Transfers->save()) {
            return redirect('crear_transferencia')->with('error', 'Error')->withInput();
        } else {
            // Actualizar transfer_id en inventory_logs después de crear la transferencia
            InventoryLog::whereNull('transfer_id')
                ->where('movement_type', 'Transferencia (Salida)')
                ->orWhere('movement_type', 'Transferencia (Entrada)')
                ->update(['transfer_id' => $Transfers->transfer_id]);

            foreach ($request->product_qty as $product_id => $qty) {
                // Verificar si $qty es nulo o vacío
                if ($qty === null || $qty === '') {
                    continue; // Saltar a la siguiente iteración si $qty es nulo o vacío
                }

                // Verificar si $qty es numérico
                if (!is_numeric($qty)) {
                    // Manejar el error si $qty no es un número
                    return redirect('crear_transferencia')->with('error', 'Cantidad inválida para el producto ' . $product_id)->withInput();
                }

                $product = Products::find($product_id);

                // Captura del historial antes de la actualización en Products (CEDIS)
                $inventoryLogCedis = new InventoryLog();
                $inventoryLogCedis->facility_id = 2; // ID de CEDIS
                $inventoryLogCedis->product_id = $product_id;
                $inventoryLogCedis->qty_before = $product->inventory;
                $inventoryLogCedis->qty_after = $product->inventory - $qty;
                $inventoryLogCedis->movement_type = 'Transferencia (Salida)';
                $inventoryLogCedis->user_id = $user->id;
                $inventoryLogCedis->transfer_id = $Transfers->transfer_id; // Asignar el transfer_id
                $inventoryLogCedis->save();

                // Actualizar el inventario en la tabla products (CEDIS)
                $product->inventory -= $qty;
                $product->save();

                // Actualizar el facility inventory (MISIONES)
                $facilityInventoryArrive = FacilityInventory::where('product_id', $product_id)
                ->where('facility_id', $request->facility_arrive) // Usa $request->facility_arrive
                ->first();

                $qtyBeforeFacility = $facilityInventoryArrive ? $facilityInventoryArrive->qty : 0;

                if ($facilityInventoryArrive) {
                    $facilityInventoryArrive->qty += $qty;
                    $facilityInventoryArrive->save();
                } else {
                    $newFacilityInventory = new FacilityInventory();
                    $newFacilityInventory->product_id = $product_id;
                    $newFacilityInventory->facility_id = $request->facility_arrive; // Usa $request->facility_arrive
                    $newFacilityInventory->qty = $qty;
                    $newFacilityInventory->save();
                }

                // Captura del historial después de la actualización en FacilityInventory (MISIONES)
                $inventoryLogMisiones = new InventoryLog();
                $inventoryLogMisiones->facility_id = $request->facility_arrive; // ID de MISIONES
                $inventoryLogMisiones->product_id = $product_id;
                $inventoryLogMisiones->qty_before = $qtyBeforeFacility;
                $inventoryLogMisiones->qty_after = $qtyBeforeFacility + $qty;
                $inventoryLogMisiones->movement_type = 'Transferencia (Entrada)';
                $inventoryLogMisiones->user_id = $user->id;
                $inventoryLogMisiones->transfer_id = $Transfers->transfer_id; // Asignar el transfer_id
                $inventoryLogMisiones->save();

                // Crear el registro en product_transfers
                ProductTransfers::create([
                    'transfer_id' => $Transfers->transfer_id,
                    'product_id' => $product_id,
                    'qty' => $qty,
                ]);

                // Captura del historial antes de la actualización en FacilityInventory (Salida)
                $facilityInventoryDeparture = FacilityInventory::where('facility_id', $request->facility_departure)
                    ->where('product_id', $product_id)
                    ->first();

                $qtyBeforeDeparture = $facilityInventoryDeparture ? $facilityInventoryDeparture->qty : 0;

                if ($facilityInventoryDeparture) {
                    $facilityInventoryDeparture->qty -= $qty;
                    $facilityInventoryDeparture->save();
                } else {
                    $newInventoryDeparture = new FacilityInventory();
                    $newInventoryDeparture->facility_id = $request->facility_departure;
                    $newInventoryDeparture->product_id = $product_id;
                    $newInventoryDeparture->qty = -$qty; // O 0, dependiendo de tu lógica
                    $newInventoryDeparture->save();
                }

                // Captura del historial después de la actualización en FacilityInventory (Salida)
                $inventoryLogDeparture = new InventoryLog();
                $inventoryLogDeparture->facility_id = $request->facility_departure;
                $inventoryLogDeparture->product_id = $product_id;
                $inventoryLogDeparture->qty_before = $qtyBeforeDeparture;
                $inventoryLogDeparture->qty_after = $qtyBeforeDeparture - $qty;
                $inventoryLogDeparture->movement_type = 'Transferencia (Salida)';
                $inventoryLogDeparture->user_id = $user->id;
                $inventoryLogDeparture->transfer_id = $Transfers->transfer_id;
                $inventoryLogDeparture->save();
            }

            return redirect('transferencias')->with('success', 'Transferencia Creada')->withInput();
        }
    }

    public function TransferDetail($transfer_id){
      
        $catalogs   = new GeneralCatalogs();
        $activePage = 'transferencias';
        $Transfers  = Transfers::where('transfer_id', $transfer_id)
                        ->orderBy('transfer_id', 'desc')
                        ->first();

        /*$ProductTransfers = ProductTransfers::where('transfer_id', $transfer_id)
                        ->orderBy('product_id', 'desc')
                        ->get();*/

        $transferDetails = ProductTransfers::where('transfer_id', $transfer_id)
                            ->with('product') // Eager load the 'product' relationship
                            ->get();

        $TransfersLogs  = TransfersLogs::where('transfer_id', $transfer_id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('transferencia_detalle', compact('activePage', 'catalogs', 'Transfers','transferDetails', 'TransfersLogs'));

    }

    public function SubmitTransferLogs(Request $request){
        $user                       = Auth::user();
        

        $validatedData = $request->validate([
            'transfer_id' => 'required',
            'comments'    => 'required'
            
        ], [
            'transfer_id.required' => 'Campo requerido',
            'comments.required'    => 'Campo requerido'
        ]);


        $catalogs                   = new GeneralCatalogs();
        $activePage                 = 'transferencias';
        $TransfersLogs              = new TransfersLogs;
        $TransfersLogs->transfer_id = $request->transfer_id;
        $TransfersLogs->comments    = $request->comments;
        $TransfersLogs->user_id     = $user->id;

        if(!$TransfersLogs->save()){
            return redirect('transfer_detail/'.$request->transfer_id)->with('error', 'Error')->withInput();
        }else{
            return redirect('transfer_detail/'.$request->transfer_id)->with('success', 'Transferencia Actualziada')->withInput();
        }
    }

    public function SubmitTransferUpdate(Request $request){
       
        $user = Auth::user();
        $catalogs = new GeneralCatalogs();
        $activePage = 'transferencias';

        $Transfers = Transfers::where('transfer_id', $request->transfer_id)
            ->orderBy('transfer_id', 'desc')
            ->first();
        $Transfers->status = $request->status;
        if (!$Transfers->save()) {
          
            return redirect('transfer_detail/' . $request->transfer_id)->with('error', 'Error')->withInput();
        } else {
            if ($request->status == 4) {
                $transferDetails = ProductTransfers::where('transfer_id', $request->transfer_id)
                    ->with('product')
                    ->get();

                foreach ($transferDetails as $productTransfer) {
                    $facilityInventoryArrive = FacilityInventory::where('facility_id', $request->facility_arrive)
                        ->where('product_id', $productTransfer->product_id)
                        ->first();

                    $qtyBeforeArrive = $facilityInventoryArrive ? $facilityInventoryArrive->qty : 0;

                    if ($facilityInventoryArrive) {
                        $facilityInventoryArrive->qty += $productTransfer->qty;
                        $facilityInventoryArrive->save();
                    } else {
                        //dd($request->facility_arrive);
                        $newInventoryArrive = new FacilityInventory();
                        $newInventoryArrive->facility_id = $request->facility_arrive;
                        $newInventoryArrive->product_id = $productTransfer->product_id;
                        $newInventoryArrive->qty = $productTransfer->qty;
                        $newInventoryArrive->save();
                    }

                    // Captura del historial después de la actualización en FacilityInventory (Entrada)
                    $inventoryLogArrive = new InventoryLog();
                    $inventoryLogArrive->facility_id = $request->facility_arrive;
                    $inventoryLogArrive->product_id = $productTransfer->product_id;
                    $inventoryLogArrive->qty_before = $qtyBeforeArrive;
                    $inventoryLogArrive->qty_after = $qtyBeforeArrive + $productTransfer->qty;
                    $inventoryLogArrive->movement_type = 'Transferencia (Entrada)';
                    $inventoryLogArrive->user_id = $user->id;
                    $inventoryLogArrive->transfer_id = $request->transfer_id;
                    $inventoryLogArrive->save();
                }
            }

            return redirect('transfer_detail/' . $request->transfer_id)->with('success', 'Status Actualizado')->withInput();
        }
    }
    

}


