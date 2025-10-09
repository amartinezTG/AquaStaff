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

 
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Corte de Caja</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active"><a href="/corte_caja">Corte de Caja</a></li>
          <li class="breadcrumb-item active">Detalle</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">
        <div class="col-lg-6">
          <div class="card">
           
            <div class="card-body">
              <h5 class="card-title">Corte de Caja</h5>

              
              <a href="{{ route('detalle_corte_export', $corte_id) }}" class="btn btn-success">Descargar Excel</a>

              <a href="{{ route('detalle_corte_pdf', $corte_id) }}" class="btn btn-info" target="_blank">Descargar PDF</a>

              @if(session('success'))
                        <div class="alert alert-success">
                          {{ session('success') }}
                        </div>
                      @endif
              
                <div class="row">

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Fecha </label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                   

                    <label class="col-sm-4 col-form-label">{{ $corteCaja->fecha_corte }}</label>
                    
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Sucursal</label>
                
                  @error('sucursal')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    
                    <label class="col-sm-4 col-form-label">{{ $corteCaja->sucursal }}</label>
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Dinero recibido </label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <label class="col-sm-4 col-form-label" >${{ number_format($corteCaja->dinero_recibido,2) }}</label>
                    
                  </div>
                </div>          

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Total Vehículos </label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">

                    <label class="col-sm-4 col-form-label">${{ number_format($corteCaja->total_tickets,2) }}</label>
                    
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Dinero Acumulado Efectivo </label>
                
                  @error('dinero_acumulado_efectivo')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                    
                    <label class="col-sm-4 col-form-label">${{ number_format($corteCaja->dinero_acumulado_efectivo,2) }}</label>

               
                    
                  </div>
                </div>

                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Dinero Acumulado Tarjeta </label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">

                    <label class="col-sm-4 col-form-label">${{ number_format($corteCaja->dinero_acumulado_tarjeta,2) }}</label>
                    
                  </div>
                </div>

                

                


                <div class="col-6">
                  <label for="tipo_cambio" class="form-label fw-bold">Tipo de cambio </label><br>
                  
                  <label class="col-sm-4 col-form-label">${{ $tipoCambio ?? 20.0 }}</label>  

                </div>

                <div class="col-6">
                  <label for="tipo_cambio" class="form-label fw-bold">Total efectivo MXN </label><br>
                  <label class="col-sm-4 col-form-label" id="total_efectivo_en_mxn">${{ number_format($total_efectivo_en_mxn,2) }} </label>
                  
                </div>


                <div class="col-6">
                  <label for="email" class="form-label fw-bold">Total Ventas </label>
                
                  @error('email')
                    <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  <div class="input-group">
                       
                    <label class="col-sm-4 col-form-label">${{ number_format($corteCaja->total_ventas,2) }}</label>
                    
                  </div>
                </div>    

                <div class="col-6">
                  <label for="tipo_cambio" class="form-label fw-bold">Total efectivo USD </label><br>
                  <label class="col-sm-4 col-form-label" id="total_efectivo_en_usd">${{ number_format($total_usd,2) }}</label>
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

                @php
                  $tipos = [
                    1 => 'Débito',
                    2 => 'Crédito',
                    3 => 'AMEX',
                  ];

                  $totalCantidad = 0;
                  $totalImporte  = 0;
                @endphp
                @foreach ($tipos as $tipo => $label)
                @php
                  $cantidad = $ventasPorTipoPago[$tipo]['total_transacciones'] ?? 0;
                  $monto    = $ventasPorTipoPago[$tipo]['total_monto'] ?? '0.00';
                  $totalCantidad += $cantidad;
                  $totalImporte += floatval(str_replace(',', '', $monto));
                @endphp
                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label fw-bold">{{ $label }}</label>
                  <div class="col-sm-4">
                    <label class="col-sm-12 col-form-label">{{ $cantidad }}</label>
                  </div>
                  <div class="col-sm-4">
                    <label class="col-sm-12 col-form-label">${{ $monto }}</label>
                  </div>
                </div>
              @endforeach

              <div class="row mt-4 mb-4">
                <label class="col-sm-4 col-form-label fw-bold">Total Tarjetas:</label>
                <label class="col-sm-4 col-form-label fw-bold">{{ $totalCantidad }}</label>
                <label class="col-sm-4 col-form-label fw-bold">${{ number_format($totalImporte, 2) }}</label>
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

              <div class="row mb-3">
                <label class="col-sm-4 col-form-label fw-bold">Paquete</label>
                <label class="col-sm-4 col-form-label fw-bold">Cantidad</label>
                <label class="col-sm-4 col-form-label fw-bold">Importe</label>
              </div>

              @php
                $totalQty = 0;
                $totalImporte = 0;
              @endphp

              @foreach ($catalogs->package_type as $packageId => $packageName)
                @php
                  $cantidad = $ventasPorPaquete[$packageId]['total_transacciones'] ?? 0;
                  $importe = $ventasPorPaquete[$packageId]['total_monto'] ?? 0;

                  $totalQty += $cantidad;
                  $totalImporte += $importe;
                @endphp
                <div class="row mb-2">
                  <label class="col-sm-4 col-form-label fw-bold">{{ $packageName }}</label>
                  <div class="col-sm-4">
                    <label class="col-sm-12 col-form-label">{{ $cantidad }}</label>
                  </div>
                  <div class="col-sm-4">
                    <label class="col-sm-12 col-form-label">${{ number_format($importe, 2) }}</label>
                  </div>
                </div>
              @endforeach

              <div class="row mt-3 mb-4">
                <label class="col-sm-4 col-form-label fw-bold">Total:</label>
                <label class="col-sm-4 col-form-label fw-bold">{{ $totalQty }}</label>
                <label class="col-sm-4 col-form-label fw-bold">${{ number_format($totalImporte, 2) }}</label>
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
                   
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>

                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_basico_s')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Básico+</label>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>

                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_ultra')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Ultra</label>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>

                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('paquete_deluxe')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-3 col-form-label fw-bold">Paquete Deluxe</label>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>
                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
                  </div>

                  <div class="col-sm-3">
                    <label class="col-sm-12 col-form-label"></label>
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
                    

                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_1000')->first()->cantidad ?? 0 }}</label>
                    
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_500')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$500.00</label>
                  <div class="col-sm-8">

                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_500')->first()->cantidad ?? 0 }}</label>

                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_200')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$200.00</label>
                  <div class="col-sm-8">
                    
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_200')->first()->cantidad ?? 0 }}</label>

                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_100')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$100.00</label>
                  <div class="col-sm-8">

                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_100')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_50')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$50.00</label>
                  <div class="col-sm-8">
                    
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_50')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_20')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$20.00</label>
                  <div class="col-sm-8">
              
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_20')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_10')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$10.00</label>
                  <div class="col-sm-8">
                
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_10')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_5')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$5.00</label>
                  <div class="col-sm-8">
             
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_5')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_2')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$2.00</label>
                  <div class="col-sm-8">
               
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_2')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_1')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$1.00</label>
                  <div class="col-sm-8">
                    
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'mxn_1')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>



                <div class="row mb-3">
                  @error('mxn_1')
                     <br><span class="text-danger">{{ $total_mxn1 }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Total MXN:</label>
                  <div class="col-sm-8">
                   
                    <label class="col-sm-4 col-form-label fw-bold" id="total_mxn1" >${{ number_format($total_mxn,2) }}</label>

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
                
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_100')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_50')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$50.00</label>
                  <div class="col-sm-8">
            
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_50')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_20')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$20.00</label>
                  <div class="col-sm-8">
                  
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_20')->first()->cantidad ?? 0 }}</label>
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_10')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$10.00</label>
                  <div class="col-sm-8">
                 
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_10')->first()->cantidad ?? 0 }}</label>
                 
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_1')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">$1.00</label>
                  <div class="col-sm-8">
               
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_1')->first()->cantidad ?? 0 }}</label>
                 
                  </div>
                </div>


                <div class="row mb-3">
                  @error('usd_1c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Un centavo 1¢</label>
                  <div class="col-sm-8">
                   
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_1c')->first()->cantidad ?? 0 }}</label>
                 
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_5c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cinco centavos 5¢</label>
                  <div class="col-sm-8">
                   
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_5c')->first()->cantidad ?? 0 }}</label>
                 
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_10c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cinco centavos 10¢</label>
                  <div class="col-sm-8">

                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_10c')->first()->cantidad ?? 0 }}</label>
                 
                  </div>
                </div>

                <div class="row mb-3">
                  @error('usd_25c')
                     <br><span class="text-danger">{{ $message }}</span>
                  @enderror

                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Cuarto de dólar 25¢</label>
                  <div class="col-sm-8">
                 
                    <label class="col-sm-4 col-form-label">${{ $detallesArqueo->where('denominacion', 'usd_25c')->first()->cantidad ?? 0 }}</label>
                 
                  </div>
                </div>

                <div class="row mb-3">
                  @error('mxn_1')
                     <br><span class="text-danger">{{ $total_usd }}</span>
                  @enderror
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold">Total USD:</label>
                  <div class="col-sm-8">
                    <label for="inputText" class="col-sm-4 col-form-label fw-bold" id="total_usd">${{ number_format($total_usd,2) }}</label>
                    
                  </div>
                </div>


              </div>

            </div>
          </div>
        </div>

      </div>

      <div class="row">
        <div class="col-lg-6" id="fondo_de_caja">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Comentarios</h5>
              <div class="row">
                <div class="col-12">
                  
                  <label for="inputText" class="col-sm-4 col-form-label fw-bold" >{{ $Comentarios }}</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

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




