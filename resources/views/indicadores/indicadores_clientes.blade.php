@include('layout.shared')
@include('layout.includes')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="/dashboard" class="logo d-flex align-items-center">
                <img src="assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        @include('layout.nav-header')
    </header>

    <main id="main" class="main">
        <style>
            #clientes_table { font-size: 0.80rem; }
 
            #clientes_table thead th {
                background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                color: #fff;
                font-weight: 600;
                font-size: 0.76rem;
                text-align: center;
                vertical-align: middle;
                white-space: nowrap;
                padding: 10px 8px;
                border: none;
            }
            #clientes_table thead tr.col-filters th {
                background: #e8f0fe !important;
                padding: 4px 4px !important;
                position: static !important;
            }
            #clientes_table thead tr.col-filters input {
                font-size: 0.72rem;
                padding: 3px 5px;
                border: 1px solid #b3c7f7;
                border-radius: 4px;
                width: 100%;
                color: #333;
                background: #fff;
            }

            #clientes_table tbody td {
                vertical-align: middle;
                padding: 8px;
                font-size: 0.78rem;
                border-right: 1px solid #e9ecef;
            }

            #clientes_table tbody tr:hover { background-color: #e8f0fe; }

            .badge-vigente   { background-color: #198754; color: #fff; }
            .badge-vencida   { background-color: #dc3545; color: #fff; }
            .badge-sin       { background-color: #6c757d; color: #fff; }

            .badge-express   { background-color: #ffc107; color: #212529; }
            .badge-basico    { background-color: #17a2b8; color: #fff; }
            .badge-ultra     { background-color: #28a745; color: #fff; }
            .badge-delux     { background-color: #dc3545; color: #fff; }
            .badge-na        { background-color: #adb5bd; color: #fff; }

            .dt-button {
                color: #fff !important;
                border-radius: 20px !important;
                font-weight: 600 !important;
                font-size: 0.82rem !important;
                padding: 7px 18px !important;
                border: none !important;
                transition: all .2s;
            }
            .buttons-excel { background: linear-gradient(135deg,#46b723,#3cb723) !important; }
            .buttons-pdf   { background: linear-gradient(135deg,#ff3b3b,#e53e3e) !important; }
            .buttons-copy  { background: linear-gradient(135deg,#ffb800,#f59e0b) !important; }

            /* Cards resumen */
            .stat-card { border-radius: 12px; border: none; transition: transform .2s; }
            .stat-card:hover { transform: translateY(-3px); }

            /* DataTable lavados dentro del modal */
            #tbl-lavados_wrapper .dt-buttons { margin-bottom: 6px; }
            #tbl-lavados_wrapper .dt-search { display: inline-flex; align-items: center; gap: 4px; }
            #tbl-lavados_wrapper .dt-search input { font-size: .78rem; padding: 3px 7px; border-radius: 5px; border: 1px solid #ced4da; }
            #tbl-lavados_wrapper .dt-paging { margin-top: 4px; }
            #tbl-lavados_wrapper .dt-info  { font-size: .75rem; color: #6c757d; }
        </style>

        <div class="pagetitle">
            <h1>Clientes</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item">Indicadores</li>
                    <li class="breadcrumb-item active">Clientes</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">

            <!-- Cards de resumen -->
            <div class="row mb-3" id="summaryCards" style="display:none;">
                <div class="col-6 col-md-3 mb-3">
                    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                        <div class="card-body py-3">
                            <h6 class="mb-1">Total Clientes</h6>
                            <h3 class="mb-0" id="statTotal">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#198754,#146c43);">
                        <div class="card-body py-3">
                            <h6 class="mb-1">Membresía Vigente</h6>
                            <h3 class="mb-0" id="statVigente">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#dc3545,#b02a37);">
                        <div class="card-body py-3">
                            <h6 class="mb-1">Membresía Vencida</h6>
                            <h3 class="mb-0" id="statVencida">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#6c757d,#495057);">
                        <div class="card-body py-3">
                            <h6 class="mb-1">Sin Membresía</h6>
                            <h3 class="mb-0" id="statSin">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtro rápido de estatus -->
            <div class="row mb-3" id="filterRow" style="display:none;">
                <div class="col-12">
                    <div class="card" style="border-left:5px solid #0d6efd;">
                        <div class="card-body py-2">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="fw-bold me-2">Filtrar:</span>
                                <button class="btn btn-sm btn-outline-secondary active" onclick="filtrarEstatus('todos')" id="btn-todos">Todos</button>
                                <button class="btn btn-sm btn-outline-success" onclick="filtrarEstatus('Vigente')" id="btn-vigente">Vigente</button>
                                <button class="btn btn-sm btn-outline-danger"  onclick="filtrarEstatus('Vencida')"  id="btn-vencida">Vencida</button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="filtrarEstatus('Sin membresía')" id="btn-sin">Sin membresía</button>
                                <span class="ms-3 fw-bold">Tipo:</span>
                                <button class="btn btn-sm" style="background:#ffc107;color:#212529;" onclick="filtrarTipo('Express')">Express</button>
                                <button class="btn btn-sm" style="background:#17a2b8;color:#fff;" onclick="filtrarTipo('Básico')">Básico</button>
                                <button class="btn btn-sm" style="background:#28a745;color:#fff;" onclick="filtrarTipo('Ultra')">Ultra</button>
                                <button class="btn btn-sm" style="background:#dc3545;color:#fff;" onclick="filtrarTipo('Delux')">Delux</button>
                                <button class="btn btn-sm btn-secondary" onclick="filtrarTipo('')">Limpiar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Tabla -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left:5px solid #0d6efd;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <h5 class="mb-0 fw-semibold">Listado de Clientes</h5>
                                    <small class="text-muted">Clientes registrados y sincronizados desde el servidor. La tabla se actualiza automáticamente cada 5 minutos con nuevos registros y dos veces al día con cambios en los datos.</small>
                                </div>
                            </div>
                            <div class="table-responsive">

                                <table id="clientes_table" class="table table-bordered table-hover nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Tag</th>
                                            <th>Placa</th>
                                            <th>Auto</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Membresía</th>
                                            <th>Estatus</th>
                                            <th>Registro</th>
                                            <th>Inicio</th>
                                            <th>Vence</th>
                                            <th>Recurrente</th>
                                            <th>Recurrente Procepago</th>
                                            <th>Renovaciones</th>
                                            <th>Lavados</th>
                                            <th>Último lavado</th>
                                            <th>Prosepago ID</th>
                                            <th>Titular</th>
                                            <th>Tarjeta</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

    <!-- Modal detalle cliente -->
    <div class="modal fade" id="modalDetalleCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);color:#fff;">
                    <h5 class="modal-title mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        <span id="modalClienteNombre">—</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">

                    <!-- Tabs -->
                    <ul class="nav nav-tabs px-3 pt-2" id="detalleTab">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-membresias">
                                <i class="bi bi-credit-card me-1"></i>Membresías
                                <span class="badge bg-primary ms-1" id="badge-membresias">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lavados">
                                <i class="bi bi-droplet me-1"></i>Lavados
                                <span class="badge bg-info ms-1" id="badge-lavados">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-domiciliaciones">
                                <i class="bi bi-bank me-1"></i>Cobros
                                <span class="badge bg-success ms-1" id="badge-domiciliaciones">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-transacciones">
                                <i class="bi bi-arrow-left-right me-1"></i>Transacciones
                                <span class="badge bg-warning text-dark ms-1" id="badge-transacciones">0</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content px-3 pb-3 pt-2" id="detalleTabContent">

                        <!-- Tab Membresías -->
                        <div class="tab-pane fade show active" id="tab-membresias">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered table-hover" id="tbl-membresias">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th><th>Tipo</th><th>Inicio</th><th>Vence</th>
                                            <th>Meses</th><th>Usos</th><th>Prosepago ID</th><th>Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-membresias"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Lavados -->
                        <div class="tab-pane fade" id="tab-lavados">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered table-hover" id="tbl-lavados">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th><th>Fecha</th><th>Membresía</th><th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-lavados"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Cobros (Domiciliaciones) -->
                        <div class="tab-pane fade" id="tab-domiciliaciones">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th><th>Tarjeta</th><th>Titular</th><th>Banco</th>
                                            <th>Importe</th><th>Concepto</th><th>Estatus</th><th>Mensaje</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-domiciliaciones"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Transacciones -->
                        <div class="tab-pane fade" id="tab-transacciones">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th><th>Folio</th><th>Importe</th><th>Concepto</th>
                                            <th>Estatus</th><th>Causa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-transacciones"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

        <script src="{{ asset('assets/js/indicadores-clientes.js') }}?v={{ filemtime(public_path('assets/js/indicadores-clientes.js')) }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                cargarClientes();
            });
        </script>
    </main>

    <footer id="footer" class="footer">
        <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
    </footer>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    @include('layout.footer')
</body>
