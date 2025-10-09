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
      <h1>Caja Chica</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">Fondo de Caja</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Fondo de Caja</h5>
               <p><a href="/caja_chica_sucursal">Agregar fondo de caja</a></p>

              @if(session('success'))
                <div class="alert alert-success">
                  {{ session('success') }}
                </div>
              @endif

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
                  @foreach ($FondoDeCaja as $fondo)
                  <tr>
                    <th>{{$fondo->id}} </th>
                    <td>{{$fondo->sucursal}}</td>
                    <td>{{$fondo->fecha_corte}}</td>
                    
                    <td>${{number_format($fondo->efectivo,2)}}</td>
                    <td>${{number_format(($fondo->monto_1+$fondo->monto_2+$fondo->monto_3),2)}}</td>
                    
                    <td><a href="/detalle_caja_chica/{{$fondo->id}}">Detalle</a></td>
                  </tr>
                  @endforeach
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