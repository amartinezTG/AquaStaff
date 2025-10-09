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
      <h1>Cajeros</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">Lavados por Membresia</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

                <form class="row g-3" method="GET" action="{{ route('cajero.membership.packages') }}">                
                  @csrf
                  <div class="col-md-5">
                    <label class="col-sm-6 col-form-label">Fecha Inicio</label>
                    <input type="datetime-local" class="form-control" name="startDate" value="{{ $startDate }}">
                  </div>

                  <div class="col-md-5">
                    <label class="col-sm-4 col-form-label">Fecha Final</label>
                    <input type="datetime-local" class="form-control" name="endDate" value="{{ $endDate }}">
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
                    <label class="col-md-12 col-form-label text-center"><strong>LAVADO POR MEMBRESÍAS </strong> </label> 
                  </div>  
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="row">
        @foreach ($packageData as $package)
                <div class="col-lg-3 col-md-3">
                    <div class="card info-card sales-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                {{ $package['package_id'] ? $catalogs->package_type[$package['package_id']] : 'Lavados por Membresía' }}
                            </h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-droplet-half"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>${{ number_format($package['total_sales'], 2) }}</h6>
                                    <span class="text-success small pt-1 fw-bold">{{ number_format($package['total_purchases']) }} transacciones</span>
                                    <span class="text-muted small pt-2 ps-1"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
      </div>

      <div class="row">
        <div class="col-lg-12 col-md-12">
          <div class="card info-card sales-card">
            <div class="card-body text-center">
              <h5 class="card-title ">Transacciones de clientes</h5>
              <div class="table-responsive">
              <table class="table table-bordered" id="datatableRows">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Nombre del Cliente</th>
                      
                        <th>Paquete</th>
                        <th>Folio</th>
                    </tr>
                </thead>
                <tbody>

                  @foreach ($transactions as $transaction)
                      <tr>
                          <td>{{ $transaction->TransationDate }}</td>
                          <td>${{ number_format($transaction->Total, 2) }}</td>
                          <td>{{ $transaction->clients ? $transaction->clients->name : 'N/A' }}</td>
                          <td>{{ $transaction->package ? $catalogs->package_type[$transaction->package] : 'N/A' }}</td>
                          <td>{{ $transaction->PaymentFolio }}</td>
                         
                      </tr>
                  @endforeach
              </tbody>
              </table>
              </div>

            </div>
          </div>
        </div>
      </div>


    </section>
    
    
    <br>


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
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-asdmin-bootstrap-admin-html-template/ 
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>-->
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

@include('layout.footer')