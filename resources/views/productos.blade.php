@include('layout.shared')

<body class="toggle-sidebar">

    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center">
                <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>@include('layout.nav-header')

    </header><main id="main" class="main">

        <div class="pagetitle">
            <h1>Reporte</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Productos </li>
                </ol>
            </nav>
        </div><section class="section dashboard">
            <div class="row">

                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-12">
                            <div class="card recent-sales overflow-auto">

                                <div class="card-body">
                                    <h5 class="card-title">Productos </h5>
                                    <p><a href="/agregar_producto">Agregar producto</a></p>

                                    <table class="table table-borderless datatable">
                                        <thead>
                                            <tr>
                                                <th scope="col">Nombre </th>
                                                <th scope="col">SKU</th>
                                                <th scope="col">Inventario</th>
                                                <th scope="col">Unidad de Medida</th>
                                                
                                                @foreach ($facilities as $facility)
                                                    <th scope="col">Punto de Reorden {{ $facility->name }}</th>
                                                @endforeach
                                                <th scope="col">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($productos as $product)
                                                <tr>
                                                    <th scope="row">{{ $product->name }}</th>
                                                    <td>{{ $product->sku }}</td>
                                                    <td>{{ $product->inventory }}</td>
                                                    <td>{{ $catalogs->unit_measurement[$product->unit_measurement] }}</td>
                                                    
                                                    @foreach ($facilities as $facility)
                                                        <td>
                                                            @php
                                                                $facilityInventory = $facilityInventories->where('product_id', $product->product_id)->where('facility_id', $facility->facility_id)->first();
                                                                echo $facilityInventory ? $facilityInventory->reorder : '-';
                                                            @endphp
                                                        </td>
                                                    @endforeach
                                                    <td>
                                                        <a href="/editar_producto/{{ $product->product_id }}"><i class="bi bi-pencil-square"></i></a>
                                                        <a href="/eliminar_producto/{{ $product->product_id }}" onclick="alert('¿Estás seguro de eliminar este registro?');"><i class="bi bi-trash"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>

                                </div>

                            </div>
                        </div></section>

    </main><footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span></span></strong>. All Rights Reserved
        </div>
        <div class="credits">
            </div>
    </footer><a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    @include('layout.footer')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

</body>