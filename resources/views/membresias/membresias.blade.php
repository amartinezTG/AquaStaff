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
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
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
                    <h6>{{$totalNew}}</h6>
                    <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>

                  </div>
                </div>
              </div>

            </div>
          </div><!-- End Sales Card -->

          <!-- Valor total de ventas -->
         <!--  <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Valor total de las membresías</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cash-coin"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >${{$totalValue}}</h6>  
                    <span class="text-success small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1"></span>   
                  </div>
                </div>
                
              </div>

            </div>
          </div>-><!-- End Sales Card -->


          <!-- Valor total de ventas -->
          <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">

              <div class="card-body text-center">
                <h5 class="card-title">Membresías de clientes recurrentes</h5>
                  
                <div class="d-flex justify-content-center" >
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cash-coin"></i>
                  </div>
                  <div class="ps-3">
                    <h6 >{{$totalRecurrent}}</h6>  
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

            <!-- Right side columns -->
        <div class="col-lg-12">

          <!-- Membresias -->
          <div class="card">
            <div class="filter">
              
            </div>

            <div class="card-body pb-0">
              <h5 class="card-title">Membresias <span></span></h5>


              <table id="datatable" class=" table-responsive" >
  <thead>
    <tr>
      <th>Fecha</th>
      <th>Cliente</th>
      <th>Paquete</th>
      <th>Sucursal</th>
    </tr>
  </thead>
  <tbody>
    @foreach($list as $row)
      <tr>
        <td>{{ $row->Fecha }}</td>
        <td>
          {{ $row->ClienteNombre }}<br>
          <small>{{ $row->ClienteID }}</small>
        </td>
        <td><strong>
          @php
              $paquete = trim($row->Paquete);
              $tipo    = $paquete ?? null;
          @endphp
         
          @if($tipo !== null && trim($tipo) !== '')
              {{ $catalogs->membership_type[$tipo] }} 
          @else
             {{$row->Paquete}} 
          @endif

        </strong></td>
        <td>{{ $row->Sucursal }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

              <!--ß ß <p><a href="/exportar-membresias-ventas/{{$startDate}}/{{$endDate}}">Descargar Reporte</a></p>
                <table id="datatable" class=" table-responsive" >
                  <thead>
                    <tr>
                      <th scope="col">Fecha</th>
                      <th scope="col">Cliente</th>
                      <th scope="col">Paquete</th>
                      <th scope="col">Sucursal</th>
                    </tr>
                  </thead>
                  @foreach ($memberships as $membership)
                    <tbody>
                      <tr>
                        <td>{{$membership->start_date}}<br>
                      </td>
                        <td>@if ($membership->client)
                        {{ $membership->client->first_name }} {{ $membership->client->last_name }}
                        <br>
                        <small>{{ $membership->client_id }} </small>
                    @else
                        Cliente no encontrado
                    @endif</a></td>
                        <td><strong>{{$catalogs->membership_type[$membership->membership_id]}}</strong></td>
                        <td>{{$membership->facility}}</td>                                                       
                      </tr>
                          
                    </tbody>
                  @endforeach
                </table>
              -->

              </div>
            </div><!-- End Membresias -->
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