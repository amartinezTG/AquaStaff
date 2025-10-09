@include('layout.shared')

<style type="text/css">
  #your_div_id{
    display: none;
  }
</style>
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

 
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Integración COMPAQ</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active"><a href="/compaq">Interación Compaq</a></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <!-- Left side FACTURAS -->
      <div class="row" id="clientes">

        <div class="col-12">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- -->
              <div class="col-8">
                <div class="card top-selling">
                  <form class="row g-3" method="POST" action="{{ route('compaq') }}">           
                  @csrf
                  <div class="card-body pb-0">
                    <h5 class="card-title">INTEGRACION COMPAQ  {{$CompaqDetalle->name}} - Creado: {{$CompaqDetalle->updated_at}}<span> <a href="{{ route('download.txt',$CompaqDetalle->name) }}">Descargar {{$CompaqDetalle->name}}</a> </span></h5>
                    <table id="datatable3" class="table table-striped table-bordered datatable3">
                      <thead>
                        <tr>                          
                          <th>Folio</th>                          
                          <th>Fecha</th>
                          <th>Total</th>
                          <th>Sucursal</th>
                          <th>Ticket Fiscal</th>
                          <th>Factura Fiscal</th>
                        </tr>
                      </thead>
                      <tbody>
                      <!-- Paquetes  -->      
                              
                        @if ($CompaqIntegration)
                          @foreach ($CompaqIntegration->localTransactions as $invoices)
                            <tr>
                              <td>{{$invoices->_id}}</td>
                              <td>{{$invoices->TransationDate}}</td>
                              <td>${{number_format($invoices->Total,2)}}</td>
                              <td>{{$invoices->facility}}</td>
                              <td>{{$invoices->PaymentFolio}}</td>
                              <td>{{$invoices->fiscal_invoice}}</td>
                            </tr>
                          @endforeach  
                        @else
                          <tr>
                              <td><p>Compaq Integration not found.</p></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                            </tr>
                        @endif
                      </tbody>
                    </table>

                  </div>
                </form>
                </div><!-- End Promociones y descuentos -->

              </div>

              <div class="col-4" >

                <div class="card top-selling" id="your_div_id">
                  <div class="card-body pb-0">                    
                  </div>
                </div>

                <div class="card top-selling">
                  <div class="card-body pb-0">
                  </div>
                </div>

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

<script type="text/javascript">
  


</script>