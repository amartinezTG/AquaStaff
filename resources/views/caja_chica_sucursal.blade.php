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
          <li class="breadcrumb-item active">Corte de Caja</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      

      <form class="row g-3" method="POST" action="{{ route('corte_caja_sucursal') }}" name="corte_caja_sucursal">                
        @csrf
     

      <div class="row">

        <div class="col-lg-8" id="fondo_de_caja">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Fondo de caja</h5>
              <div class="row">
              <div class="col-12">
                
                @error('corte_caja_sucursal')
                   <br><span class="text-danger">{{ $message }}</span>
                @enderror

                <div class="input-group">
                    <label for="inputText" class="col-sm-2 col-form-label">Fecha: </label>
                    <div class="col-sm-5">
                      <input type="date" class="form-control" tabindex="1" name="fecha_corte" id="fecha_corte" value="" max="<?php echo date('Y-m-d'); ?>" tabindex="1" required>
                    </div>  
                    
                </div>
                <br>
                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-2 col-form-label">Efectivo: </label>
                  <div class="col-sm-5">
                    <input type="text" name="fondo_de_cada_efectivo" value="" id="fondo_de_cada_efectivo" tabindex="2" class="form-control" required>
                  </div>  
                </div>

                <br>
                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-2 col-form-label">Sucusal: </label>
                  <div class="col-sm-5">
                    <select class="form-select" name="sucursal" id="sucursal" tabindex="3" required>
                      <option>--</option>
                      <option value="MISIONES">MISIONES</option>
                    </select>
                  </div>  
                </div>

                <br>
                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-5 col-form-label"><strong>Concepto de Egresos: </strong></label>
                  <div class="col-sm-3">
                    
                  </div>  
                </div>
                <br>

                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-2 col-form-label">Motivo:</label>
                  <div class="col-sm-6">
                    <input type="text" name="concepto_de_egresos_motivo_1" value="" tabindex="4" id="concepto_de_egresos_motivo_1" class="form-control">
                  </div>  

                  <label for="inputText" class="col-sm-2 col-form-label"> &nbsp;&nbsp;Monto:</label>
                  <div class="col-sm-2">
                    <input type="text" name="concepto_de_egresos_monto_1" value="" tabindex="5" id="concepto_de_egresos_monto_1" class="form-control">
                  </div>  
                </div>

                <br>

                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-2 col-form-label">Motivo:</label>
                  <div class="col-sm-6">
                    <input type="text" name="concepto_de_egresos_motivo_2" value="" tabindex="6" id="concepto_de_egresos_motivo_2" class="form-control">
                  </div>  

                  <label for="inputText" class="col-sm-2 col-form-label"> &nbsp;&nbsp;Monto:</label>
                  <div class="col-sm-2">
                    <input type="text" name="concepto_de_egresos_monto_2" value="" tabindex="7" id="concepto_de_egresos_monto_2" class="form-control">
                  </div>  
                </div>

                <br>

                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-2 col-form-label">Motivo:</label>
                  <div class="col-sm-6">
                    <input type="text" name="concepto_de_egresos_motivo_3" value="" tabindex="8" id="concepto_de_egresos_motivo_3" class="form-control">
                  </div>  

                  <label for="inputText" class="col-sm-2 col-form-label"> &nbsp;&nbsp;Monto:</label>
                  <div class="col-sm-2">
                    <input type="text" name="concepto_de_egresos_monto_3" value="" tabindex="9" id="concepto_de_egresos_monto_3" class="form-control">
                  </div>  
                </div>

                <br>
                
                <div class="input-group">
                       
                  <label for="inputText" class="col-sm-2 col-form-label">Motivo:</label>
                  <div class="col-sm-6">
                    <input type="text" name="concepto_de_egresos_motivo_4" value="" tabindex="10" id="concepto_de_egresos_motivo_4" class="form-control">
                  </div>  

                  <label for="inputText" class="col-sm-2 col-form-label"> &nbsp;&nbsp;Monto:</label>
                  <div class="col-sm-2">
                    <input type="text" name="concepto_de_egresos_monto_4" value="" tabindex="11" id="concepto_de_egresos_monto_4" class="form-control">
                  </div>  
                </div>

              </div>
            </div>

            </div>
          </div>
        </div>

      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body">

              <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-4"><br>
                  <button class="btn btn-warning w-100 submitBtn" tabindex="15" type="submit" id=" submit_btn">Guardar</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
      <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    </form>

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