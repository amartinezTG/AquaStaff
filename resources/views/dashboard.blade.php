@include('layout.shared')

<body>

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

  @include('layout.nav-bar')

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Reporte</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">dashboard </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">


        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-4">
              <div class="card info-card sales-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filtro</h6>
                    </li>

                    <li><a class="dropdown-item" href="#" data-text="Hoy,  {{$DayOfMonth}} {{$LetterOfMonth}}" data-value="{{number_format($TodayTotalTransactions->total,0)}}" data-metric="Ventas" data-filter="Hoy">Hoy</a></li>
                    <li><a class="dropdown-item" href="#" data-text="{{$startOfWeekAndEndWeek}} de {{$LetterOfMonth}} " data-value="{{number_format($WeekTotalTransactions->total,0)}}" data-metric="Ventas" data-filter="Semana">Semana</a></li>
                    <li><a class="dropdown-item" href="#" data-text="<?php echo $catalogs->month[$numberOfMonth]?> " data-value="{{number_format($MonthTotalTransactions->total,0)}}" data-metric="Ventas" data-filter="Mes">Mes</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title" id="Ventas">Ventas <span>| Hoy, <?php echo $catalogs->day_of_week[$dayOfWeek]?> {{$DayOfMonth}} {{$LetterOfMonth}}</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-cart"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="Ventas-Value">{{ number_format($TodayTotalTransactions->total,0)}} </h6>
                      <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <!-- Revenue Card -->
            <div class="col-xxl-4 col-md-4">
              <div class="card info-card revenue-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filtro</h6>
                    </li>

                    <li><a class="dropdown-item" href="#" data-text="Hoy, <?php echo $catalogs->day_of_week[$dayOfWeek]?> {{$DayOfMonth}} {{$LetterOfMonth}}" data-value="${{number_format($dailyByProv['INTERLOGIC']->total ?? 0, 2)}}" data-metric="Ingresos" data-filter="Hoy">Hoy</a></li>

                    <li><a class="dropdown-item" href="#" data-text="{{$startOfWeekAndEndWeek}} de {{$LetterOfMonth}}" data-value="${{number_format($weeklyByProv['INTERLOGIC']->total ?? 0, 2)}} " data-metric="Ingresos" data-filter="Semana">Semana</a></li>

                    <li><a class="dropdown-item" href="#" data-text="<?php echo $catalogs->month[$numberOfMonth]?>" data-value="${{number_format($monthlyByProv['INTERLOGIC']->total ?? 0, 2)}}" data-metric="Ingresos" data-filter="Mes">Mes</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title" id="Ingresos">Ingresos <span>| {{$startOfWeekAndEndWeek}} de {{$LetterOfMonth}}</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="Ingresos-Value">${{ number_format($weeklyByProv['INTERLOGIC']->total ?? 0, 2) }}</h6>
                      <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>

                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Revenue Card -->



            <!-- Customers Card -->
            <div class="col-xxl-4 col-md-4">

              <div class="card info-card customers-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filtro</h6>
                    </li>

                    <li><a class="dropdown-item" href="#" data-text="Hoy, <?php echo $catalogs->day_of_week[$dayOfWeek]?> {{$DayOfMonth}} {{$LetterOfMonth}}" data-value="{{$TodayMemberships}}" data-metric="Clientes" data-filter="Hoy">Hoy</a></li>
                    <li><a class="dropdown-item" href="#" data-text="{{$startOfWeekAndEndWeek}} de {{$LetterOfMonth}}" data-value="{{$WeekMemberships}}" data-metric="Clientes" data-filter="Semana">Semana</a></li>
                    <li><a class="dropdown-item" href="#" data-text="<?php echo $catalogs->month[$numberOfMonth]?>" data-value="{{$MonthMemberships}}" data-metric="Clientes" data-filter="Mes">Mes</a></li>
                  </ul>
                </div>


                <div class="card-body">
                  <h5 class="card-title" id="Clientes">Membresías  <span>| <?php echo $catalogs->month[$numberOfMonth]?></span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="Clientes-Value">{{$MonthMemberships}}</h6>
                      <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>

                    </div>
                  </div>

                </div>


               <!--<div class="card-body">
                  <h5 class="card-title" id="Clientes">Membresías Vendidas<span>| <?php echo $catalogs->month[$numberOfMonth]?></span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="Clientes-Value">{{$MonthMemberships}}</h6>
                      <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>

                    </div>
                  </div>

                </div>-->

              </div>

            </div><!-- End Customers Card -->

           

          <div class="col-7">
