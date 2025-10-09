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
          <li class="breadcrumb-item"><a href="/usuarios">Usuarios</a></li>
          <li class="breadcrumb-item active">Crear Usuario </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Top Selling -->
            <div class="col-8">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Crear Usuario <span></span></h5>
                    @if ($errors->any())
                      <div class="alert alert-danger">
                        <ul>
                          @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                          @endforeach
                        </ul>
                      </div>
                      @endif

                      @if(session('error'))
                        <div class="alert alert-danger">
                          {{ session('error') }}
                        </div>
                      @endif
                      
                  <form class="row g-3" method="POST" action="{{ route('usuario') }}">
                    @csrf


                    @if (isset($user->id))
                      <input type="hidden" name="id" value="{{ $user->id }}">
                    @else                    
                    @endif

                    <div class="row">
                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label">Nombre</label>
                        <div class="col-sm-12">
                          <input type="text" name="name" class="form-control" required tabindex="1" value="@if (isset($user->name)){{$user->name}}@endif" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label">Usuario</label>
                        <div class="col-sm-12">
                          <input type="text" class="form-control" name="email" tabindex="2" value="@if (isset($user->email)){{$user->email}}@endif" >
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label">Contraseña</label>
                        <div class="col-sm-12">
                          <input type="password" class="form-control" name="password" tabindex="3"    @if (isset($user->id)) @else required @endif >
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label">Confirmar Contraseña</label>
                        <div class="col-sm-12">
                          <input type="password" class="form-control" name="password_confirmation" tabindex="4" @if (isset($user->id)) @else required @endif>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label">Rol</label>
                        <div class="col-sm-12">
                          <select class="form-select" name="role" aria-label="" tabindex="5" required>
                            <option selected>---</option>
                            @foreach ($catalogs->role_type as $key => $rol)
                              <option value="{{$key}}"  @if (isset($user->role)==$key) selected @else  @endif >{{$rol}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>   

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label">Status</label>
                        <div class="col-sm-12">
                          <select class="form-select" name="active" aria-label="" tabindex="6" required>
                            <option selected>---</option>
                            <option value="1"  @if (isset($user->active)==1) selected @else  @endif>Activo</option>
                            <option value="0" @if (isset($user->active)==0) selected @else  @endif>Inactivo</option>
                        
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6"></div>
                     
                    </div>


                    <div class="row mb-12">
                      
                      <div class="col-sm-10"><br>                        
                        <button type="submit" class="btn btn-primary">Guardar</button>
                      </div>
                    </div>

                  </form>

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
