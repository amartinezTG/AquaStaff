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
          <li class="breadcrumb-item active">Transferencias </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">


            <!-- Reports -->
            <div class="col-12">
              <div class="card">
                <div class="filter">
                </div>
                <div class="col-12">
                  <div class="card-body">

                    <form class="row g-3">
                      
                      <div class="col-md-6">
                        <label class="col-sm-4 col-form-label">Fecha Inicio</label>
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



            <!-- Transferencias -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Transferencias </h5>
                  <p><a href="/crear_transferencia">Crear Transferencia</a></p>

                  <table class="table table-borderless ">
                    <thead>
                      <tr>
                        
                        <th scope="col">Fecha de transferencia</th>
                        <th scope="col">Factura</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad transferida</th>
                        
                        <th scope="col">Origen</th>
                        <th scope="col">Destino</th>
                        <th scope="col">Estado de Transferencia</th>
                        <th scope="col">Fecha de Recepción</th>
                        <th scope="col">Comentarios</th>
                        <th scope="col"></th>

                        
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td scope="row">01/06/2024</td>
                        <td>212</td>
                        <td>Armoll All</td>
                        <td>100</td>
                        
                        <td>CEDIS</td>
                        <td>Misiones</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                        <td >01/06/2024</td>
                        <td >Recibido sin incidencias</td>
                        <td><a href="#">Detalle</a></td>
                
                      </tr>

                      <tr>
                        <td scope="row">01/06/2024</td>
                        <td>212</td>
                        <td>Armoll All</td>
                        <td>100</td>
                        
                        <td>CEDIS</td>
                        <td>Misiones</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                        <td >01/06/2024</td>
                        <td >Recibido sin incidencias</td>
                        <td><a href="#">Detalle</a></td>
                
                      </tr>
                      <tr>
                        <td scope="row">01/06/2024</td>
                        <td>212</td>
                        <td>Armoll All</td>
                        <td>100</td>
                        
                        <td>CEDIS</td>
                        <td>Misiones</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                        <td >01/06/2024</td>
                        <td >Recibido sin incidencias</td>
                        <td><a href="#">Detalle</a></td>
                
                      </tr>
                      <tr>
                        <td scope="row">01/06/2024</td>
                        <td>212</td>
                        <td>Armoll All</td>
                        <td>100</td>
                        
                        <td>CEDIS</td>
                        <td>Misiones</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                        <td >01/06/2024</td>
                        <td >Recibido sin incidencias</td>
                        <td><a href="#">Detalle</a></td>
                
                      </tr>
                      <tr>
                        <td scope="row">01/06/2024</td>
                        <td>212</td>
                        <td>Armoll All</td>
                        <td>100</td>
                        
                        <td>CEDIS</td>
                        <td>Misiones</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                        <td >01/06/2024</td>
                        <td >Recibido sin incidencias</td>
                        <td><a href="#">Detalle</a></td>
                
                      </tr>
                      
                    </tbody>
                  </table>

                </div>

              </div>
            </div><!-- End Recent Sales -->


            <!-- Top Selling -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Niveles de Inventario </h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th scope="col">Fecha </th>
                        <th scope="col">Producto  </th>
                        <th scope="col">Cantidad Inicial</th>
                        <th scope="col">Cantidad Recibida</th>
                        <th scope="col">Cantidad Utilizada</th>
                        <th scope="col">Cantidad Final</th>
                        <th scope="col">Punto de Reorden</th>
                        <th scope="col">Estado de Inventario</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Detergente</td>
                        <td>100</td>
                        <td>50</td>
                        <td>80</td>
                        <td>70</td>
                        <td>50</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Cera para Autos</td>
                        <td>60</td>
                        <td>30</td>
                        <td>50</td>
                        <td>40</td>
                        <td>300</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Toallas de Microfibra</td>
                        <td>200</td>
                        <td>100</td>
                        <td>150</td>
                        <td>150</td>
                        <td>100</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Limpiador de Vidrios</td>
                        <td>80</td>
                        <td>40</td>
                        <td>60</td>
                        <td>60</td>
                        <td>40</td>
                        <td><span class="badge bg-success">Suficiente</span></td>
                      </tr>
                      <tr>
                        <th scope="row">01/06/2024</th>
                        <td>Shampoo para Autos</td>
                        <td>50</td>
                        <td>20</td>
                        <td>40</td>
                        <td>30</td>
                        <td>20</td>
                        <td><span class="badge bg-warning">Reordenar</span></td>
                      </tr>
                    </tbody>
                  </table>

                </div>

              </div>
            </div><!-- End Recent Sales -->



            <!-- Top Selling -->
            <div class="col-6">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Crear Transferencia <span></span></h5>

                  <form>
                    <div class="row mb-12">
                      <label for="inputText" class="col-sm-5 col-form-label">Número de factura</label>
                      <div class="col-sm-7">
                        <input type="text" class="form-control">
                      </div>
                    </div>
                    

                    <div class="row mb-12">
                      <label for="inputEmail" class="col-sm-5 col-form-label">Número de piezas</label>
                      <div class="col-sm-7">
                        <input type="text" class="form-control">
                      </div>
                    </div>

                    <div class="row mb-12">
                      <label for="inputEmail" class="col-sm-5 col-form-label">Origen</label>
                      <div class="col-sm-7">
                        <select class="form-select" aria-label="Default select example">
                          <option selected>---</option>
                          <option value="1">CEDIS</option>
                        </select>
                      </div>
                    </div>

                    <div class="row mb-12">
                      <label for="inputEmail" class="col-sm-5 col-form-label">Entrega a sucursal</label>
                      <div class="col-sm-7">
                        <select class="form-select" aria-label="Default select example">
                          <option selected>---</option>
                          <option value="1">Rio Grande</option>
                          <option value="2">Misiones</option>
                          <option value="3">Torres</option>
                        </select>
                      </div>
                    </div>


                    <div class="row mb-12">
                      <label for="inputEmail" class="col-sm-5 col-form-label">Productos</label>
                      <div class="col-sm-7">
                        <input type="email" class="form-control">
                      </div>
                    </div>

                    

                    <div class="row mb-12">

                      <table class="table table-borderless ">
                    <thead>
                      <tr>
                      
                        <th scope="col">Producto  </th>
                        <th scope="col">Codigo  </th>
                        <th scope="col">Cantidad</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        
                        <td>Jabón CONS </td>
                        <td>50241</td>
                        <td class="col-sm-3"><input type="text" class="  form-control"></td>
                      </tr>
                      <tr>
                        
                        <td>CERA AQUAWASH  </td>
                        <td>2123</td>
                        <td><input type="text" class="form-control"></td>
                      </tr>
                      <tr>
                        
                        <td>ARMOR ALL </td>
                        <td>123123</td>
                        <td><input type="text" class="form-control"></td>
                      </tr>
                    </tbody>
                  </table>
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
<script type="text/javascript">
  
  $(document).ready(function(){
    $('#colorselector').on('change', function() {
      $("#reporte_diario").hide();
      $("#reporte_diario2").hide();
      $("#monedero").hide();
      $("#monedero2").hide();
      $("#operaciones").hide();
      $("#inventario").hide();
      
      if ( this.value == 'reporte_diario'){
        $("#reporte_diario").show();
        $("#reporte_diario2").show();
      }

      if ( this.value == 'monedero'){
        $("#monedero").show();
        $("#monedero2").show();
      }

      if ( this.value == 'operaciones'){
        $("#operaciones").show();
      }

      if ( this.value == 'inventario'){
        $("#inventario").show();
      }
    });
  });

</script>