<!-- Sucursales -->
          <div class="card">
            <!--<div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filtro</h6>
                </li>

                <li><a class="dropdown-item" href="#">Mes</a></li>
                <li><a class="dropdown-item" href="#">3 Meses</a></li>
                <li><a class="dropdown-item" href="#">6 Meses</a></li>
              </ul>
            </div>-->

            <!--<div class="card-body pb-0">
              <h5 class="card-title">Membresias <span>| 6 meses</span></h5>

              <div id="lineChart" style="min-height: 400px;" id="echart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#lineChart"), {
                    series: [{
                      name: "Membresías nuevas",
                      data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
                    }, {
                      name: "Renovaciones",
                      data: [2, 3, 4, 15, 32, 22, 55, 14, 60]
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
                      categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
                    }
                  }).render();
                });
              </script>

            </div>
          </div>--><!-- End Sucursales -->
          <!--</div>--> 

           

          <!-- Promociones y descuentos -->
          <!--<div class="col-7" id="reporte_diario">
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
                <h5 class="card-title">Reporte Diario <span>| Junio 1, 2024 </span></h5>
                <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th scope="col">Stack & Control</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        
                      </tr>
                      <tr>
                        <td scope="col"></td>
                        <td scope="col"><strong>Inicio de turno </strong></td>
                        <td scope="col"></td>
                       
                      </tr>
                      <tr>
                        <td></td>
                        <td>Efectivo</td>
                        <td>$500.00</td>
                        
                      </tr>
                      <tr>
                        <td></td>
                        <td><strong>Fin de turno</strong></td>
                        <td></td>
                        
                      </tr>
                      <tr>
                        <td></td>
                        <td>Efectivo</td>
                        <td>$1,240.00</td>
                        
                      </tr>
                      <tr>
                        <td></td>
                        <td>Pagos con tarjeta</td>
                        <td>$700.00</td>
                        
                      </tr>
                      
                      <tr>
                        <td></td>
                        <td>Total en Ventas</td>
                        <td>$1,940.00</td>
                        
                      </tr>
                    </thead>
                </table>
              </div>
              </div>
          </div>-->


          <!-- Promociones y descuentos -->
          <!--<div class="col-5" id="reporte_diario2">
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
                <h5 class="card-title">Reporte Diario <span>| Junio 1, 2024 </span></h5>
                <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        
                      </tr>
                      <tr>
                        <td scope="col"></td>
                        <td scope="col"></td>
                        <td scope="col"></td>
                       
                      </tr>
                      <tr>
                        <td></td>
                        <td><strong>Total Deposito Efectivo </strong></td>
                        <td>$1,234.00</td>
                        
                      </tr>
                      <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        
                      </tr>
                      <tr>
                        <td></td>
                        <td><strong>Total Pagos Visa  </strong></td>
                        <td>$1,240.00</td>
                        
                      </tr>
                      <tr>
                        <td></td>
                        <td><strong>Total Ventas</strong></td>
                        <td>$2,474.00</td>
                        
                      </tr>
                      
                     
                    </thead>
                </table>
              </div>
              </div>
          </div>-->


          <!-- Promociones y descuentos -->
          <!--<div class="col-6" id="monedero">
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
                <h5 class="card-title">Monedero<span> | Junio 1, 2024 a Junio 13, 2024</span></h5>
                <table class="table table-borderless ">
                    <thead>
                      <tr>
                        
                        <th scope="col">Tipo Modena</th>
                        <th scope="col"> </th>
                        <th scope="col"></th>
                        <th scope="col">Cantidad</th>
                     
                      </tr>

                      <tr>
                        <td>Billete 500 pesos</td>
                        <td></td>
                        <td></td>
                        <td>400</td>
                      </tr>

                      <tr>
                        <td>Billete 200 pesos</td>
                        <td></td>
                        <td></td>
                        <td>430</td>
                      </tr>

                      <tr>
                        <td>Billete 100 pesos</td>
                        <td></td>
                        <td></td>
                        <td>448</td>
                      </tr>

                      <tr>
                        <td>Billete 50 pesos</td>
                        <td></td>
                        <td></td>
                        <td>470</td>
                      </tr>

                      <tr>
                        <td>Billete 20 pesos</td>
                        <td></td>
                        <td></td>
                        <td>540</td>
                      </tr>

                      <tr>
                        <td>Moneda 20 pesos</td>
                        <td></td>
                        <td></td>
                        <td>580</td>
                      </tr>

                      <tr>
                        <td>Moneda 10 pesos</td>
                        <td></td>
                        <td></td>
                        <td>690</td>
                      </tr>

                      <tr>
                        <td>Moneda 5 pesos</td>
                        <td></td>
                        <td></td>
                        <td>1,100</td>
                      </tr>

                      <tr>
                        <td>Moneda 2 pesos</td>
                        <td></td>
                        <td></td>
                        <td>1,200</td>
                      </tr>

                      <tr>
                        <td>Moneda 1 peso</td>
                        <td></td>
                        <td></td>
                        <td>1,380</td>
                      </tr>
                    </thead>
                </table>
              </div>
              </div>
          </div>-->


          <!-- Promociones y descuentos -->
          <!--<div class="col-6" id="monedero2">
              <div class="card top-selling">
              
              <div class="card-body pb-0">
                <h5 class="card-title">Monedero <span>| Junio 1, 2024 a Junio 13, 2024</span></h5>

              <!-- Bar Chart -->
              <!--<div id="barChart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#barChart"), {
                    series: [{
                      data: [400, 430, 448, 470, 540, 580, 690, 1100, 1200, 1380]
                    }],
                    chart: {
                      type: 'bar',
                      height: 350
                    },
                    plotOptions: {
                      bar: {
                        borderRadius: 4,
                        horizontal: true,
                      }
                    },
                    dataLabels: {
                      enabled: false
                    },
                    xaxis: {
                      categories: ['Billete 500 pesos', 'Billete 200 pesos', 'Billete 100 pesos', 'Billete 50 pesos', 'Billete 20 pesos', 'Moneda 20 pesos', 'Moneda 10 pesos',
                        'Moneda 5 pesos', 'Moneda 2 pesos', 'Moneda 1 peso'
                      ],
                    }
                  }).render();
                });
              </script>
              <!-- End Bar Chart -->
              <!--</div>
              </div>
          </div>-->

          <!-- Promociones y descuentos -->
          <!--<div class="col-12" id="operaciones">
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
              <h5 class="card-title">Operaciones </h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        
                        <th scope="col"># Venta</th>
                        <th scope="col">Producto </th>
                        <th scope="col">Precio</th>
                        <th scope="col">Unidades</th>
                        <th scope="col">Venta</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <th scope="row"><a href="#"><img src="assets/client/shampoo-cera.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Jabón con cera para Autos #12892</a></th>
                        <td>$119.00</td>
                        <td class="fw-bold">1</td>
                        <td>$7,326.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <th scope="row"><a href="#"><img src="assets/client/shampoo-cera.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Jabón con cera para Autos #12892</a></th>
                        <td>$119.00</td>
                        <td class="fw-bold">1</td>
                        <td>$7,326.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <th scope="row"><a href="#"><img src="assets/client/shampoo-cera.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Jabón con cera para Autos #12892</a></th>
                        <td>$119.00</td>
                        <td class="fw-bold">1</td>
                        <td>$7,326.00</td>
                      </tr>
                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <th scope="row"><a href="#"><img src="assets/client/shampoo-cera.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Jabón con cera para Autos #12892</a></th>
                        <td>$119.00</td>
                        <td class="fw-bold">1</td>
                        <td>$7,326.00</td>
                      </tr>


                      <tr>
                        <td>#23423234 <br> Lunes, 3 Junio 2024</td>
                        <th scope="row"><a href="#"><img src="assets/client/shampoo-cera.jpeg" alt=""></a><br>
                        <a href="#" class="text-primary fw-bold">Jabón con cera para Autos #12892</a></th>
                        <td>$119.00</td>
                        <td class="fw-bold">74</td>
                        <td>$7,326.00</td>
                      </tr>
                    </tbody>
                  </table>


            </div>
          </div><!-- End Promociones y descuentos -->
        <!--</div>-->


          <!-- Recent Sales -->
          <!--  <div class="col-12" id="inventario">
              <div class="card recent-sales overflow-auto">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filtro</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Hoy</a></li>
                    <li><a class="dropdown-item" href="#">Semana</a></li>
                    <li><a class="dropdown-item" href="#">Mes</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Niveles de Inventario <span> | Hoy</span></h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th scope="col">Fecha </th>
                        <th scope="col">Producto  </th>
                        <th scope="col">Cantidad Inicial</th>
                        <th scope="col">Cantidad Recibida</th>
                        <th scope="col">Cantidad Utilizada</th>
                        <th scope="col">Cantidad Final</th>
                        <th scope="col">Punto de Reorden</th>
                        <th scope="col">Estado de Inventario</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Detergente</td>
                        <td>100</td>
                        <td>50</td>
                        <td>80</td>
                        <td>70</td>
                        <td>50</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Cera para Autos</td>
                        <td>60</td>
                        <td>30</td>
                        <td>50</td>
                        <td>40</td>
                        <td>300</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Toallas de Microfibra</td>
                        <td>200</td>
                        <td>100</td>
                        <td>150</td>
                        <td>150</td>
                        <td>100</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Limpiador de Vidrios</td>
                        <td>80</td>
                        <td>40</td>
                        <td>60</td>
                        <td>60</td>
                        <td>40</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Shampoo para Autos</td>
                        <td>50</td>
                        <td>20</td>
                        <td>40</td>
                        <td>30</td>
                        <td>20</td>
                        <td><span class="badge bg-warning">Reordenar</span></td>
                      </tr>
                    </tbody>
                  </table>

                </div>

              </div>
            </div>--><!-- End Recent Sales -->




            <!-- Recent Sales --><!-- 
           <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filtro</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Hoy</a></li>
                    <li><a class="dropdown-item" href="#">Ayer</a></li>
                    <li><a class="dropdown-item" href="#">Semana pasada</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Transacciones Recientes <span> | Hoy</span></h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row"><a href="#">#2457</a></th>
                        <td>Brandon Jacob</td>
                        <td><a href="#" class="text-primary">At praesentium minu</a></td>
                        <td>$64</td>
                        <td><span class="badge bg-success">Approved</span></td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#">#2147</a></th>
                        <td>Bridie Kessler</td>
                        <td><a href="#" class="text-primary">Blanditiis dolor omnis similique</a></td>
                        <td>$47</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#">#2049</a></th>
                        <td>Ashleigh Langosh</td>
                        <td><a href="#" class="text-primary">At recusandae consectetur</a></td>
                        <td>$147</td>
                        <td><span class="badge bg-success">Approved</span></td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#">#2644</a></th>
                        <td>Angus Grady</td>
                        <td><a href="#" class="text-primar">Ut voluptatem id earum et</a></td>
                        <td>$67</td>
                        <td><span class="badge bg-danger">Rejected</span></td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#">#2644</a></th>
                        <td>Raheem Lehner</td>
                        <td><a href="#" class="text-primary">Sunt similique distinctio</a></td>
                        <td>$165</td>
                        <td><span class="badge bg-success">Approved</span></td>
                      </tr>
                    </tbody>
                  </table>

                </div>

              </div>
            </div> --><!-- End Recent Sales -->

            

          </div>
        </div><!-- End Left side columns -->



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

  $(document).ready(function() {
    $('.filter .dropdown-item').click(function(e) {
        e.preventDefault();
        
        const metric = $(this).data('metric');
        const filter = $(this).data('filter');
        const text   = $(this).data('text');
        const value  = $(this).data('value');


        // Actualizar el texto de los filtros en el HTML
        $('#'+metric+' span').text(`| ${text}`);
        $('#'+metric+'-Value').text(` ${value}`);


        //alert(metric);   data-text="Febrero" data-value="559" data-metric="Ingresos" data-filter="Mes"

        // Realizar la solicitud AJAX
        /*$.ajax({
            url: '/dashboard', // Ajusta la ruta a tu controlador
            data: { filter: filter },
            success: function(data) {
                $('#salesValue').text(data.totalSales);
                $('#revenueValue').text(data.totalIngresos);
                $('#customersValue').text(data.totalClientes);
            }
        });*/
    });
});

</script>