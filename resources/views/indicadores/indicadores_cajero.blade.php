{{-- resources/views/indicadores/pagos.blade.php --}}

@include('layout.shared')
@include('layout.includes')

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
            /* Estilos para la tabla de pagos */
            #indicadores_pagos_table {
                font-size: 0.82rem;
                border-collapse: separate;
                border-spacing: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden;
            }

            /* Headers */
            #indicadores_pagos_table thead th {
                white-space: normal !important;
                line-height: 1.2;
                font-size: 0.78rem;
                vertical-align: middle;
                background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
                color: #ffffff;
                font-weight: 600;
                text-align: center;
                padding: 10px 8px;
                border: none;
            }

            /* Fila de grupos */
            #indicadores_pagos_table thead tr.group-header th {
                background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
                color: #ffffff;
                font-size: 0.85rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid #ffffff;
            }

            /* Filas del cuerpo */
            #indicadores_pagos_table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            #indicadores_pagos_table tbody tr:hover {
                background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
            }

            /* Celdas */
            #indicadores_pagos_table tbody td {
                white-space: nowrap;
                padding: 10px 8px;
                border-right: 1px solid #e9ecef;
                font-size: 0.80rem;
                vertical-align: middle;
            }

            /* Primera columna (fechas) */
            #indicadores_pagos_table tbody td:first-child {
                font-weight: 600;
                background-color: #f8f9fa;
                color: #495057;
                border-left: 4px solid #28a745;
            }

            /* Columnas de totales destacadas */
            #indicadores_pagos_table tbody td:nth-child(2) {
                font-weight: 700;
                color: #007bff;
            }

            #indicadores_pagos_table tbody td:nth-child(3) {
                font-weight: 700;
                color: #28a745;
            }

            #indicadores_pagos_table tbody td:nth-child(6) {
                font-weight: 700;
                color: #17a2b8;
            }

            #indicadores_pagos_table tbody td:nth-child(9) {
                font-weight: 700;
                color: #ffc107;
            }

            #indicadores_pagos_table tbody td:nth-child(14) {
                font-weight: 700;
                color: #28a745;
            }

            #indicadores_pagos_table tbody td:last-child {
                background-color: #fff3cd;
                font-weight: 700;
                color: #856404;
                border-left: 2px solid #ffc107;
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
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
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
            #chartsPaymentsContainer .card {
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border: none;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            #chartsPaymentsContainer .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            }

            #chartsPaymentsContainer .card-header {
                border-radius: 12px 12px 0 0 !important;
                border-bottom: none;
            }

            #chartsPaymentsContainer {
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

            /* Responsive */
            @media (max-width: 768px) {
                #indicadores_pagos_table {
                    font-size: 0.75rem;
                }
                
                #indicadores_pagos_table thead th {
                    font-size: 0.7rem;
                    padding: 8px 4px;
                }
                
                #indicadores_pagos_table tbody td {
                    padding: 8px 4px;
                    font-size: 0.75rem;
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
            <h1>Indicadores de Pagos y Cajeros</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item">Indicadores</li>
                    <li class="breadcrumb-item active">Pagos y Cajeros</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <!-- Filtros de fecha -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #28a745;">
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
                                    <button class="btn btn-warning w-100 submitBtn" onclick="indicadoresPagosTable()" type="button">
                                        <i class="ti ti-search me-1"></i>Consultar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de indicadores de pagos -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #28a745;">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="indicadores_pagos_table" class="table table-bordered table-hover table-striped dt-responsive nowrap w-100 dataTable">
                                    <thead>
                                        <tr class="group-header">
                                            <th rowspan="2">Fecha</th>
                                            <th rowspan="2">Total Lavados   </th>
                                            
                                            <th colspan="3" class="text-center">Efectivo</th>
                                            <th colspan="3" class="text-center">Tarjetas Paquetes</th>
                                            <th colspan="5" class="text-center">Membresías</th>
                                            
                                            <th rowspan="2">$ Total Procepago</th>
                                            <th rowspan="2">$ Total Día</th>
                                        </tr>
                                        <tr>
                                            <!-- Efectivo -->
                                            <th>Total</th>
                                            <th>AQUA01</th>
                                            <th>AQUA02</th>
                                            
                                            <!-- Tarjetas Paquetes -->
                                            <th>Total</th>
                                            <th>AQUA01</th>
                                            <th>AQUA02</th>
                                            
                                            <!-- Membresías -->
                                            <th>Total</th>
                                            <th>Compra AQUA01</th>
                                            <th>Compra AQUA02</th>
                                            <th>Renov. AQUA01</th>
                                            <th>Renov. AQUA02</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de Gráficas de Pagos -->
            <div id="chartsPaymentsContainer" class="row mt-4" style="display: none;">
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-chart-pie me-2"></i>Análisis de Pagos y Cajeros
                            </h5>
                            <button type="button" class="btn btn-sm btn-light" onclick="toggleChartsPayments()">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Métodos de Pago -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 350px;">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Ingresos Diarios -->
                <div class="col-lg-8 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 350px;">
                                <canvas id="dailyRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Comparación Cajeros -->
                <div class="col-lg-8 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 400px;">
                                <canvas id="cajeroComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

              

                <!-- Resumen Estadístico -->
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: white;">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-calculator me-2"></i>Resumen del Período
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="summaryStats">
                                <!-- Estadísticas se llenarán dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- Incluir el script de indicadores de pagos -->
        <script src="{{ asset('assets/js/indicadores.js') }}"></script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                indicadoresPagosTable();
            });
        </script>

    </main>

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span></span></strong>. All Rights Reserved
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    @include('layout.footer')

</body>