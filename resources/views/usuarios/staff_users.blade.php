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
      <h1>Reporte</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Portada</a></li>
          <li class="breadcrumb-item active">Usuarios </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Users -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Usuarios </h5>
                  @if (auth()->user()->role == 1) 
                  <p><a href="{{ route('usuario') }}">Crear Usuario</a></p>

                  <table class="table table-borderless ">
                    <thead>
                      <tr>                        
                        <th scope="col">Nombre</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Activo</th>
                        <th scope="col"></th>
                      </tr>
                    </thead>

                    <tbody>
                      @foreach ($staff_users as $users)
                      <tr>
                        <td scope="row">{{$users->name}}</td>
                        <td scope="row">{{$catalogs->role_type[$users->role]}}</td>
                        <td>
                           @if($users->active == 1) Activo @else Inactivo @endif</td>
                                              
                        <td><a href="/editar_usuario/{{$users->id}}"><i class="bi bi-pencil-square"></i></a>
                          </td>
                                        
                      </tr>
                      @endforeach                      
                    </tbody>

                  </table>
                  @endif

                </div>

              </div>
            </div><!-- End Recent Sales -->

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
  

</script>