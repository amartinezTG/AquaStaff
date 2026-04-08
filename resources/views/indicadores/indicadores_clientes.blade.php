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
                            <div class="table-responsive">

                                <table id="clientes_table" class="table table-bordered table-hover nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Tag</th>
                                            <th>Placa</th>
                                            <th>Auto</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Membresía</th>
                                            <th>Estatus</th>
                                            <th>Inicio</th>
                                            <th>Vence</th>
                                            <th>Recurrente</th>
                                            <th>Renovaciones</th>
                                            <th>Lavados</th>
                                            <th>Último lavado</th>
                                            <th>Prosepago ID</th>
                                            <th>Banco</th>
                                            <th>Titular</th>
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

        <script src="{{ asset('assets/js/indicadores-clientes.js') }}"></script>
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
