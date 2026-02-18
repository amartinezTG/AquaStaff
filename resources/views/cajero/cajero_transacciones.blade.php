{{-- resources/views/indicadores/membresias.blade.php --}}

@include('layout.shared')
@include('layout.includes')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
<style>
    /* Evitar que .dashboard .filter afecte nuestra fila de filtros de columna */
    #CajerosTable thead tr.col-filters {
        position: static !important;
    }
    #CajerosTable thead tr.col-filters th {
        position: static !important;
    }
    .dashboard .filter {
  position: relative !important;
  right: 0px;
  top: 15px;
}
 #CajerosTable thead  th {
        background-color: #6f42c1 !important;
        color: #ffffff !important;
    }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center">
                <img src="assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->
        {{-- sidebar --}}
        @include('layout.nav-header')
    </header>

    <main id="main" class="main">
       
        <div class="pagetitle">
            <h1>Transacciones de Cajero</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item">Cajero</li>
                    <li class="breadcrumb-item active">Transacciones</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <!-- Filtros de fecha -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #6f42c1;">
                        <div class="card-body">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                                        value="{{ now()->toDateString() }}" required>
                                </div>

                                <div class="col-md-5">
                                    <label for="fecha_final" class="form-label">Fecha Final</label>
                                    <input type="date" class="form-control" name="fecha_final" id="fecha_final"
                                        value="{{ now()->endOfMonth()->toDateString() }}" required>
                                </div>

                                <div class="col-md-2">
                                    <button class="btn btn-warning w-100 submitBtn" onclick="CajerosDaTable()" type="button">
                                        <i class="ti ti-search me-1"></i>Consultar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Tabla de indicadores de membresías -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #6f42c1;">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="CajerosTable" class="table table-striped table-hover table-bordered  w-100 dataTable no-footer">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Id Cajero</th>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Cliente</th>
                                            <th>Paquete</th>
                                            <th>Cajero </th>
                                            <th>Metodo</th>
                                            <th>Tipo de transacción</th>
                                            <th>Total</th>
                                            <th>Cadena Facturación</th>
                                            <th>Fiscal Invoice</th>
                                            <th>RFC</th>
                                            <th>Nombre de la Empresa</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de Gráficas de Membresías -->
           
        </section>

        <!-- Incluir el script de indicadores de membresías -->
        <script src="{{ asset('assets/js/cajero.js') }}"></script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                CajerosDaTable();
            });
        </script>

    </main>

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span></span></span></strong>. All Rights Reserved
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    @include('layout.footer')

</body>