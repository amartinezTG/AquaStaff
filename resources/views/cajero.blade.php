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
                    <input type="datetime-local" class="form-control" name="start_date" value="{{ $startDate }}">
                  </div>

                  <div class="col-md-5">
                    <label class="col-sm-4 col-form-label">Fecha Final</label>
                    <input type="datetime-local" class="form-control" name="end_date" value="{{ $endDate }}">
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
                      
                     <h6>
                    {{$catalogs->folio_payment_type[$mostUsedPaymentType->PaymentType]}}
                       </h6>
                      <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1">{{$mostUsedPaymentType->total}}</span> 
                   
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
                @if ($package->Package === null)
                    <h5 class="card-title">Lavados por Membresía</h5>
                @else
                    <h5 class="card-title">{{ $catalogs->package_type[$package->Package] }}</h5>
                @endif

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-droplet-half"></i>
                    </div>
                    <div class="ps-3">
                        <h6>${{ number_format($package->total_sales, 2) }}</h6>
                        <span class="text-success small pt-1 fw-bold">{{ number_format($package->total_purchases) }}</span>
                        <span class="text-muted small pt-2 ps-1"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Métodos de Pago</h5>

              <!-- Pie Chart -->
              <div id="pieChart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#pieChart"), {
                    series: @json($chartUsedPayment['series']),
                    chart: {
                      height: 350,
                      type: 'pie',
                      toolbar: {
                        show: true
                      }
                    },
                    labels: @json($chartUsedPayment['labels'])
                  }).render();
                });
              </script>
              <!-- End Pie Chart -->

            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Paquetes</h5>

              <!-- Donut Chart -->
              <div id="donutChart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#donutChart"), {
                    series:  @json($packageChart['series']),
                    chart: {
                      height: 350,
                      type: 'donut',
                      toolbar: {
                        show: true
                      }
                    },
                    labels: @json($packageChart['labels']),
                  }).render();
                });
              </script>
              <!-- End Donut Chart -->

            </div>
          </div>
        </div>

      </div>

      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tráfico de ventas por horario</h5>
              <!-- Line Chart -->
              <div id="lineChartHr"></div>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                    new ApexCharts(document.querySelector("#lineChartHr"), {
                        series: @json($chartDataPie['series']),
                        chart: {
                            height: 350,
                            type: 'pie',
                            toolbar: {
                                show: true
                            }
                        },
                        labels: @json($chartDataPie['labels'])
                    }).render();
                });
              </script>
              <!-- End Line Chart -->
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tráfico de ventas (promedio por hora)</h5>
              <!-- Line Chart -->
              <div id="lineChart"></div>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#lineChart"), {
                    series: @json($chartData['datasets']),
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
                      categories: @json($chartData['labels']),
                      labels: {
                          datetimeFormatter: {
                              year: 'yyyy',
                              month: 'MMM',
                              day: 'dd',
                              hour: 'HH:mm'
                          }
                      }
                    }
                  }).render();
                });
              </script>
              <!-- End Line Chart -->
            </div>
          </div>
        </div>

        
      </div>



      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tráfico de ventas (promedio por hora)</h5>
              <p><a href="{{ route('exportar-trafico-ventas', ['startDate' => urlencode($startDate), 'endDate' => urlencode($endDate)]) }}">Descargar Reporte</a></p>
              <!--<p><a href="/exportar-trafico-ventas/{{$startDate}}/{{$endDate}}">Descargar Reporte</a></p>-->
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead class="thead-dark">
                      <tr>
                        <th class="table-warning">Hora</th>
                        <th class="table-secondary">Paquete Express</th>
                        <th>Venta total<br> paquete<br> express</th>
                        <th class="table-secondary">Paquete Básico</th>
                        <th>Venta total<br> paquete<br> Básico</th>
                        <th class="table-secondary">Paquete Ultra</th>
                        <th>Venta total<br> paquete<br> Ultra</th>
                        <th class="table-secondary">Paquete Deluxe</th>
                        <th>Venta total<br> paquete<br> Deluxe</th>
                        <th class="table-secondary">Compras por Membresía</th>
                        <th>Venta total<br> Compras por Membresía</th>
                        <th class="table-secondary">Total <br>paquetes <br>del día</th>
                        <th>Total venta</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($formattedData as $range => $data)
                      <tr>
                        <td class="table-warning">{{ isset($catalogs->business_hours[$range]) ? $catalogs->business_hours[$range] : $range }}</td>
                        <td class="table-secondary text-center">{{ $data['612f057787e473107fda56aa']['total_records'] ?? 0 }}</td>
                        <td class="text-center">${{ number_format(($data['612f057787e473107fda56aa']['total_sales'] ?? 0), 2) }}</td>
                        <td class="table-secondary text-center">{{ $data['612f067387e473107fda56b0']['total_records'] ?? 0 }}</td>
                        <td class="text-center">${{ number_format(($data['612f067387e473107fda56b0']['total_sales'] ?? 0), 2) }}</td>
                        <td class="table-secondary text-center">{{ $data['612f1c4f30b90803837e7969']['total_records'] ?? 0 }}</td>
                        <td class="text-center">${{ number_format(($data['612f1c4f30b90803837e7969']['total_sales'] ?? 0), 2) }}</td>
                        <td class="table-secondary text-center">{{ $data['612abcd1c4ce4c141237a356']['total_records'] ?? 0 }}</td>
                        <td class="text-center">${{ number_format(($data['612abcd1c4ce4c141237a356']['total_sales'] ?? 0), 2) }}</td>
                        <td class="table-secondary text-center">{{ $data['null']['total_records'] ?? 0 }}</td>
                        <td class="text-center">${{ number_format(($data['null']['total_sales'] ?? 0), 2) }}</td>
                        <td class="table-secondary text-center">
                          {{ ($data['612f057787e473107fda56aa']['total_records'] ?? 0) +
                             ($data['612f067387e473107fda56b0']['total_records'] ?? 0) +
                             ($data['612f1c4f30b90803837e7969']['total_records'] ?? 0) +
                             ($data['612abcd1c4ce4c141237a356']['total_records'] ?? 0) +
                             ($data['null']['total_records'] ?? 0) }}
                        </td>
                        <td class="text-center">
                          ${{ number_format(
                          ($data['612f057787e473107fda56aa']['total_sales'] ?? 0) +
                          ($data['612f067387e473107fda56b0']['total_sales'] ?? 0) +
                          ($data['612f1c4f30b90803837e7969']['total_sales'] ?? 0) +
                          ($data['612abcd1c4ce4c141237a356']['total_sales'] ?? 0) +
                          ($data['null']['total_sales'] ?? 0), 2) }}
                        </td>
                      </tr>
                        @endforeach
                      <tr>
                        <td class="table-warning"><strong>Total día</strong></td>
                        <td class="table-secondary text-center"><strong>{{ $totals['612f057787e473107fda56aa_records'] }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totals['612f057787e473107fda56aa_sales'], 2) }}</strong></td>
                        <td class="table-secondary text-center"><strong>{{ $totals['612f067387e473107fda56b0_records'] }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totals['612f067387e473107fda56b0_sales'], 2) }}</strong></td>
                        <td class="table-secondary text-center"><strong>{{ $totals['612f1c4f30b90803837e7969_records'] }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totals['612f1c4f30b90803837e7969_sales'], 2) }}</strong></td>
                        <td class="table-secondary text-center"><strong>{{ $totals['612abcd1c4ce4c141237a356_records'] }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totals['612abcd1c4ce4c141237a356_sales'], 2) }}</strong></td>
                        <td class="table-secondary text-center"><strong>{{ $totals['null_records'] }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totals['null_sales'], 2) }}</strong></td>
                        <td class="table-secondary text-center"><strong>{{ $totals['total_records'] }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totals['total_sales'], 2) }}</strong></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
            </div>
          </div>
          
        </div>

        <div class="col-lg-4">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Promociones</h5>
              <!-- Table with stripped rows -->
              <div class="table-responsive">
                <table class="table table-bordered" >
                  <thead class="thead-dark">
                    <tr>
                      <th class="table-warning"></th>
                      <th class="table-warning">Paquete <br> express</th>
                      <th class="table-secondary">Paquete <br> básico</th>
                      <th class="table-secondary">Paquete <br> ultra</th>
                      <th class="table-secondary">Paquete <br> deluxe</th>
                      <th class="table-secondary">PaqTotaluete <br> promociones</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>

        </div>

        <div class="row">
        @if ($weatherData->isNotEmpty())
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Información del Clima ({{ $startDate }} a {{ $endDate }})</h5>
              <div class="table-responsive">
                <table class="table table-bordered" id="datatableRows">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Temperatura (°C)</th>
                      <th>Descripción</th>
                      <th>Velocidad del Viento (km/h)</th>
                      <th>Humedad (%)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($weatherData as $weather)
                    <tr>
                      <td>{{ Carbon\Carbon::parse($weather->created_at)->format('m-d-Y') }}</td>
                      <td>{{ Carbon\Carbon::parse($weather->created_at)->format('h:i A') }}</td>
                      <td>{{ $weather->temperature }}</td>
                      <td>{{isset($catalogs->weather_description[$weather->weather_descriptions]) ? $catalogs->weather_description[$weather->weather_descriptions] : $weather->weather_descriptions }}</td>
                      <td>{{ $weather->wind_speed }}</td>
                      <td>{{ $weather->humidity }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @else
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">No hay datos del clima disponibles para el rango de fechas seleccionado.</h5>
            </div>
          </div>
        @endif

      </div>

      <div class="row">
        <div class="col-lg-4">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Membresias</h5>
              <!-- Table with stripped rows -->
              <div class="table-responsive">
                <table class="table table-bordered" >
                  <thead class="thead-dark">
                    <tr>
                      
                      <th class="table-warning">Paquete <br> express</th>
                      <th class="table-secondary">Paquete <br> básico</th>
                      <th class="table-secondary">Paquete <br> ultra</th>
                      <th class="table-secondary">Paquete <br> deluxe</th>
                      <th class="table-secondary">PaqTotaluete <br> promociones</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>

        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Cortesias</h5>
              <!-- Table with stripped rows -->
              <div class="table-responsive">
                <table class="table table-bordered" >
                  <thead class="thead-dark">
                    <tr>
                      <th class="table-warning">Paquete <br> express</th>
                      <th class="table-secondary">Paquete <br> básico</th>
                      <th class="table-secondary">Paquete <br> ultra</th>
                      <th class="table-secondary">Paquete <br> deluxe</th>
                      <th class="table-secondary">PaqTotaluete <br> promociones</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        


      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Listado de transacciones</h5>
              <p></p>
              <!-- Table with stripped rows -->
              <div class="table-responsive">
              <table id="datatable" class=" table-responsive" id="datatableRows" >
                <thead>
                  <tr>
                    <th>Fecha </th>
                    <th>Método de Pago</th>
                    <th>Total</th>
                    <th>Membresia</th>
                    <th>Paquete</th>
                    <th>Folio</th>
                  </tr>
                </thead>
                <tbody>
                   <!-- Ordenes  -->
                  @foreach ($totalTransactionsList as $orders)
                  <tr>
                    <td align="right">{{$orders->TransationDate}}</td>
                    <td>{{$catalogs->folio_payment_type[$orders->PaymentType]}}</td>
                    <td>${{number_format($orders->Total,2)}}</td>
                    <td>{{$orders->Membership}}</td>
                    <td>{{$catalogs->package_type[$orders->Package]}}</td>
                    <td>{{$orders->PaymentFolio}}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            </div>
          </div>
        </div>
      </div>

    </section><a href="{{ route('exportar-csv', ['startDate' => urlencode($startDate), 'endDate' => urlencode($endDate)]) }}">indicadores</a>

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