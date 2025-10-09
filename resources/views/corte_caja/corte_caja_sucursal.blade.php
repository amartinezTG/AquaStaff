@include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
   
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

@include('layout.nav-header')

  </header><!-- End Header -->

  <?php //include('assets/includes/nav-bar.inc.php');?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Corte de Caja</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active"><a href="corte_caja">Corte de Caja</a></li>
          <li class="breadcrumb-item active">Agregar Corte de Caja</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      

      <form class="row g-3" method="POST" action="{{ route('corte_caja_sucursal') }}" name="corte_caja_sucursal">                
        @csrf
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
           
            <div class="card-body">
              <h5 class="card-title">Corte de Caja</h5>
              @if(session('success'))
                        <div class="alert alert-success">
                          {{ session('success') }}
                        </div>
                      @endif
              
                <div class="row">

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Fecha **</label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <input type="date" class="form-control" tabindex="1" name="fecha_corte" id="fecha_corte" value="" max="<?php echo date('Y-m-d'); ?>" required>
                    
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Sucursal **</label>
                
                  @error('sucursal')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <select class="form-select" name="sucursal" id="sucursal" tabindex="2" required disabled>
                      
                      <option value="MISIONES">MISIONES</option>
                    </select>
                  </div>
                </div>

                               

                

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Dinero Acumulado Efectivo (Cajeros)</label>
                
                  @error('dinero_acumulado_efectivo')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <input type="text" class="form-control" tabindex="5" name="dinero_acumulado_efectivo" id="dinero_acumulado_efectivo" value="" disabled required>
                    
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Dinero Acumulado Tarjeta (Cajeros)</label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <input type="text" class="form-control" tabindex="6" name="dinero_acumulado_tarjeta" id="dinero_acumulado_tarjeta" value="" disabled required>
                    
                  </div>
                </div>

                

                <div class="col-6">
                  <label for="tipo_cambio" class="form-label fw-bold">Total efectivo MXN </label>
                  <input type="text" name="total_efectivo_en_mxn" id="total_efectivo_en_mxn" class="form-control" readonly>
                </div>

                <div class="col-6">
                  <label for="tipo_cambio" class="form-label fw-bold">Total efectivo USD </label>
                  <input type="text" name="total_efectivo_en_usd" id="total_efectivo_en_usd" class="form-control" readonly>
                </div>


               <div class="col-6">
                  <label for="email" class="form-label fw-bold">Denominaciones de dinero recibido </label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <input type="text" class="form-control" tabindex="7" name="dinero_recibido" value="" id="dinero_recibido" disabled required>
                    
                  </div>
                </div>



                <div class="col-6">
                  <label for="tipo_cambio" class="form-label fw-bold">Tipo de cambio </label>
                  <input type="text" name="tipo_cambio" id="tipo_cambio" value="{{ $tipoCambio ?? 17.0 }}" class="form-control" readonly>

                </div>

                 

                

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Total Ventas **</label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <input type="text" class="form-control" tabindex="3" name="total_ventas" id="total_ventas" value="" disabled required>
                    
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Total Vehículos **</label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <input type="text" class="form-control" tabindex="4" name="total_tickets" id="total_tickets" value="" disabled required>
                    
                  </div>
                </div>


                



              </div>

            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
           
            <div class="card-body" id="ventas_totales_en_tarjetas">
              <h5 class="card-title">Ventas Totales en Tarjetas</h5>
              
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">TIPO TARJETA</label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">CANTIDAD</label>       
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">IMPORTE</label>                  
                </div>

                <div class="row mb-4">            
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Debito</label>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_pago_debito"></label>
                  </div>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_pago_debito"></label>
                  </div>
                </div>

                <div class="row mb-4">            
                  <label for="inputText" class="col-sm-6 col-form-label fw-bold">Crédito</label>                 
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_pago_credito"></label>
                  </div>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_pago_credito"></label>
                  </div>                  
                </div>

                <div class="row mb-4">            
                  <label for="inputText" class="col-sm-6 col-form-label fw-bold">AMEX</label>                 
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_pago_amex"></label>
                  </div>  
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_pago_amex"></label>
                  </div>                  
                </div>

                <div class="row mb-4">
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Total Tarjetas:</label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold" id="cantidad_tarjetas"></label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold" id="total_tarjetas">$</label>
                </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6" id="desgloce_ventas_paquetes">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Desgloce Ventas Paquetes</h5>
              <div class="row">
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-4 col-form-label"></label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cantidad</label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Importe</label>
                </div>
                <div class="row mb-3">
                  @error('paquete_basico')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Paquete Básico</label>
                  <div class="col-sm-4">                    
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_paquete_basico"></label>
                  </div>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_paquete_basico"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_basico_s')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Express</label>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_paquete_basico_s"></label>
                  </div>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_paquete_basico_s"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_ultra')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Paquete Ultra</label>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_paquete_ultra"></label>
                  </div>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_paquete_ultra"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_deluxe')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Paquete Deluxe</label>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="qty_paquete_deluxe"></label>
                  </div>
                  <div class="col-sm-4">
                    <label for="inputText" class="col-sm-12 col-form-label" id="total_paquete_deluxe"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Total:</label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold" id="total_qty_paquetes"></label>
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold" id="total_importe_paquetes"></label>
                </div>
              </div>

            </div>
          </div>
        </div>


        <div class="col-lg-6" id="cortesias">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Cortesias</h5>
              <div class="row">
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-3 col-form-label"></label>
                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Sistema </label>
                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Cantidad real</label>
                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Importe</label>
                </div>
                <div class="row mb-3">
                  @error('paquete_basico')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Básico</label>
                  <div class="col-sm-3">
                    <input type="text" name="usd_1" value="" id="p" class="form-control">
                  </div>
                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>

                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_basico_s')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Básico+</label>
                  <div class="col-sm-3">
                    <input type="text" name="usd_1" value="" id="p" class="form-control">
                  </div>
                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>

                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_ultra')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Ultra</label>
                  <div class="col-sm-3">
                    <input type="text" name="usd_1" value="" id="p" class="form-control">
                  </div>
                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>

                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_deluxe')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Deluxe</label>
                  <div class="col-sm-3">
                    <input type="text" name="usd_1" value="" id="p" class="form-control">
                  </div>
                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>

                  <div class="col-sm-3">
                    <input type="text" name="importe_paquete_deluxe" value="" id="paquete_deluxe" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Total:</label>
                  <label for="inputText" class="col-sm-3 col-form-label"></label>
                  <label for="inputText" class="col-sm-3 col-form-label"></label>
                  <label for="inputText" class="col-sm-3 col-form-label"></label>
                </div>
              </div>

            </div>
          </div>
        </div>

        

      </div>
      <div class="row">
        <div class="col-lg-6" id="denominacion_en_pesos">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Denominación en Pesos</h5>

              <div class="row">
                <div class="row mb-3">
                  @error('mxn_1000')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$1,000.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_1000" value="" id="mxn_1000" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_500')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$500.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_500" value="" id="mxn_500" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_200')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$200.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_200" value="" id="mxn_200" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_100')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$100.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_100" value="" id="mxn_100" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_50')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$50.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_50" value="" id="mxn_50" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_20')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$20.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_20" value="" id="mxn_20" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_10')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$10.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_10" value="" id="mxn_10" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_5')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$5.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_5" value="" id="mxn_5" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_2')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$2.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_2" value="" id="mxn_2" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_1')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$1.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="mxn_1" value="" id="mxn_1" class="form-control">
                  </div>
                </div>



                <div class="row mb-3">
                  @error('mxn_1')
                     <br><span class="text-danger">{{ $total_mxn1 }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Total MXN:</label>
                  <div class="col-sm-8">
                    <input type="number" name="total_mxn1" value="" id="total_mxn1" class="form-control" readonly>
                  </div>
                </div>

              </div>

            </div>


          </div>
        </div>
      
        <div class="col-lg-6" id="denominacion_en_dolares">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Denominación en Dólares</h5>

              <div class="row">
                <div class="row mb-3">
                  @error('usd_100')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$100.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_100" value="" id="usd_100" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_50')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$50.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_50" value="" id="usd_50" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_20')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$20.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_20" value="" id="usd_20" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_10')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$10.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_10" value="" id="usd_10" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_1')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$1.00</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_1" value="" id="usd_1" class="form-control">
                  </div>
                </div>


                <div class="row mb-3">
                  @error('usd_1c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Un centavo 1¢</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_1c" value="" id="usd_1c" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_5c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cinco centavos 5¢</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_5c" value="" id="usd_5c" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_10c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cinco centavos 10¢</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_10c" value="" id="usd_10c" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_25c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cuarto de dólar 25¢</label>
                  <div class="col-sm-8">
                    <input type="text" name="usd_25c" value="" id="usd_25c" class="form-control">
                  </div>
                </div>


              </div>

              <div class="row">
          <div class="col-lg-6" id="fondo_de_caja">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Total USD:</h5>
                <div class="row">
                  <div class="col-12">
                    <input type="text" name="total_usd" value="" id="total_usd" class="form-control">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


            </div>
          </div>
        </div>

        


      </div>

      

      <div class="row">

     
        
      </div>

      <div class="row">
        <div class="col-lg-6" id="fondo_de_caja">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Comentarios</h5>
              <div class="row">
                <div class="col-12">
                  <input type="text" name="comentarios" value="" id="comentarios" class="form-control">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-4"><br>
                  <button class="btn btn-warning w-100 submitBtn" tabindex="15" type="submit" id=" submit_btn">Guardar</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
      <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    </form>

    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span></span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ 
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>-->
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

@include('layout.footer')

<script type="text/javascript">
  
  $(document).ready(function(){
    $('#ventas_totales_en_tarjetas').hide();
    $('#desgloce_ventas_paquetes').hide();
    $('#cortesias').hide();
    $('#denominacion_en_pesos').hide();
    $('#denominacion_en_dolares').hide();
    $('#fondo_de_caja').hide();

    var arr_usd = ['usd_100', 'usd_50', 'usd_20', 'usd_10', 'usd_1', 'usd_1c', 'usd_5c', 'usd_10c', 'usd_25c'];
    var arr_mxn = ['mxn_1000', 'mxn_500', 'mxn_200', 'mxn_100', 'mxn_50', 'mxn_20', 'mxn_10', 'mxn_5', 'mxn_2', 'mxn_1'];


  
    function calcularTotales() {
        var totalUsdBilletes = 0;
        var totalUsdMonedas = 0;
        var totalUsd = 0;
        var totalMxn = 0;

        var denominacionesBilletes = ['usd_100', 'usd_50', 'usd_20', 'usd_10', 'usd_1'];
        var denominacionesMonedas = ['usd_1c', 'usd_5c', 'usd_10c', 'usd_25c'];

        for (var i = 0; i < arr_usd.length; i++) {
            var valor = parseFloat($('#' + arr_usd[i]).val()) || 0;
            if (denominacionesBilletes.includes(arr_usd[i])) {
                totalUsdBilletes += valor * parseFloat(arr_usd[i].split('_')[1]);
            } else if (denominacionesMonedas.includes(arr_usd[i])) {
                totalUsdMonedas += valor * parseFloat(arr_usd[i].split('_')[1].replace('c', '')) / 100;
            }
        }

        totalUsd = totalUsdBilletes + totalUsdMonedas;

        for (var i = 0; i < arr_mxn.length; i++) {
            var valor = parseFloat($('#' + arr_mxn[i]).val()) || 0;
            totalMxn += valor * parseFloat(arr_mxn[i].split('_')[1]);
        }

        //$('#total_usd_billetes').val(totalUsdBilletes.toFixed(2));
        //$('#total_usd_monedas').val(totalUsdMonedas.toFixed(2));
        $('#total_usd').val(totalUsd.toFixed(2));
        $('#total_mxn1').val(totalMxn.toFixed(2));

        //$('#total_mxn1').val(totalMxn.toFixed(2));

        let tipoCambio = parseFloat($('#tipo_cambio').val()) || 17;
        let efectivoEnMXN = (totalUsd * tipoCambio) + totalMxn;
        $('#total_efectivo_mxn').val(efectivoEnMXN.toFixed(2));

        calcularEfectivoTotal();
    }

    var arr_usd_selector = arr_usd.map(id => '#' + id).join(',');
    $(document).on('change keyup', arr_usd_selector, calcularTotales);

    var arr_mxn_selector = arr_mxn.map(id => '#' + id).join(',');
    $(document).on('change keyup', arr_mxn_selector, calcularTotales);

    $('#tipo_cambio').on('change keyup', calcularTotales);


  function calcularEfectivoTotal() {
    let tipoCambio = parseFloat($('#tipo_cambio').val()) || 19.0;
    let totalUSD = parseFloat($('#total_usd').val()) || 0;
    let totalMXN = parseFloat($('#total_mxn1').val()) || 0;
    //alert(totalUSD  +' '+ tipoCambio +' '+ totalMXN);


   // let total_efectuvo_mxn_usd efectivoEnMXN = (totalUSD * tipoCambio) + totalMXN;
    let efectivoEnMXN =  totalMXN;
    let efectivoEnUSD =  totalUSD;

    let total_efectuvo_mxn_usd = (totalUSD * tipoCambio) + totalMXN;

    $('#total_efectivo_en_mxn').val(efectivoEnMXN.toFixed(2));

    $('#total_efectivo_en_usd').val(efectivoEnUSD.toFixed(2));

    $('#dinero_recibido').val(total_efectuvo_mxn_usd.toFixed(2));
    

  }

  // Llama esta función en los eventos de cambio
  $('#total_usd, #dinero_acumulado_efectivo, #tipo_cambio').on('change', calcularEfectivoTotal);


  });



    $('#fecha_corte').on('change', function() {
        var fechaCorte = $(this).val();

        $.ajax({
            url: "{{ route('validar.fecha.corte') }}", // Ruta definida en web.php
            method: 'POST',
            data: { fecha_corte: fechaCorte, _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    // Si la validación es exitosa, puedes continuar con el proceso
                    /*console.log('Total de ventas:', response.total_ventas);
                    console.log('Total en ventas:', response.total_monto);
                    console.log('Total en efectivo:', response.total_efectivo);
                    console.log('Total en tarjeta:', response.total_tarjeta);*/

                  $('#ventas_totales_en_tarjetas').show();
                  $('#desgloce_ventas_paquetes').show();
                  $('#cortesias').show();
                  $('#denominacion_en_pesos').show();
                  $('#denominacion_en_dolares').show();
                  $('#fondo_de_caja').show();

                  
                  var cantidad_tarjetas;
                  var cantidad_pago_debito;
                  var cantidad_pago_credito;

                  let total_pago_debito;
                  let total_tarjetas;
                  let total_pago_credito;

                  $('#tipo_pago_debito').text('$00.00');
                  $('#tipo_pago_credito').text('$00.00');
                  $('#tipo_pago_amex').text('$00.00');
                  
                  $.each(response.ventas_por_tipo_pago, function(tipoPago, datos) {
                    //alert(tipoPago+' - '+ datos.total_monto + ' - '+datos.total_transacciones);
                      switch (tipoPago) {
                        case 1: // Debito
                          //alert(datos.total_monto);
                          total_pago_debito = datos.total_monto;
                          cantidad_pago_debito =datos.total_transacciones;
                          $('#qty_pago_debito').html(datos.total_transacciones);
                          $('#total_pago_debito').html('$'+datos.total_monto);
                          break;
                        case 2: // Crédito
                          cantidad_pago_credito =datos.total_transacciones;
                          total_pago_credito = datos.total_monto;
                          $('#qty_pago_credito').html(datos.total_transacciones);
                          $('#total_pago_credito').html('$'+datos.total_monto);
                          break;
                          // Agrega más casos para otros tipos de pago
                      }
                    });

                    total_tarjetas    = (total_pago_debito || 0)+(total_pago_credito || 0);
                    cantidad_tarjetas = (cantidad_pago_debito || 0)+(cantidad_pago_credito || 0);
                    
                    let total_tarjetas_format = total_tarjetas.toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'MXN',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });


                    $('#total_tarjetas').html('$'+total_tarjetas_format);
                    $('#cantidad_tarjetas').html(cantidad_tarjetas);

                    //var total_qty_paquete;
                    let total_qty_paquete     = 0;
                    let total_importe_paquete = 0;

                    var total_qty_paquete_basico;
                    var total_importe_paquete_basico;

                    var total_qty_paquete_basicoplus;
                    var total_importe_paquete_basicoplus;

                    var total_qty_paquete_ultra;
                    var total_importe_paquete_ultra;

                    var total_qty_paquete_deluxe;
                    var total_importe_paquete_deluxe;

                    $.each(response.ventas_por_paquete, function(tipoPaquete, datos) {
                      switch (tipoPaquete) {
                        case '612f067387e473107fda56b0': // Basico
                          total_qty_paquete_basico     = datos.total_transacciones;
                          total_importe_paquete_basico = datos.total_monto;
                          $('#qty_paquete_basico').html(datos.total_transacciones);
                          let montoFormateado1         = datos.total_monto;
                         // let montoFormateado1 = datos.total_monto;
                          let montoFormateado1a = montoFormateado1.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                          });
                          $('#total_paquete_basico').html(montoFormateado1a);
                        break;

                        case '612f057787e473107fda56aa': // Express+
                          total_qty_paquete_basicoplus     = datos.total_transacciones;
                          total_importe_paquete_basicoplus = datos.total_monto;
                          $('#qty_paquete_basico_s').html(datos.total_transacciones);
                          let montoFormateado2             = datos.total_monto;
                          let montoFormateado2a = montoFormateado2.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                          });

                          $('#total_paquete_basico_s').html(montoFormateado2a);
                        break;

                        case '612f1c4f30b90803837e7969': // Ultra
                          total_qty_paquete_ultra     = datos.total_transacciones;
                          total_importe_paquete_ultra = datos.total_monto;
                          $('#qty_paquete_ultra').html(datos.total_transacciones);
                          let montoFormateado3        = datos.total_monto;
                          let montoFormateado3a = montoFormateado3.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                          });

                          $('#total_paquete_ultra').html(montoFormateado3a);
                        break;

                        case '612abcd1c4ce4c141237a356': // Deluxe
                          total_qty_paquete_deluxe     = datos.total_transacciones;
                          total_importe_paquete_deluxe = datos.total_monto;
                          $('#qty_paquete_deluxe').html(datos.total_transacciones);
                          let montoFormateado4         = datos.total_monto;
                          let montoFormateado4a = montoFormateado4.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                          });
                          $('#total_paquete_deluxe').html(montoFormateado4a);
                        break;

                      default:
                        //
                      break;
                      }
                    });

                    //let total_qty_paquete = 0;
                    if (!isNaN(total_qty_paquete_basico)) {
                      total_qty_paquete += parseFloat(total_qty_paquete_basico);
                    }
                    if (!isNaN(total_qty_paquete_basicoplus)) {                     
                      total_qty_paquete += total_qty_paquete_basicoplus;
                       //alert(total_qty_paquete);
                    }
                    if (!isNaN(total_qty_paquete_ultra)) {
                      total_qty_paquete += total_qty_paquete_ultra;
                    }
                    if (!isNaN(total_qty_paquete_deluxe)) {
                      total_qty_paquete += total_qty_paquete_deluxe;
                    }

                    //total_importe_paquete = total_importe_paquete_basico+total_importe_paquete_basicoplus+total_importe_paquete_ultra+total_importe_paquete_deluxe;

                    if (!isNaN(total_importe_paquete_basico)) {
                      total_importe_paquete += parseFloat(total_importe_paquete_basico);
                    }
                    if (!isNaN(total_importe_paquete_basicoplus)) {                     
                      total_importe_paquete += total_importe_paquete_basicoplus;
                      // alert(total_importe_paquete);
                    }
                    if (!isNaN(total_importe_paquete_ultra)) {
                      total_importe_paquete += total_importe_paquete_ultra;
                    }
                    if (!isNaN(total_importe_paquete_deluxe)) {
                      total_importe_paquete += total_importe_paquete_deluxe;
                    }

                    $('#total_qty_paquetes').html(total_qty_paquete);

                    let total_importe_paquete1 = total_importe_paquete;
                    let total_importe_paquete1a = total_importe_paquete1.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                          });

                    $('#total_importe_paquetes').html(total_importe_paquete1a);

                    //alert(total_tarjetas);

                    $('#total_tickets').val(response.total_ventas);
                    $('#total_ventas').val(response.total_monto);
                    $('#dinero_acumulado_efectivo').val(response.total_efectivo);
                    $('#dinero_acumulado_tarjeta').val(response.total_tarjeta);

                    $('#sucursal').removeAttr("disabled");
                    $('#total_ventas').removeAttr("disabled");
                    $('#total_tickets').removeAttr("disabled");
                    $('#dinero_acumulado_efectivo').removeAttr("disabled");
                    $('#dinero_acumulado_tarjeta').removeAttr("disabled");
                    $('#dinero_recibido').removeAttr("disabled");
                    $('#dinero_recibido_1000').removeAttr("disabled");
                    $('#dinero_recibido_500').removeAttr("disabled");
                    $('#dinero_recibido_200').removeAttr("disabled");
                    $('#dinero_recibido_100').removeAttr("disabled");
                    $('#dinero_recibido_50').removeAttr("disabled");
                    $('#dinero_recibido_20').removeAttr("disabled");
                    $('#comentarios').removeAttr("disabled");
                    $('#submit_btn').removeAttr("disabled");

                }
            }
        });
    });



</script>