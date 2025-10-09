{{-- resources/views/indicadores/membresias.blade.php --}}

@include('layout.shared')
@include('layout.includes')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 

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
        <style>
            /* Estilos para la tabla de membresías */
            #indicadores_membresias_table {
                font-size: 0.82rem;
                border-collapse: separate;
                border-spacing: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden;
            }

            /* Headers */
            #indicadores_membresias_table thead th {
                white-space: normal !important;
                line-height: 1.2;
                font-size: 0.78rem;
                vertical-align: middle;
                background: linear-gradient(135deg, #6f42c1 0%, #553c9a 100%);
                color: #ffffff;
                font-weight: 600;
                text-align: center;
                padding: 12px 8px;
                border: none;
            }

            /* Filas del cuerpo */
            #indicadores_membresias_table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            #indicadores_membresias_table tbody tr:hover {
                background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(111, 66, 193, 0.15);
            }

            /* Celdas */
            #indicadores_membresias_table tbody td {
                white-space: nowrap;
                padding: 12px 8px;
                border-right: 1px solid #e9ecef;
                font-size: 0.80rem;
                vertical-align: middle;
            }

            /* Columna de cliente */
            #indicadores_membresias_table tbody td:nth-child(1) {
                font-weight: 600;
                background-color: #f8f9fa;
                color: #495057;
                border-left: 4px solid #6f42c1;
                white-space: normal;
                max-width: 200px;
            }

            /* Columna de UserID */
            #indicadores_membresias_table tbody td:nth-child(2) {
                font-family: monospace;
                font-size: 0.75rem;
                color: #6c757d;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Columna de paquete */
            #indicadores_membresias_table tbody td:nth-child(3) {
                font-weight: 600;
                color: #28a745;
            }

            /* Columna de tipo membresía */
            #indicadores_membresias_table tbody td:nth-child(4) {
                font-weight: 700;
                padding: 8px;
            }

            /* Estilos para tipos de membresía */
            .membership-express {
                background-color: #fff3cd !important;
                color: #856404 !important;
                border-left: 3px solid #ffc107 !important;
            }

            .membership-basico {
                background-color: #d1ecf1 !important;
                color: #0c5460 !important;
                border-left: 3px solid #17a2b8 !important;
            }

            .membership-ultra {
                background-color: #d4edda !important;
                color: #155724 !important;
                border-left: 3px solid #28a745 !important;
            }

            .membership-delux {
                background-color: #f8d7da !important;
                color: #721c24 !important;
                border-left: 3px solid #dc3545 !important;
            }

            .membership-na {
                background-color: #e2e3e5 !important;
                color: #383d41 !important;
                border-left: 3px solid #6c757d !important;
            }

            /* Columna de total órdenes */
            #indicadores_membresias_table tbody td:last-child {
                background-color: #e7f3ff;
                font-weight: 700;
                color: #004085;
                border-left: 2px solid #007bff;
                text-align: center;
                font-size: 1rem;
            }

            /* Botones DataTables */
            .dt-buttons {
                margin-bottom: 15px;
                gap: 8px;
                display: flex;
                flex-wrap: wrap;
            }

            .dt-button {
                color: #ffffff !important;
                border-radius: 25px !important;
                font-weight: 600 !important;
                font-size: 0.85rem !important;
                padding: 8px 20px !important;
                border: none !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .dt-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            .buttons-excel {
                background: linear-gradient(135deg, #46b723ff 0%, #3cb723ff 100%) !important;
            }

            .buttons-pdf {
                background: linear-gradient(135deg, #ff3b3bff 0%, #e53e3e 100%) !important;
            }

            .buttons-copy {
                background: linear-gradient(135deg, #ffb800ff 0%, #f59e0b 100%) !important;
            }

            .btn-primary {
                background: linear-gradient(135deg, #6f42c1 0%, #553c9a 100%) !important;
                border: none !important;
                border-radius: 25px !important;
                font-weight: 600 !important;
                text-transform: uppercase;
            }

            .btn-warning {
                background: linear-gradient(135deg, #ffc107 0%, #f59e0b 100%) !important;
                border: none !important;
                border-radius: 25px !important;
                font-weight: 600 !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .btn-warning:hover {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            /* Cards */
            .card {
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                border: none;
            }

            /* Estilos para gráficas */
            #chartsMembershipsContainer .card {
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border: none;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            #chartsMembershipsContainer .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            }

            #chartsMembershipsContainer .card-header {
                border-radius: 12px 12px 0 0 !important;
                border-bottom: none;
            }

            #chartsMembershipsContainer {
                animation: slideIn 0.5s ease-out;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Badge para contadores */
            .badge-counter {
                font-size: 1.2rem;
                padding: 8px 15px;
                border-radius: 20px;
                font-weight: 700;
            }

            /* Responsive */
            @media (max-width: 768px) {
                #indicadores_membresias_table {
                    font-size: 0.75rem;
                }
                
                #indicadores_membresias_table thead th {
                    font-size: 0.7rem;
                    padding: 8px 4px;
                }
                
                #indicadores_membresias_table tbody td {
                    padding: 8px 4px;
                    font-size: 0.75rem;
                    white-space: normal;
                }
                
                #indicadores_membresias_table tbody td:nth-child(1),
                #indicadores_membresias_table tbody td:nth-child(2) {
                    max-width: 120px;
                }
                
                .dt-buttons {
                    justify-content: center;
                }
                
                .dt-button {
                    font-size: 0.75rem !important;
                    padding: 6px 12px !important;
                }
            }
        </style>

        <div class="pagetitle">
            <h1>Indicadores de Membresías</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item">Indicadores</li>
                    <li class="breadcrumb-item active">Membresías</li>
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
                                        value="{{ now()->startOfMonth()->toDateString() }}" required>
                                </div>

                                <div class="col-md-5">
                                    <label for="fecha_final" class="form-label">Fecha Final</label>
                                    <input type="date" class="form-control" name="fecha_final" id="fecha_final"
                                        value="{{ now()->endOfMonth()->toDateString() }}" required>
                                </div>

                                <div class="col-md-2">
                                    <button class="btn btn-warning w-100 submitBtn" onclick="indicadoresMembresiasTable()" type="button">
                                        <i class="ti ti-search me-1"></i>Consultar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards de resumen rápido -->
            <div class="row" id="quickSummaryCards" style="display: none;">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #6f42c1 0%, #553c9a 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Total Clientes</h6>
                                    <h3 class="mb-0" id="totalClientes">0</h3>
                                </div>
                                <div class="text-white-50">
                                    <i class="ti ti-users" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Total Lavados</h6>
                                    <h3 class="mb-0" id="totalOrdenes">0</h3>
                                </div>
                                <div class="text-white-50">
                                    <i class="ti ti-shopping-cart" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #f59e0b 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Membresía Top</h6>
                                    <h6 class="mb-0" id="membershipTop">N/A</h6>
                                </div>
                                <div class="text-white-50">
                                    <i class="ti ti-crown" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-white" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Promedio Órdenes</h6>
                                    <h3 class="mb-0" id="promedioOrdenes">0</h3>
                                </div>
                                <div class="text-white-50">
                                    <i class="ti ti-chart-bar" style="font-size: 2rem;"></i>
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
                                <table id="indicadores_membresias_table" class="table table-bordered table-hover table-striped dt-responsive nowrap w-100 dataTable">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>User ID</th>
                                            <th>Paquete</th>
                                            <th>Tipo Membresía</th>
                                            <th>Total Órdenes</th>
                                            <th>Ticket Promedio</th>
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
            <div id="chartsMembershipsContainer" class="row mt-4" style="display: none;">
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #6f42c1 0%, #553c9a 100%); color: white;">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-chart-pie me-2"></i>Análisis de Membresías
                            </h5>
                            <button type="button" class="btn btn-sm btn-light" onclick="toggleChartsMemberships()">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Distribución de Membresías -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Distribución Clientes por Tipo de Membresía</h6>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 350px;">
                                <canvas id="membershipDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Órdenes por Membresía -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Total Órdenes por Tipo de Membresía</h6>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 350px;">
                                <canvas id="ordersPerMembershipChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Top 10 Clientes -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Top 10 Clientes por Número de Órdenes</h6>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 400px;">
                                <canvas id="topClientsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Detalladas -->
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: white;">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-calculator me-2"></i>Estadísticas Detalladas del Período
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="detailedStats">
                                <!-- Estadísticas se llenarán dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- Incluir el script de indicadores de membresías -->
        <script src="{{ asset('assets/js/indicadores-membresias.js') }}"></script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                indicadoresMembresiasTable();
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