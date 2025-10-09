@include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="#" class="logo d-flex align-items-center">
        <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
        <span class="d-none d-lg-block" style="color:#4CB8B8;"></span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

@include('layout.nav-header')

  </header><!-- End Header -->

  <?php //include('assets/includes/nav-bar.inc.php');?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Facturación</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/facturacion">Dashboard</a></li>
          <li class="breadcrumb-item active">Facturación</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">

        <div class="col-9">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

                <form class="row g-3">                 

                  <div class="col-md-6">
                    <label class="col-sm-6 col-form-label">Fecha Inicio</label>
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
        </div>
      </div>

      <!-- Left side FACTURAS -->
      <div class="row" id="clientes">

        <div class="col-12">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- -->
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
                    <h5 class="card-title">FACTURAS GENERADAS <span></span></h5>
                    <table id="datatable" class="table table-borderless datatable">
                    <thead>
                <tr>
                <th>FOLIO</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Sucursal</th>
                <th>Archivos</th>
                </tr>
                </thead>
                  <tbody>
                    
                    @foreach ($facturas as $factura)
                      <tr>
                        <td>{{ $factura->PaymentFolio }}</td>
                        <td>{{ $factura->TransationDate }}</td>
                        <td>${{ number_format($factura->Total, 2) }}</td>
                        <td>
                          {{ $factura->facility }}
                          </td>
                          <td> XML PDF</td>
                      </tr>
                    @endforeach
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


   
  });