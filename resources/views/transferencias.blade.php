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
      <h1>Reporte</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">Transferencias </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">


            <!-- Reports -->
            <!--<div class="col-12">
              <div class="card">
                <div class="filter">
                </div>
                <div class="col-12">
                  <div class="card-body">

                    <form class="row g-3">
                      
                      <div class="col-md-6">
                        <label class="col-sm-4 col-form-label">Fecha Inicio</label>
                        <input type="date" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="col-sm-4 col-form-label">Fecha Final</label>
                        <input type="date" class="form-control">
                      </div>
                      
                      
                    </form>

                  </div>
                </div>

              </div>
            </div> -->

            <!-- Transferencias -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Transferencias </h5>
                  <p><a href="/crear_transferencia">Crear Transferencia</a></p>

               

                  <table class="table table-borderless ">
                    <thead>
                      <tr>                        
                        <th scope="col">ID</th>
                        <th scope="col">Fecha de transferencia</th>
                        <th scope="col">Nombre</th>
                                             
                        <th scope="col">Origen</th>
                        <th scope="col">Destino</th>
                        <th scope="col">Estado de Transferencia</th>
                        <th scope="col">Fecha de Recepción</th>
                
                        <th scope="col"></th>                        
                      </tr>
                    </thead>

                    <tbody>
                      @foreach ($transferencias as $transfers)
                      <tr>
                        <td scope="row">{{$transfers->transfer_id}}</td>
                        <td scope="row">{{$transfers->created_at}}</td>
                        <td>{{$transfers->name}}</td>
                                              
                        <td>{{ $transfers->facilityDeparture->name }}</td>
                        <td>{{ $transfers->facilityArrive->name }}</td>


                        <td><span class="badge {{$catalogs->transfer_status_color[$transfers->status]}}">{{$catalogs->transfer_status[$transfers->status]}}</span></td>
                        <td ></td>
                       
                        <td><a href="/transfer_detail/{{$transfers->transfer_id}}">Detalle</a></td>                
                      </tr>
                      @endforeach                      
                    </tbody>

                  </table>

                </div>

              </div>
            </div><!-- End Recent Sales -->


          <div class="col-12">
            <div class="card recent-sales overflow-auto">
              <div class="card-body">
                <h5 class="card-title">Niveles de Inventario</h5>
                <table class="table table-borderless datatable">
                  <thead>
                    <tr>
                      <th>Sucursal</th>
                      <th>Producto</th>
                      <th>Inventario</th>
                      <th>Punto de Reorden</th>
                      <th>Estado de Inventario</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($facilityInventories as $item)
                      <tr>
                        <td>{{ $item->facility->name }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->reorder }}</td>
                        <td>
                          @if ($item->qty >= $item->reorder)
                            <span class="badge {{ $catalogs->inventory_status_color['suficiente'] }}">
                            {{ $catalogs->inventory_status['suficiente'] }}
                            </span>
                          @else
                            <span class="badge {{ $catalogs->inventory_status_color['bajo'] }}">
                              {{ $catalogs->inventory_status['bajo'] }}
                            </span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>



      


      <div class="col-12">
        <div class="card recent-sales overflow-auto">
          <div class="card-body">
            <h5 class="card-title">Resumen de Balance por Producto y Sucursal</h5>
            <table class="table table-borderless datatable">
              <thead>
                <tr>
                  <th scope="col">Sucursal</th>
                  <th scope="col">Producto</th>
                  <th scope="col">Balance Actual</th>
                </tr>
              </thead>
              <tbody>
                  
                @foreach ($balances as $balance)
                  <tr>
                    <td>{{ $catalogs->facility_type[$balance->facility_id] }}</td>
                    <td>{{ $balance->product->name }}</td>
                    <td>{{ $balance->qty }}</td>
                  </tr>
                @endforeach
                  
              </tbody>
            </table>
          </div>
        </div>
      </div>


      <!-- Historial de Inventario -->
      <div class="col-12">
        <div class="card recent-sales overflow-auto">
          <div class="card-body">
            <h5 class="card-title">Historial de Inventario </h5>
            <table class="table table-borderless datatable" id="datatableRows">
              <thead>
                <tr>
                  <th scope="col">Fecha </th>
                  <th scope="col">Sucursal </th>
                  <th scope="col">Producto </th>
                  <th scope="col">Cantidad Anterior </th>
                  <th scope="col">Cantidad Después </th>
                  <th scope="col">Tipo de Movimiento </th>
                  <th scope="col">Usuario </th>
                  <th scope="col">Transferencia</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($inventoryLogs as $log)
                <tr>
                  <td>{{ $log->created_at }}</td>
                  <td>{{ $catalogs->facility_type[$log->facility_id] }}</td>
                  <td>{{ $log->product->name }}</td>
                  <td>{{ $log->qty_before }}</td>
                  <td>{{ $log->qty_after }}</td>
                  <td>{{ $log->movement_type }}</td>
                  <td>{{ $log->user->name }}</td>
                  <td>{{ $log->transfer_id }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
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