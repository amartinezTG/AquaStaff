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
      <h1>Factura Global</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active"><a href="/compaq">Compaq</a></li>
          <li class="breadcrumb-item active">Detalle</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

   

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">FACTURA GLOBAL</h5>
              @if(session('success'))
                <div class="alert alert-success">
                  {{ session('success') }}
                </div>
              @endif
              
                <div class="row">

                <div class="col-3">
                  <label for="email" class="form-label"><strong>Nombre:</strong> {{$GlobalInvoice->name}}</label>
                  <div class="input-group">
                     
                  </div>
                </div>

                <div class="col-3">
                  <label for="email" class="form-label"><strong><a href="{{ route('global_invoice_download.xml',$GlobalInvoice->name) }}">XML</a> - <a href="{{ route('global_invoice_download.pdf',$GlobalInvoice->name) }}">PDF</a></strong> </label>
                  <div class="input-group">
                     
                  </div>
                </div>


                <div class="col-3">
                  <label for="email" class="form-label"><strong>Fecha Creada:</strong> {{$GlobalInvoice->created_at}}</label>
                
                
                  <div class="input-group">
                  </div>
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
              


                  <form class="row g-3" method="POST" action="{{ route('compaq') }}">           
                  @csrf
                 
                    <h5 class="card-title">TICKETS  <span></span></h5>
                    <table id="datatable" class="table table-striped table-bordered datatable">
                      <thead>
                        <tr>
                          
                          <th>Folio</th>
                          
                          <th>Fecha</th>
                          <th>Total</th>
                          <th>Tipo de pago</th>
                          <th>Sucursal</th>
                          <th>Ticket Fiscal</th>
                          <th>Factura Fiscal</th>
                        </tr>
                      </thead>
                      <tbody>
                      <!-- Paquetes  -->
                     
                        @foreach ($LocalTransaction as $invoices)
                          <tr>
                            
                            <td>{{$invoices->_id}}</td>
                            <td>{{$invoices->TransationDate}}</td>
                            <td>${{ number_format($invoices->Total,2) }}</td>
                            <td>{{$catalogs->folio_payment_type[$invoices->PaymentType]}}</td>
                            <td>{{$invoices->facility}}</td>
                            <td>{{$invoices->PaymentFolio}}</td>
                            <td>{{$invoices->fiscal_invoice}}</td>
                          </tr>
                        @endforeach  
                      </tbody>
                    </table>

                </form>
        



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