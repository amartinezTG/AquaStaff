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
            #individual_table { font-size: 0.80rem; }
 
            #individual_table thead th {
                background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
                color: #fff;
                font-weight: 600;
                font-size: 0.76rem;
                text-align: center;
                vertical-align: middle;
                white-space: nowrap;
                padding: 10px 8px;
                border: none;
            }

            #individual_table tbody td {
                vertical-align: middle;
                padding: 7px 8px;
                font-size: 0.78rem;
                border-right: 1px solid #e9ecef;
            }

            #individual_table tbody tr.bloqueada { opacity: 0.50; background: #f8f8f8; }
            #individual_table tbody tr:hover:not(.bloqueada) { background-color: #f3eeff; }

            .badge-pendiente  { background-color: #198754; color: #fff; }
            .badge-global     { background-color: #fd7e14; color: #fff; }
            .badge-individual { background-color: #6f42c1; color: #fff; }

            .dt-button {
                color: #fff !important;
                border-radius: 20px !important;
                font-weight: 600 !important;
                font-size: 0.82rem !important;
                padding: 7px 18px !important;
                border: none !important;
            }
            .buttons-excel { background: linear-gradient(135deg,#46b723,#3cb723) !important; }
            .buttons-copy  { background: linear-gradient(135deg,#ffb800,#f59e0b) !important; }

            .stat-card { border-radius: 12px; border: none; transition: transform .2s; }
            .stat-card:hover { transform: translateY(-3px); }

            #btnFacturar {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 1050;
                border-radius: 30px;
                padding: 12px 28px;
                font-weight: 700;
                font-size: 0.95rem;
                box-shadow: 0 4px 20px rgba(111,66,193,.4);
                display: none;
                background: linear-gradient(135deg, #6f42c1, #5a32a3);
                border: none;
                color: #fff;
            }

            /* Autocomplete dropdown */
            #rfc-suggestions {
                position: absolute;
                z-index: 9999;
                background: #fff;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                max-height: 220px;
                overflow-y: auto;
                width: 100%;
                box-shadow: 0 4px 12px rgba(0,0,0,.12);
            }
            #rfc-suggestions .suggestion-item {
                padding: 8px 12px;
                cursor: pointer;
                font-size: .82rem;
                border-bottom: 1px solid #f0f0f0;
            }
            #rfc-suggestions .suggestion-item:hover { background: #f3eeff; }
        </style>

        <div class="pagetitle">
            <h1>Facturación Individual</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item"><a href="/facturacion">Facturación Global</a></li>
                    <li class="breadcrumb-item active">Facturación Individual</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">

            <!-- Filtros -->
            <div class="card mb-3" style="border-left:5px solid #6f42c1;">
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
                            <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Tipo de transacción</label>
                            <select id="transactionType" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="0">Compra Membresía</option>
                                <option value="1" selected>Renovación Membresía</option>
                                <option value="2">Compra Paquete</option>
                            </select>
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
                            <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Estatus</label>
                            <select id="estatusFiltro" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="pendiente" selected>Sin facturar</option>
                                <option value="global">En factura global</option>
                                <option value="individual">Ya facturada</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                            <button class="btn btn-sm w-100 text-white" style="background:#6f42c1;" onclick="buscarTransacciones()">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards resumen -->
            <div class="row mb-3" id="summaryCards" style="display:none;">
                <div class="col-6 col-md-3 mb-2">
                    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#6f42c1,#5a32a3);">
                        <div class="card-body py-3">
                            <h6 class="mb-1" style="font-size:.8rem;">Total mostradas</h6>
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
                            <h6 class="mb-1" style="font-size:.8rem;">En factura global</h6>
                            <h3 class="mb-0" id="statGlobal">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                        <div class="card-body py-3">
                            <h6 class="mb-1" style="font-size:.8rem;">Ya facturadas indiv.</h6>
                            <h3 class="mb-0" id="statIndividual">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info selección -->
            <div id="seleccionInfo" class="alert alert-purple py-2 mb-2"
                style="display:none; font-size:.82rem; background:#f3eeff; border:1px solid #c9b8f0; color:#4a2c8a;">
                <i class="bi bi-receipt me-1"></i>
                <span id="seleccionTexto">0 transacciones seleccionadas — $0.00</span>
                <button class="btn btn-sm btn-outline-secondary ms-3" onclick="limpiarSeleccion()">Limpiar selección</button>
            </div>

            <!-- Tabla -->
            <div class="card" style="border-left:5px solid #6f42c1;">
                <div class="card-body p-3">
                    <table id="individual_table" class="table table-bordered table-hover" style="width:100%">
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
                                <th>Datos Factura</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </section>

        <!-- Botón flotante -->
        <button id="btnFacturar" onclick="abrirModalFacturar()">
            <i class="bi bi-receipt me-2"></i>Facturar selección
        </button>

        <script src="{{ asset('assets/js/facturacion_individual.js') }}"></script>
    </main>

    <footer id="footer" class="footer">
        <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
    </footer>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    @include('layout.footer')
</body>
