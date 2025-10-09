@include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/client/AQCC_Isotipo-03.png" alt="">
        <span class="d-none d-lg-block" style="color:#4CB8B8;">AquaAdmin</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

@include('layout.nav-header')

  </header><!-- End Header -->

  
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Cajeros</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Portada</a></li>
          <li class="breadcrumb-item active">Cajeros</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

                <form class="row g-3" method="POST" action="{{ route('cajero') }}">                
                  @csrf
                  <div class="col-md-5">
                    <label class="col-sm-6 col-form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                  </div>

                  <div class="col-md-5">
                    <label class="col-sm-4 col-form-label">Fecha Final</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                  </div>  

                  <div class="col-md-2"><label class="col-sm-4 col-form-label">&nbsp;</label>
                    <button class="btn btn-warning w-100 submitBtn" tabindex="6" type="submit">Consultar</button>
                  </div>                

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="row">

        <div class="col-12">
        <div class="row">
          
          <!-- Número total de transacciones -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">

                <h5 class="card-title ">Número total de transacciones</h5>

                <div class="d-flex justify-content-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-receipt"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{ number_format($totalTransactions) }}</h6>
                    <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>

                  </div>
                </div>
              </div>

            </div>
          </div><!-- End Sales Card -->

          <!-- Valor total de ventas -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Valor total de ventas</h5>
                  
                <div class="d-flex justify-content-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-currency-dollar"></i>
                  </div>
                  <div class="ps-3">
                    <h6>${{ number_format($totalSales,2) }}</h6>
                     <span class="text-success small pt-1 fw-bold">MXN</span> <span class="text-muted small pt-2 ps-1"></span>

                  </div>
                </div>
                
              </div>

            </div>
          </div><!-- End Sales Card -->

          <!-- Valor total de ventas -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Valor promedio por transacción</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-credit-card-2-back"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >${{ number_format($averageSale,2) }}</h6>  
                    <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>   
                  </div>
                </div>
                
              </div>

            </div>
          </div><!-- End Sales Card -->


          <!-- Valor total de ventas -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Tipo de pago más utilizado</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-wallet2"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >{{ $catalogs->folio_payment_type[$mostUsedPaymentType->PaymentType] }}</h6>  
                    <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>   
                  </div>
                </div>
                
              </div>

            </div>
          </div><!-- End Sales Card -->


         
          <!-- Paquetes  -->
          @foreach ($packageData as $package)
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">{{ $catalogs->package_type[$package->Package] }} </h5>

                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-droplet-half"></i>
                  </div>
                  <div class="ps-3">
                    <h6>${{ number_format($package->total_sales,2) }}</h6>
                    <span class="text-success small pt-1 fw-bold">{{ number_format($package->total_purchases) }}</span> <span class="text-muted small pt-2 ps-1"></span>

                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach

        </div>
        </div>
      </div>


      <!-- Left side VENTAS -->
      <div class="row" id="ventas">

        <div class="col-12">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- Promociones y descuentos -->
              <div class="col-12">
                <div class="card top-selling">
                <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Descarga</h6>
                </li>

                <li><a class="dropdown-item" href="#">Descarga Excel</a></li>
              </ul>
            </div>
                  <div class="card-body pb-0">
                    <h5 class="card-title">Ventas <span>| Mayo 05, 2024 - Junio 13, 2024</span></h5>
                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        
                        <th scope="col"># Venta</th>
                        <th scope="col">Cliente </th>
                        <th scope="col">Producto </th>
                        <th scope="col">Precio</th>
                       
                        <th scope="col">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <td><a href="">Daniel Ayala #9273</a></td>

                        <td scope="row"><a href="#"><img src="assets/client/lavado-basico.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Deluxe #12892</a></td>
                        <td>$400.00</td>
                        
                        <td>$400.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <td></td>
                        <td scope="row"><a href="#"><img src="assets/client/lavado-basico.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Ultra #12892</a> <br></td>
                        <td>$300.00</td>
                        
                        <td>$7,326.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <td></td>
                        <td scope="row"><a href="#"><img src="assets/client/lavado-basico.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Básico + #12892</a></td>
                        <td>$200.00</td>
                        
                        <td>$200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <td></td>
                        <td scope="row"><a href="#"><img src="assets/client/lavado-basico.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Deluxe #12892</a></td>
                        <td>$400.00</td>
                      
                        <td>$400.00</td>
                      </tr>

                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <td><a href="">Daniel Ayala #9273</a></td>
                        <td scope="row"><a href="#"><img src="assets/client/lavado-basico.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Básico + #12892</a></td>
                        <td>$200.00</td>
                      
                        <td>$200.00</td>
                      </tr>
                    </tbody>
                  </table>

                  </div>
                </div><!-- End Promociones y descuentos -->

              </div>

            </div>
          </div>
        </div><!-- End Left side columns -->

        
          
      </div>
      <!-- End VENTAS -->




      <!-- Left side CLIENTES -->
      <div class="row" id="clientes">

        <div class="col-12">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- Promociones y descuentos -->
              <div class="col-12">
                <div class="card top-selling">
                  <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Descarga</h6>
                </li>

                <li><a class="dropdown-item" href="#">Descarga Excel</a></li>
              </ul>
            </div>
                
                  <div class="card-body pb-0">
                    <h5 class="card-title">Clientes <span>| Mayo 05, 2024 - Junio 13, 2024</span></h5>
