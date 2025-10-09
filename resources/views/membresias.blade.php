@include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="#" class="logo d-flex align-items-center">
        <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">

      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

@include('layout.nav-header')

  </header><!-- End Header -->

@include('layout.nav-bar')

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Reporte</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Portada</a></li>
          <li class="breadcrumb-item active">Membresías </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

                <form class="row g-3" method="POST" action="{{ route('membresias') }}">                
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
          <div class="card">
            <div class="col-12">

              <div class="card-body">
                  <div class="col-12">
                    <label class="col-md-12 col-form-label text-center"><strong>Resultados del</strong> {{$DateVisual}} </label>
                  </div>  
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

                <h5 class="card-title ">Número de membresías vendidas</h5>

                <div class="d-flex justify-content-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-person-bounding-box"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{$totalMemberships}}</h6>
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
                <h5 class="card-title">Tasa de Renovación</h5>
                  
                <div class="d-flex justify-content-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-person-badge"></i>
                  </div>
                  <div class="ps-3">
                    <h6>{{$renewalRate}} </h6>
                     <span class="text-success small pt-1 fw-bold">%</span> <span class="text-muted small pt-2 ps-1"></span>

                  </div>
                </div>
                
              </div>

            </div>
          </div><!-- End Sales Card -->


          <!-- Valor total de ventas -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Duración Promedio  de las membresías</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-calendar3"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >{{$averageDuration}}</h6>
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
                <h5 class="card-title">Valor total de las membresías</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cash-coin"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >{{$totalValue}}</h6>  
                    <span class="text-success small pt-1 fw-bold">$</span> <span class="text-muted small pt-2 ps-1"></span>   
                  </div>
                </div>
                
              </div>

            </div>
          </div><!-- End Sales Card -->

          <!-- Valor total de ventas -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Paquetes Más Populares</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cart3"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >{{$popularPackages}}</h6>  
                    <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>   
                  </div>
                </div>
                
              </div>

            </div>
          </div><!-- End Sales Card -->

        </div>
        </div>
      </div>
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

           

          <!-- Top Selling 
            <div class="col-12">
              <div class="card  overflow-auto">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body pb-0">
                  <h5 class="card-title">Retención de Clientes</h5>-->

              <!-- Table with stripped rows
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Clientes Iniciales</th>
                    <th scope="col">Clientes Retenidos</th>
                    <th scope="col">Nuevos Clientes</th>
                    <th scope="col">Tasa de Retención (%)</th>
                    <th scope="col">Total Clientes</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row">01/2024</th>
                    <td>1000</td>
                    <td>950</td>
                    <td>100</td>
                    <td>95%</td>
                    <td>1050</td>
                  </tr>
                  <tr>
                    <th scope="row">02/2024</th>
                    <td>1050</td>
                    <td>980</td>
                    <td>150</td>
                    <td>93.33%</td>
                    <td>1130</td>
                  </tr>
                  <tr>
                    <th scope="row">03/2024</th>
                    <td>1130</td>
                    <td>1020</td>
                    <td>180</td>
                    <td>90.27%</td>
                    <td>1200</td>
                  </tr>
                  <tr>
                    <th scope="row">04/2024</th>
                    <td>1200</td>
                    <td>1150</td>
                    <td>120</td>
                    <td>95.83%</td>
                    <td>1270</td>
                  </tr>
                  <tr>
                    <th scope="row">05/2024</th>
                    <td>1270</td>
                    <td>1220</td>
                    <td>200</td>
                    <td>96.06%</td>
                    <td>1420</td>
                  </tr>
                </tbody>
              </table> -->
              <!-- End Table with stripped rows 

                  

                </div>

              </div>
            </div><!-- End Top Selling -->


          <!-- Right side columns -->
        <div class="col-lg-12">

          <!-- Sucursales -->
          <div class="card">
            

            <div class="card-body pb-0">
              <h5 class="card-title">Membresias </h5>

              <div id="lineChart" style="min-height: 400px;" id="echart"></div>

              <script>

                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#lineChart"), {
                    series: [{
                      name: "Membresías nuevas",
                      //data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
                      data: <?php echo json_encode($membershipsChart); ?> 
                    }, {
                      name: "Renovaciones",
                      //data: [2, 3, 4, 15, 32, 22, 55, 14, 60]
                      data: <?php echo json_encode($renewals); ?> 
                    }],
                    chart: {
                      height: 350,
                      type: 'line',
                      zoom: {
                        enabled: false
                      }
                    },
                    dataLabels: {
                      enabled: false
                    },
                    stroke: {
                      curve: 'straight'
                    },
                    grid: {
                      row: {
                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                      },
                    },
                    xaxis: {
                      //categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
                     categories:  <?php echo json_encode($months); ?>
                    }
                  }).render();
                });

              </script>

            </div>
          </div><!-- End Sucursales -->



            <!-- Right side columns -->
        <div class="col-lg-12">

          <!-- Sucursales -->
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Descarga</h6>
                </li>

               
              </ul>
            </div>
                  <div class="card-body pb-0">
                    <h5 class="card-title">Membresias <span></span></h5>
                      <table id="datatable" class=" table-responsive" >
                        <thead>
                          <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Cliente</th>
                           
                            <th scope="col">Sucursal</th>
                           
                        
                          </tr>
                        </thead>
                        @foreach ($memberships as $membership)
                        <tbody>
                          <tr>
                            <td>{{$membership->start_date}}</td>
                            <td>{{$membership->membership_id}}</a></td>
                            <td><a href="">{{$membership->facility}}</a></td>
                                                       
                          </tr>
                          
                        </tbody>
                        @endforeach
                      </table>

                  </div>
          </div><!-- End Sucursales -->
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">
  
  $(document).ready(function(){
    $('#colorselector').on('change', function() {
      $("#reporte_diario").hide();
      $("#reporte_diario2").hide();
      $("#monedero").hide();
      $("#monedero2").hide();
      $("#operaciones").hide();
      $("#inventario").hide();
      
      if ( this.value == 'reporte_diario'){
        $("#reporte_diario").show();
        $("#reporte_diario2").show();
      }

      if ( this.value == 'monedero'){
        $("#monedero").show();
        $("#monedero2").show();
      }

      if ( this.value == 'operaciones'){
        $("#operaciones").show();
      }

      if ( this.value == 'inventario'){
        $("#inventario").show();
      }
    });
  });

</script>