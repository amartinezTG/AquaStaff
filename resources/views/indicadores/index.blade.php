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

            
           /* Estilos generales para la tabla */
            #indicadores_table {
                font-size: 0.85rem;
                border-collapse: separate;
                border-spacing: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden;
            }

            /* Header principal */
            #indicadores_table thead th {
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
                position: relative;
            }

            /* Fila de grupos (colspans) */
            #indicadores_table thead tr.group-header th {
                background: linear-gradient(135deg, #2399b7ff 0%, #1a7a91 100%);
                color: #ffffff;
                font-size: 0.85rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid #ffffff;
            }

            /* Segunda fila del header */
            #indicadores_table thead tr:last-child th {
                background: linear-gradient(135deg, #2db5d1 0%, #2399b7ff 100%);
                font-size: 0.78rem;
                padding: 10px 6px;
            }

            /* Hover en headers */
            #indicadores_table thead th:hover {
                background: linear-gradient(135deg, #1a7a91 0%, #145a6b 100%);
                transition: all 0.3s ease;
            }

            /* Filas del cuerpo */
            #indicadores_table tbody tr {
                transition: all 0.2s ease;
                border-bottom: 1px solid #e9ecef;
            }

            #indicadores_table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            #indicadores_table tbody tr:nth-child(odd) {
                background-color: #ffffff;
            }

            #indicadores_table tbody tr:hover {
                background: linear-gradient(135deg, #e3f2fd 0%, #f1f8ff 100%);
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(35, 153, 183, 0.15);
            }

            /* Celdas del cuerpo */
            #indicadores_table tbody td {
                white-space: nowrap;
                padding: 12px 8px;
                border-right: 1px solid #e9ecef;
                font-size: 0.82rem;
                vertical-align: middle;
            }

            /* Primera columna (fechas) */
            #indicadores_table tbody td:first-child {
                font-weight: 600;
                background-color: #f8f9fa;
                color: #495057;
                border-left: 4px solid #2399b7ff;
            }

            /* Columnas de números */
            #indicadores_table tbody td.text-end {
                text-align: right;
                font-family: 'Courier New', monospace;
                font-weight: 500;
            }

            /* Columnas de dinero */
            #indicadores_table tbody td[data-type="currency"] {
                color: #28a745;
                font-weight: 600;
            }

            /* Total eventos (segunda columna) */
            #indicadores_table tbody td:nth-child(2) {
                font-weight: 700;
                color: #2399b7ff;
                background-color: #f0f9ff;
            }

            /* Últimas columnas importantes (totales) */
            #indicadores_table tbody td:nth-last-child(2),
            #indicadores_table tbody td:last-child {
                background-color: #fff3cd;
                font-weight: 700;
                color: #856404;
                border-left: 2px solid #ffc107;
            }

            /* Mejoras en los botones de DataTables */
            

            /* Mejoras en la paginación */
            .dataTables_paginate .paginate_button {
                border-radius: 50% !important;
                margin: 0 2px;
            }

            .dataTables_paginate .paginate_button.current {
                background: #2399b7ff !important;
                border-color: #2399b7ff !important;
                color: white !important;
            }

            /* Mejoras en el buscador */
            .dataTables_filter input {
                border-radius: 25px;
                border: 2px solid #e9ecef;
                padding: 8px 15px;
                transition: all 0.3s ease;
            }

            .dataTables_filter input:focus {
                border-color: #2399b7ff;
                box-shadow: 0 0 0 0.2rem rgba(35, 153, 183, 0.25);
            }

            /* Card container */
            .card {
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                border: none;
            }

            /* Responsive improvements */
            @media (max-width: 768px) {
                #indicadores_table {
                    font-size: 0.75rem;
                }
                
                #indicadores_table thead th {
                    font-size: 0.7rem;
                    padding: 8px 4px;
                }
                
                #indicadores_table tbody td {
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

            /* Loading state */
            .dataTables_processing {
                background: rgba(255, 255, 255, 0.9) !important;
                border: 1px solid #2399b7ff !important;
                border-radius: 8px !important;
                color: #2399b7ff !important;
                font-weight: 600 !important;
            }
        </style>

        <div class="pagetitle">
            <h1>Indicadores</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Indicadores</li>
                    <li class="breadcrumb-item active">Operativos</li>
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
                                    <<input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                        value="{{ now()->startOfMonth()->toDateString() }}" required>
                                </div>

                                <div class="col-md-5">
                                    <label for="fecha_final" class="form-label">Fecha Final</label>
                                    <input type="date" class="form-control" name="fecha_final" id="fecha_final"
                        value="{{ now()->endOfMonth()->toDateString() }}" required>
                                </div>

                                <div class="col-md-2">
                                    <button class="btn btn-warning w-100 submitBtn"  onclick="indicadoresTable()" type="button">
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
                    <div class="card " style="border-left: 5px solid #2399b7ff;">
                        <div class="col-12">

                            <div class="card-body p-4" >
                                <div class="table-responsive">
                                    <table id="indicadores_table" class="table table-bordered  table-hover  table-striped dt-responsive nowrap w-100 dataTable no-footer dtr-inline" style="position: relative; width: 1575px;">
                                        <thead>
                                            <tr class="group-header">
                                            <th rowspan="2">Fecha</th>
                                            <th rowspan="2">Total Lavados</th>

                                            <th colspan="8" class="text-center">Paquetes (pago)</th>
                                            <th colspan="5" class="text-center">Membresías (uso)</th>
                                            <th colspan="4" class="text-center">Mov. Membresía</th>

                                            <th rowspan="2">Garantia</th>
                                            <th rowspan="2">$ Total día</th>
                                            <th rowspan="2">$ Total Sin IVA </th>
                                            </tr>
                                            <tr>
                                            <!-- Paquetes (pago) -->
                                            <th>Lavados</th>
                                            <th>Express</th>
                                            <th>Basico</th>
                                            <th>Ultra</th>
                                            <th>Deluxe</th>
                                            <th>Promo 150</th>
                                            <th>Promo 50</th>
                                            <th>$ Paquetes</th>

                                            <!-- Membresías (uso) -->
                                            <th>Lavados</th>
                                            <th>Express</th>
                                            <th>Básico</th>
                                            <th>Ultra</th>
                                            <th>Deluxe</th>

                                            <!-- Mov. Membresía -->
                                            <th>Compra</th>
                                            <th>Renov.</th>
                                            <th>$ Compra</th>
                                            <th>$ Renov.</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div id="chartsContainer" class="row mt-4 " style="display: none;" >
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #2399b7ff 0%, #1a7a91 100%); color: white;">
                            <h5 class="card-title mb-0">
                                <i class="ti ti-chart-line me-2"></i>Análisis Gráfico de Indicadores
                            </h5>
                            <button type="button" class="btn btn-sm btn-light" onclick="toggleCharts()">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Total de Eventos -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 300px;">
                                <canvas id="totalEventosChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Ingresos -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 300px;">
                                <canvas id="ingresosChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Distribución de Servicios -->
                <div class="col-lg-8 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 400px;">
                                <canvas id="paquetesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de Membresías -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div style="position: relative; height: 400px;">
                                <canvas id="membresiasChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                /* Estilos adicionales para las gráficas */
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

                /* Animación de entrada */
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

                /* Loader personalizado para gráficas */
                .chart-loading {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    color: #2399b7ff;
                    font-size: 1.2rem;
                }

                .chart-loading::after {
                    content: '';
                    width: 20px;
                    height: 20px;
                    border: 2px solid #e9ecef;
                    border-top: 2px solid #2399b7ff;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    display: inline-block;
                    margin-left: 10px;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                /* Responsive para gráficas */
                @media (max-width: 768px) {
                    #chartsContainer .col-lg-8,
                    #chartsContainer .col-lg-6,
                    #chartsContainer .col-lg-4 {
                        margin-bottom: 1rem;
                    }
                    
                    #chartsContainer canvas {
                        max-height: 250px;
                    }
                }
            </style>
        </section>
          <script  src="{{ asset('assets/js/indicadores.js') }}"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    indicadoresTable();
                });
            </script>
    </main>
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span></span></strong>. All Rights Reserved
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    @include('layout.footer')


</body>
