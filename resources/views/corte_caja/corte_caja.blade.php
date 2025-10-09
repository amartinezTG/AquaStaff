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
          <li class="breadcrumb-item active">Corte de Caja</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Cortes de Caja</h5>
              <p><a href="/corte_caja_sucursal">Agregar corte de caja</a></p>
              <!-- Table with stripped rows -->

              @if(session('success'))
                <div class="alert alert-success">
                  {{ session('success') }}
                </div>
              @endif

              @if(session('error'))
                <div class="alert alert-danger">
                  {{ session('error') }}
                </div>
              @endif
              <table id="datatable" class=" table-responsive" >
                <thead>
                  <tr>                    
                    <th>Corte ID</th>
                    <th>Sucursal</th>
                    <th>Fecha Corte</th>
                    <th>Total Ventas  </th>
                    <th>Total tickets</th>
                    <th>Dinero recibido</th>
                    <th>Quien Hizo Corte</th>
                    <th>Status</th>
                     <th></th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Ordenes  -->
                  @if($cortesDeCaja->isNotEmpty())
                  @foreach ($cortesDeCaja as $cortes)
                  <tr>
                    <th>{{$cortes->id}} </th>
                    <td>{{$cortes->sucursal}}</td>
                    <td>{{$cortes->fecha_corte}}</td>
                    
                    <td>${{number_format($cortes->total_ventas,2)}}</td>
                    <td>{{$cortes->total_tickets}}</td>
                    <td>${{number_format($cortes->dinero_recibido,2)}}</td>
                    <td>{{ $cortes->usuario->name ?? 'N/A' }}</td>
                    <td>{{$cortes->estado}}</td>
                    <td>
                      <a href="/detalle_corte/{{$cortes->id}}">Detalle</a>
                      @if (auth()->user()->role == 1 OR auth()->user()->role == 2)
                        <br>
                        <a href="{{ route('editar_corte', $cortes->id) }}">Editar</a>

                        <br>
                        <a href="{{ route('detalle_corte_export', $cortes->id) }}">Descargar</a>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>



      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Fondo de Caja</h5>
              <!-- Table with stripped rows -->
              <table id="datatable2" class=" table-responsive" >
                <thead>
                  <tr>
                    
                    <th> ID</th>
                    <th>Sucursal</th>
                    <th>Fecha Corte</th>
                    <th>Total Efectivo  </th>
                    <th>Gastos  </th>
                    <th>  </th>
                    
                  </tr>
                </thead>
                <tbody>
                  <!-- Ordenes  -->
                  @if($FondoDeCaja->isNotEmpty())
                  @foreach ($FondoDeCaja as $fondo)
                  <tr>
                    <th>{{$fondo->id}} </th>
                    <td>{{$fondo->sucursal}}</td>
                    <td>{{$fondo->fecha_corte}}</td>
                    
                    <td>${{number_format($fondo->efectivo,2)}}</td>
                    <td>${{number_format(($fondo->monto_1+$fondo->monto_2+$fondo->monto_3),2)}}</td>
                    
                    <td>
                     
                      <a href="/detalle_corte/{{$fondo->id}}">Detalle</a>
                   
                    </td>
                  </tr>
                  @endforeach
                  @endif
                </tbody>
              </table>
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



<script type="text/javascript">
  
  $(document).ready(function(){


  });
</script>