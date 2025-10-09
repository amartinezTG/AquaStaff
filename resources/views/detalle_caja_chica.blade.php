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
          <li class="breadcrumb-item active"><a href="/caja_chica">Caja Chica</a></li>
          <li class="breadcrumb-item active">Detalle</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">



      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Corte Caja Chica</h5>
              @if(session('success'))
                <div class="alert alert-success">
                  {{ session('success') }}
                </div>
              @endif
              
                <div class="row">

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Fecha:</strong></label>
                  <div class="input-group">
                       {{$cortecaja->fecha_corte}}
                  </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Sucursal:</strong></label>
                
                
                  <div class="input-group">{{$cortecaja->sucursal}}
                  </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Efectivo:</strong> </label>
                  <div class="input-group">${{number_format($cortecaja->efectivo,2)}}
                  </div>
                </div>                

                <div class="col-3">
                  <label for="email" class="form-label"><strong></strong></label>
                
                  <div class="input-group">      </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Motivo 1:</strong></label>
                  <div class="input-group">{{$cortecaja->motivo_1}} </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Monto 1:</strong></label>
                  <div class="input-group">${{number_format($cortecaja->monto_1,2)}} </div>
                </div>

                <div class="col-12"></div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Motivo 2:</strong></label>
                  <div class="input-group">{{$cortecaja->motivo_2}} </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Monto 2:</strong></label>
                  <div class="input-group">${{number_format($cortecaja->monto_2,2)}} </div>
                </div>

                <div class="col-12"></div>
                <div class="col-3">
                  <label for="email" class="form-label"><strong>Motivo 3:</strong></label>
                  <div class="input-group">{{$cortecaja->motivo_3}} </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Motivo 3:</strong></label>
                  <div class="input-group">${{number_format($cortecaja->monto_3,2)}} </div>
                </div>

                <div class="col-12"></div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              

            </div>
          </div>
        </div>
      </div>


      <div class="row">
        <div class="col-lg-12">
          
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