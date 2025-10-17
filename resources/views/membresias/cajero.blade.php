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
        </div>
        @include('layout.nav-header')
    </header>

    <main id="main" class="main">
        <style>
            /* Estilos para la tabla de membresías */
            #membresias_table {
                font-size: 0.85rem;
                border-collapse: separate;
                border-spacing: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden;
            }

            #membresias_table thead th {
                white-space: normal !important;
                line-height: 1.2;
                font-size: 0.80rem;
                vertical-align: middle;
                background: linear-gradient(135deg, #2399b7ff 0%, #1a7a91 100%);
                color: #ffffff;
                font-weight: 600;
                text-align: center;
                padding: 12px 8px;
                border: none;
            }

            #membresias_table thead th:hover {
                background: linear-gradient(135deg, #1a7a91 0%, #145a6b 100%);
                transition: all 0.3s ease;
            }

            #membresias_table tbody tr {
                transition: all 0.2s ease;
                border-bottom: 1px solid #e9ecef;
            }

            #membresias_table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            #membresias_table tbody tr:nth-child(odd) {
                background-color: #ffffff;
            }

            #membresias_table tbody tr:hover {
                background: linear-gradient(135deg, #e3f2fd 0%, #f1f8ff 100%);
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(35, 153, 183, 0.15);
            }

            #membresias_table tbody td {
                white-space: nowrap;
                padding: 12px 8px;
                border-right: 1px solid #e9ecef;
                font-size: 0.82rem;
                vertical-align: middle;
            }

            /* Badges para tipos de transacción */
            .badge-compra {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                padding: 4px 12px;
                border-radius: 15px;
                font-weight: 600;
            }

            .badge-renovacion {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                color: white;
                padding: 4px 12px;
                border-radius: 15px;
                font-weight: 600;
            }

            /* Badges para tipos de pago */
            .badge-efectivo {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 0.75rem;
            }

            .badge-debito {
                background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
                color: white;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 0.75rem;
            }

            .badge-credito {
                background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
                color: white;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 0.75rem;
            }

            .badge-cortesia {
                background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
                color: white;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 0.75rem;
            }

            /* Paquetes */
            .badge-express {
                background: #e3f2fd;
                color: #1976d2;
                padding: 4px 10px;
                border-radius: 12px;
                font-weight: 600;
            }

            .badge-basico {
                background: #f3e5f5;
                color: #7b1fa2;
                padding: 4px 10px;
                border-radius: 12px;
                font-weight: 600;
            }

            .badge-ultra {
                background: #fff3e0;
                color: #e65100;
                padding: 4px 10px;
                border-radius: 12px;
                font-weight: 600;
            }

            .badge-delux {
                background: #fce4ec;
                color: #c2185b;
                padding: 4px 10px;
                border-radius: 12px;
                font-weight: 600;
            }

            /* Columna de total */
            .total-amount {
                font-weight: 700;
                color: #28a745;
                font-size: 1rem;
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

            @media (max-width: 768px) {
                #membresias_table {
                    font-size: 0.75rem;
                }
                
                #membresias_table thead th {
                    font-size: 0.7rem;
                    padding: 8px 4px;
                }
                
                #membresias_table tbody td {
                    padding: 8px 4px;
                    font-size: 0.75rem;
                }
            }
        </style>

        <div class="pagetitle">
            <h1>Membresías en Cajero</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Membresías</li>
                    <li class="breadcrumb-item active">Cajero</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #ffc107;">
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
                                    <button class="btn btn-warning w-100 submitBtn" onclick="membresiasTable()" type="button">
                                        Consultar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #2399b7ff;">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="membresias_table" class="table table-bordered table-hover table-striped dt-responsive nowrap w-100 dataTable">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Tipo Transacción</th>
                                            <th>Tipo Pago</th>
                                            <th>Paquete</th>
                                            <th>Total</th>
                                            <th>Cajero</th>
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

        <!-- AGREGAR ESTE BLOQUE DE GRÁFICAS -->
        <section class="section">
            <div id="chartsContainer" class="row mt-4" style="display: none;">
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center" 
                             style="background: linear-gradient(135deg, #2399b7ff 0%, #1a7a91 100%); color: white;">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-chart-line me-2"></i>Análisis Gráfico de Membresías
                            </h5>
                            <button type="button" class="btn btn-sm btn-light" onclick="toggleChartsMembresias()">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Ingresos por Día -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center mb-3">Ingresos por Día</h6>
                            <div style="position: relative; height: 300px;">
                                <canvas id="ingresosDiaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Cantidad de Membresías por Día -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center mb-3">Cantidad de Membresías por Día</h6>
                            <div style="position: relative; height: 300px;">
                                <canvas id="cantidadMembresiasChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Distribución por Tipo de Pago -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center mb-3">Distribución por Tipo de Pago</h6>
                            <div style="position: relative; height: 300px;">
                                <canvas id="tipoPagoChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Paquetes Vendidos -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center mb-3">Paquetes Más Vendidos</h6>
                            <div style="position: relative; height: 300px;">
                                <canvas id="paquetesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                #chartsContainer .card {
                    border-radius: 12px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    border: none;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }

                #chartsContainer .card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
                }

                #chartsContainer .card-header {
                    border-radius: 12px 12px 0 0 !important;
                    border-bottom: none;
                }

                #chartsContainer canvas {
                    max-height: 100%;
                }

                #chartsContainer {
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

                @media (max-width: 768px) {
                    #chartsContainer canvas {
                        max-height: 250px;
                    }
                }
            </style>
        </section>
        <!-- FIN DEL BLOQUE DE GRÁFICAS -->

        <script src="{{ asset('assets/js/membresias.js') }}"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                membresiasTable();
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