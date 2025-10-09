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
      <h1>Reporte</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item"><a href="/productos">Productos</a> </li>
          <li class="breadcrumb-item active">Agregar Producto </li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">

                <div class="card">

                    <div class="card-body">
                        <h5 class="card-title">Producto</h5>
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form class="row g-3" method="POST" action="{{ route('producto_agregar') }}">
                            @csrf

                            @if (isset($productos->product_id))
                                <input type="hidden" name="product_id" value="{{ $productos->product_id }}">
                            @endif

                            <div class="row">

                                <div class="col-6">
                                    <label for="name" class="form-label">Nombre</label>

                                    @error('name')
                                        <br><span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="input-group">

                                        <input type="text" class="form-control" tabindex="1" name="name" id="name" value="{{ old('name', isset($productos->name) ? $productos->name : '') }}" required>

                                    </div>
                                </div>

                                <div class="col-6">
                                    <label for="sku" class="form-label">SKU</label>

                                    @error('sku')
                                        <br><span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="input-group">

                                        <input type="text" class="form-control" tabindex="2" name="sku" id="sku" value="{{ old('sku', isset($productos->sku) ? $productos->sku : '') }}" required>

                                    </div>
                                </div>

                                <div class="col-6">
                                    <label for="sku" class="form-label">Inventario en CEDIS</label>

                                    @error('inventory')
                                        <br><span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="input-group">

                                        <input type="text" class="form-control" tabindex="3" name="inventory" id="inventory" value="{{ old('inventory', isset($productos->inventory) ? $productos->inventory : '') }}" required>

                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="sku" class="form-label">Unidad de Medida</label>

                                    @error('unit_measurement')
                                        <br><span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="input-group">

                                        <select class="form-select" name="unit_measurement" tabindex="4" id="unit_measurement">
                                            <option value="">--</option>
                                            @foreach ($catalogs->unit_measurement as $key => $unit_measurement)
                                                <option value="{{ $key }}" @if(old('unit_measurement') == $key || (isset($productos) && $productos->unit_measurement == $key)) selected @endif>{{ $unit_measurement }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                @if(isset($facilities))
                                    @foreach($facilities as $facility)
                                        <div class="col-6">
                                            <label for="reorder_facility[{{$facility->facility_id}}]" class="form-label">Punto de Reorden {{ $facility->name }}</label>
                                            <input type="text" class="form-control" tabindex="5" name="reorder_facility[{{$facility->facility_id}}]" id="reorder_facility[{{$facility->facility_id}}]" value="{{ old('reorder_facility.' . $facility->facility_id, isset($facilityInventories) ? ($facilityInventories->where('facility_id',$facility->facility_id)->first() ? $facilityInventories->where('facility_id',$facility->facility_id)->first()->reorder : '') : '') }}">
                                        </div>
                                    @endforeach
                                @endif


                                <div class="col-6"></div>
                                <div class="col-12">


                                    <div class="col-3">
                                        <label for="sku" class="form-label"> </label>
                                        <div class="input-group">

                                            <button class="btn btn-warning w-100 submitBtn" tabindex="6" type="submit">Guardar</button>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">
  


</script>