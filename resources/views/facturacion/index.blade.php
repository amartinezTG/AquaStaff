@include('layout.shared')
@include('layout.includes')
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="/dashboard" class="logo d-flex align-items-center">
                <img src="/assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        @include('layout.nav-header')
    </header>
  
    <main id="main" class="main">
        <style>
            #facturacion_table, #historial_table { font-size: 0.80rem; }

            #facturacion_table thead th, #historial_table thead th {
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

            #facturacion_table tbody td, #historial_table tbody td {
                vertical-align: middle;
                padding: 7px 8px;
                font-size: 0.78rem;
                border-right: 1px solid #e9ecef;
            }

            #facturacion_table tbody tr.bloqueada { opacity: 0.55; }
            #facturacion_table tbody tr:hover:not(.bloqueada) { background-color: #e8f0fe; }

            .badge-pendiente  { background-color: #198754; color: #fff; }
            .badge-global     { background-color: #fd7e14; color: #fff; }
            .badge-individual { background-color: #dc3545; color: #fff; }

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
            .buttons-copy  { background: linear-gradient(135deg,#ffb800,#f59e0b) !important; }

            .stat-card { border-radius: 12px; border: none; transition: transform .2s; }
            .stat-card:hover { transform: translateY(-3px); }

            #btnGenerar {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 1050;
                border-radius: 30px;
                padding: 12px 28px;
                font-weight: 700;
                font-size: 0.95rem;
                box-shadow: 0 4px 20px rgba(0,0,0,.25);
                display: none;
            }
        </style>

        <div class="pagetitle">
            <h1>Facturación Global</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Facturación</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="facturacionTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tabTransacciones">
                        <i class="bi bi-list-check me-1"></i>Transacciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tabHistorial" id="tabHistorialLink">
                        <i class="bi bi-archive me-1"></i>Historial
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                <!-- ===================== TAB: TRANSACCIONES ===================== -->
                <div class="tab-pane fade show active" id="tabTransacciones">

                    <!-- Filtros -->
                    <div class="card mb-3" style="border-left:5px solid #0d6efd;">
                        <div class="card-body py-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Fecha inicio</label>
                                    <input type="date" id="fechaInicio" class="form-control form-control-sm"
                                        value="{{ now()->startOfMonth()->toDateString() }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Fecha fin</label>
                                    <input type="date" id="fechaFinal" class="form-control form-control-sm"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Forma de pago</label>
                                    <select id="paymentType" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        <option value="0">Efectivo</option>
                                        <option value="1">Débito</option>
                                        <option value="2">Crédito</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Cajero</label>
                                    <input type="text" id="cajeroFiltro" class="form-control form-control-sm" placeholder="Ej: AQUA01">
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Periodicidad</label>
                                    <select id="periodicidad" class="form-select form-select-sm">
                                        <option value="04" selected>Mensual</option>
                                        <option value="01">Diaria</option>
                                        <option value="02">Semanal</option>
                                        <option value="03">Quincenal</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Estatus</label>
                                    <select id="estatusFiltro" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        <option value="pendiente" selected>Sin facturar</option>
                                        <option value="global">En factura global</option>
                                        <option value="individual">Facturada individualmente</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2 d-flex gap-2">
                                    <button class="btn btn-primary btn-sm w-100" onclick="buscarTransacciones()">
                                        <i class="bi bi-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cards resumen -->
                    <div class="row mb-3" id="summaryCards" style="display:none;">
                        <div class="col-6 col-md-3 mb-2">
                            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                                <div class="card-body py-3">
                                    <h6 class="mb-1" style="font-size:.8rem;">Total transacciones</h6>
                                    <h3 class="mb-0" id="statTotal">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#198754,#146c43);">
                                <div class="card-body py-3">
                                    <h6 class="mb-1" style="font-size:.8rem;">Pendientes</h6>
                                    <h3 class="mb-0" id="statPendiente">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#fd7e14,#e8650a);">
                                <div class="card-body py-3">
                                    <h6 class="mb-1" style="font-size:.8rem;">Ya en factura global</h6>
                                    <h3 class="mb-0" id="statGlobal">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#dc3545,#b02a37);">
                                <div class="card-body py-3">
                                    <h6 class="mb-1" style="font-size:.8rem;">Facturadas individualmente</h6>
                                    <h3 class="mb-0" id="statIndividual">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>  
  
                    <!-- Selección info -->
                    <div id="seleccionInfo" class="alert alert-info py-2 mb-2" style="display:none; font-size:.82rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="seleccionTexto">0 transacciones seleccionadas — $0.00</span>
                        <button class="btn btn-sm btn-outline-secondary ms-3" onclick="limpiarSeleccion()">Limpiar selección</button>
                    </div>

                    <!-- Tabla -->
                    <div class="card" style="border-left:5px solid #0d6efd;">
                        <div class="card-body p-3">
                            <table id="facturacion_table" class="table table-bordered table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width:30px;"><input type="checkbox" id="selectAll" title="Seleccionar todos los pendientes"></th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Folio (_id)</th>
                                        <th>Cajero</th>
                                        <th>Tipo Tx</th>
                                        <th>Forma Pago</th>
                                        <th>Total</th>
                                        <th>Estatus</th>
                                        <th>Factura Global</th>
                                        <th>Cadena Fact.</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <!-- /TAB TRANSACCIONES -->

                <!-- ===================== TAB: HISTORIAL ===================== -->
                <div class="tab-pane fade" id="tabHistorial">
                    <div class="card" style="border-left:5px solid #0d6efd;">
                        <div class="card-body p-3">
                            <table id="historial_table" class="table table-bordered table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>UUID / Folio Fiscal</th>
                                        <th>Forma Pago</th>
                                        <th>Total</th>
                                        <th>Transacciones</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Generada</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /TAB HISTORIAL -->

            </div><!-- /tab-content -->

        </section>

        <!-- Botón flotante Generar -->
        <button id="btnGenerar" class="btn btn-success" onclick="generarFactura()">
            <i class="bi bi-receipt me-2"></i>Generar Factura Global
        </button>

        <script src="{{ asset('assets/js/facturacion.js') }}"></script>
    </main>

    <footer id="footer" class="footer">
        <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
    </footer>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    @include('layout.footer')
</body>
