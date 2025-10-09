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

            <!-- Top Selling -->
            <div class="col-8">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Crear Transferencia <span></span></h5>

                  <form class="row g-3" method="POST" action="{{ route('submit_transfer') }}">
                    @csrf
                    <div class="row">
                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label">Nombre</label>
                        <div class="col-sm-12">
                          <input type="text" name="name" class="form-control" required tabindex="1">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label">Número de factura</label>
                        <div class="col-sm-12">
                          <input type="text" class="form-control" name="invoice" tabindex="2" >
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label">Origen</label>
                        <div class="col-sm-12">
                          <select class="form-select" name="facility_departure" aria-label="" tabindex="3" required>
                            <option selected>---</option>
                            @foreach ($catalogs->facility_type as $key => $name)
                              <option value="{{$key}}">{{$name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>                    

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label">Entrega a sucursal</label>
                        <div class="col-sm-12">
                          <select class="form-select" name="facility_arrive" aria-label="" tabindex="4" required>
                            <option selected>---</option>
                            @foreach ($catalogs->facility_type as $key => $name)
                              <option value="{{$key}}">{{$name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label">Status</label>
                        <div class="col-sm-12">
                          <select class="form-select" name="status" aria-label="" tabindex="5" required>
                            <option selected>---</option>
                            @foreach ($catalogs->transfer_status as $key => $name)
                              <option value="{{$key}}">{{$name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6"></div>
                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label">Comentarios</label>
                        <textarea id="comments" name="comments" rows="4" cols="50" class="form-control"></textarea>
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
                          @foreach ($productos as $product)
                          <tr>                        
                            <td>{{$product->name}}</td>
                            <td>{{$product->sku}}</td>
                            <td class="col-sm-3"><input type="number" id="product_qty[{{$product->product_id}}]" name="product_qty[{{$product->product_id}}]" min="1" max="1000" class="form-control"></td>
                          </tr>
                          @endforeach
                          
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