<table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        
                        <th scope="col">Cliente</th>
                       
                        <th scope="col"># de visitas </th>
                       
                       
                        <th scope="col">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>#23423234 <a href="">Daniel Lopez #9273</a></td>
                        <td scope="row">47</td>
                        <td>$1,200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <a href="">Daniel Ayala #9273</a></td>
                        <td scope="row">55</td>
                        <td>$1,200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <a href="">Daniel Hernandez #9273</a></td>
                        <td scope="row">33</td>
                        <td>$1,200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <a href="">Daniel Salazar #9273</a></td>
                        <td scope="row">22</td>
                        <td>$1,200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <a href="">Daniel Martinez #9273</a></td>
                        <td scope="row">66</td>
                        <td>$1,200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <a href="">Daniel Aguirre #9273</a></td>
                        <td scope="row">44</td>
                        <td>$1,200.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <a href="">Daniel Garza #9273</a></td>
                        <td scope="row">5</td>
                        <td>$1,200.00</td>
                      </tr>
                    </tbody>
                  </table>

                  </div>
                </div><!-- End Promociones y descuentos -->

              </div>

            </div>
          </div>
        </div><!-- End Left side columns -->
          
      </div>
      <!-- End CLIENTES -->



      <!-- Left side SERVICIOS -->
      <div class="row" id="servicios">

        <div class="col-12">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- Promociones y descuentos -->
              <div class="col-12">
                <div class="card top-selling">
                <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Descarga</h6>
                </li>

                <li><a class="dropdown-item" href="#">Descarga Excel</a></li>
              </ul>
            </div>
                  <div class="card-body pb-0">
                    <h5 class="card-title">Servicios <span>| Mayo 05, 2024 - Junio 13, 2024</span></h5>
                      <table class="table table-borderless datatable">
                        <thead>
                          <tr>
          
                            <th scope="col">Cliente</th>
                           
                            <th scope="col"># de servicios </th>
                           
                            <th scope="col">Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>#23423234 <a href="">DELUXE #9273</a></td>
                            <td scope="row">47</td>
                            <td>$11,200.00</td>
                          </tr>
                          <tr>
                            <td>#23423234 <a href="">BASICO #9273</a></td>
                            <td scope="row">47</td>
                            <td>$11,200.00</td>
                          </tr>
                          <tr>
                            <td>#23423234 <a href="">BASICO + #9273</a></td>
                            <td scope="row">47</td>
                            <td>$11,200.00</td>
                          </tr>
                          
                          <tr>
                            <td>#23423234 <a href="">DELUXE #9273</a></td>
                            <td scope="row">47</td>
                            <td>$11,200.00</td>
                          </tr>
                          <tr>
                            <td>#23423234 <a href="">DELUXE #9273</a></td>
                            <td scope="row">47</td>
                            <td>$11,200.00</td>
                          </tr>
                        </tbody>
                      </table>

                  </div>
                </div><!-- End Promociones y descuentos -->

              </div>

            </div>
          </div>
        </div><!-- End Left side columns -->

          
      </div>
      <!-- End SERVICIOS -->



      <!-- Left side ANALISIS -->
      <div class="row" id="analisis">

        <div class="col-8">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- Promociones y descuentos -->
              <div class="col-12">
                <div class="card top-selling">
                <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Descarga</h6>
                </li>

                <li><a class="dropdown-item" href="#">Descarga Excel</a></li>
              </ul>
            </div>
                  <div class="card-body pb-0">
                    <h5 class="card-title">Analísis de pago <span>| Mayo 05, 2024 - Junio 13, 2024</span></h5>
                      <table class="table table-borderless datatable">
                        <thead>
                          <tr>
                            <th scope="col">TIPO</th>
                            <th scope="col">Movimientos</th>
                           
                            <th scope="col">Monto</th>
                        
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>EFECTIVO</td>
                            <td>451</a></td>
                            <td>$145.254.00</a></td>
                                                       
                          </tr>
                          <tr>
                            <td>VISA</td>
                            <td>123</a></td>
                            <td>$142,254.00</a></td>
                                                       
                          </tr>

                          <tr>
                            <td>MASTERCARD</td>
                            <td>23423</a></td>
                            <td>$845,654.00</a></td>
                                                       
                          </tr>


                          
                        </tbody>
                      </table>

                  </div>
                </div><!-- End Promociones y descuentos -->

              </div>

            </div>
          </div>


        </div><!-- End Left side columns -->

        <div class="col-lg-4" >
          <!-- Sales Card -->
          <div class="col-xxl-12 col-md-12">
            <div class="card info-card sales-card">

              <div class="card-body">
                <h5 class="card-title">PPAGOS <span></span></h5>

              <!-- Pie Chart -->
              <div id="pieChart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#pieChart"), {
                    series: [44, 13, 43],
                    chart: {
                      height: 350,
                      type: 'pie',
                      toolbar: {
                        show: true
                      }
                    },
                    labels: ['Efectivo', 'VISA', 'MASTERCARD']
                  }).render();
                });
              </script>
              <!-- End Pie Chart -->
              </div>

            </div>
          </div><!-- End Sales Card -->


         


        </div>
          
      </div>
      <!-- End ANALISIS  -->
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">
  
  $(document).ready(function(){
      $("#ventas").hide();
      $("#clientes").hide();
      $("#servicios").hide();
      $("#membresias").hide();
      $("#analisis").hide();

    $('#colorselector').on('change', function() {

      $("#ventas").hide();
      $("#monedero").hide();
      $("#servicios").hide();
      $("#membresias").hide();
      $("#analisis").hide();
      
      if ( this.value == 'ventas'){
        $("#ventas").show();
      }

      if ( this.value == 'clientes'){
        $("#clientes").show();
      }

      if ( this.value == 'servicios'){
        $("#servicios").show();
      }

      if ( this.value == 'membresias'){
        $("#membresias").show();
      }

      if ( this.value == 'analisis'){
        $("#analisis").show();
      }
    });
  });