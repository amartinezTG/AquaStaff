@include('layout.shared')
@include('layout.includes')
 
<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">
  
    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center">
                <img src="/assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->
        {{-- sidebar --}}
        @include('layout.nav-header')
    </header>

    <main id="main" class="main">
        <style>
            /* Estilos personalizados del dashboard */
            .dashboard-card {
                border-radius: 15px;
                border: none;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            } 

            .dashboard-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            }

            .metric-card {
                background: linear-gradient(135deg, var(--card-gradient-start), var(--card-gradient-end));
                color: white;
            }

            .metric-card.sales {
                --card-gradient-start: #28a745;
                --card-gradient-end: #20c997;
            }

            .metric-card.revenue {
                --card-gradient-start: #007bff;
                --card-gradient-end: #6610f2;
            }

            .metric-card.memberships {
                --card-gradient-start: #ffc107;
                --card-gradient-end: #fd7e14;
            }

            .metric-card.avg-ticket {
                --card-gradient-start: #dc3545;
                --card-gradient-end: #e83e8c;
            }

            .metric-value {
                font-size: 2.5rem;
                font-weight: 700;
                margin: 0;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .metric-label {
                font-size: 0.9rem;
                opacity: 0.9;
                margin-bottom: 5px;
            }

            .metric-change {
                font-size: 0.8rem;
                opacity: 0.8;
            }

            .metric-icon {
                font-size: 3rem;
                opacity: 0.3;
            }

            .chart-container {
                background: white;
                border-radius: 15px;
                padding: 20px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }

            .chart-title {
                font-size: 1.1rem;
                font-weight: 600;
                margin-bottom: 15px;
                color: #333;
            }

            .summary-section {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 15px;
                padding: 25px;
                margin-top: 20px;
            }

            .summary-item {
                background: white;
                border-radius: 10px;
                padding: 15px;
                margin-bottom: 10px;
                border-left: 4px solid #007bff;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .summary-item.membership {
                border-left-color: #ffc107;
            }

            .summary-item.package {
                border-left-color: #28a745;
            }

            .date-selector {
                background: white;
                border-radius: 10px;
                padding: 15px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }

            .refresh-btn {
                background: linear-gradient(135deg, #17a2b8, #138496);
                border: none;
                border-radius: 25px;
                color: white;
                padding: 10px 20px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .refresh-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
                color: white;
            }

            .loading-skeleton {
                background: linear-gradient(90deg, #f0f0f0 25%, transparent 37%, #f0f0f0 63%);
                background-size: 400% 100%;
                animation: shimmer 1.5s ease-in-out infinite;
            }

            @keyframes shimmer {
                0% { background-position: 100% 0; }
                100% { background-position: -100% 0; }
            }

            /* Cards de cajeros y lavados */
            .cajero-card {
                border-radius: 12px;
                border: none;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .cajero-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 6px 16px rgba(0,0,0,0.14);
            }
            .cajero-card .cajero-header {
                border-radius: 12px 12px 0 0;
                padding: 10px 16px;
                font-weight: 700;
                font-size: 0.95rem;
                color: white;
            }
            .cajero-card.aqua01 .cajero-header { background: linear-gradient(135deg, #007bff, #0056b3); }
            .cajero-card.aqua02 .cajero-header { background: linear-gradient(135deg, #17a2b8, #117a8b); }
            .cajero-card.total  .cajero-header { background: linear-gradient(135deg, #28a745, #1e7e34); }
            .cajero-stat {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 7px 0;
                border-bottom: 1px solid #f0f0f0;
                font-size: 0.85rem;
            }
            .cajero-stat:last-child { border-bottom: none; }
            .cajero-stat .stat-label { color: #6c757d; }
            .cajero-stat .stat-value { font-weight: 700; color: #333; }
            .cajero-stat .stat-value.efectivo { color: #28a745; }
            .cajero-stat .stat-value.tarjeta  { color: #007bff; }
            .cajero-stat .stat-value.membresia { color: #6f42c1; }
            .cajero-stat .stat-value.paquete   { color: #fd7e14; }

            /* Membresías activas — mini cards compactas */
            .mini-membership-card {
                border-radius: 12px;
                padding: 14px 16px;
                color: white;
                display: flex;
                align-items: center;
                gap: 12px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.12);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .mini-membership-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 6px 16px rgba(0,0,0,0.18);
            }
            .mini-membership-card .mini-icon {
                font-size: 1.8rem;
                opacity: 0.85;
            }
            .mini-membership-card .mini-label {
                font-size: 0.75rem;
                opacity: 0.9;
                margin-bottom: 2px;
            }
            .mini-membership-card .mini-count {
                font-size: 1.7rem;
                font-weight: 700;
                line-height: 1;
            }
            .mini-membership-card.total  { background: linear-gradient(135deg, #667eea, #764ba2); }
            .mini-membership-card.express { background: linear-gradient(135deg, #17a2b8, #138496); }
            .mini-membership-card.basico  { background: linear-gradient(135deg, #28a745, #1e7e34); }
            .mini-membership-card.ultra   { background: linear-gradient(135deg, #6f42c1, #5a32a3); }
            .mini-membership-card.delux   { background: linear-gradient(135deg, #fd7e14, #e36209); }

            /* Badges compactos de membresías por tipo */
            .membership-badge {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 14px;
                border-radius: 20px;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                min-width: 100px;
            }
            .membership-badge .badge-dot {
                width: 10px; height: 10px;
                border-radius: 50%;
                flex-shrink: 0;
            }
            .membership-badge .badge-label {
                font-size: 0.78rem;
                color: #6c757d;
                font-weight: 500;
            }
            .membership-badge .badge-count {
                font-size: 1.1rem;
                font-weight: 700;
                margin-left: auto;
            }
            .membership-badge.express .badge-dot   { background: #17a2b8; }
            .membership-badge.express .badge-count { color: #17a2b8; }
            .membership-badge.basico  .badge-dot   { background: #28a745; }
            .membership-badge.basico  .badge-count { color: #28a745; }
            .membership-badge.ultra   .badge-dot   { background: #6f42c1; }
            .membership-badge.ultra   .badge-count { color: #6f42c1; }
            .membership-badge.delux   .badge-dot   { background: #fd7e14; }
            .membership-badge.delux   .badge-count { color: #fd7e14; }

            /* Responsive */
            @media (max-width: 768px) {
                .metric-value {
                    font-size: 2rem;
                }
                
                .metric-icon {
                    font-size: 2rem;
                }
                
                .chart-container {
                    padding: 15px;
                }
                
                .summary-section {
                    padding: 15px;
                }

                .total-memberships-number {
                    font-size: 2.5rem;
                }
            }
        </style>

        <div class="pagetitle d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1>Dashboard AquaCar</h1>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <input type="date" class="form-control form-control-sm" id="dashboard_date"
                       value="{{ date('Y-m-d') }}" style="width:150px;">
                <button class="btn refresh-btn btn-sm" onclick="loadDashboardData(); loadActiveMemberships();">
                    <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                </button>
                <small class="text-muted">Últ. act: <span id="last-update">--:--</span></small>
            </div>
        </div>

        <section class="section dashboard">

            <!-- Métricas principales del día -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card dashboard-card metric-card sales">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-label">Lavados en el Día</div>
                                    <h3 class="metric-value" id="total_ordenes">0</h3>
                                    <div class="metric-change" id="ordenes_change">--</div>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="bi bi-graph-up metric-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card dashboard-card metric-card revenue">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-label">Ingresos</div>
                                    <h3 class="metric-value" id="total_sales">$0</h3>
                                    <div class="metric-change" id="ingresos_change">--</div>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="bi bi-currency-dollar metric-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card dashboard-card metric-card memberships">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-label">Membresías</div>
                                    <h3 class="metric-value" id="total_membresias">0</h3>
                                    <div class="metric-change" id="membresias_change">--</div>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="bi bi-people metric-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card dashboard-card metric-card avg-ticket">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-label">Ticket Promedio</div>
                                    <h3 class="metric-value" id="ticket_promedio">$0</h3>
                                    <div class="metric-change" id="ticket_change">--</div>
                                </div>
                                <div class="col-4 text-end">
                                    <i class="bi bi-receipt metric-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de servicios del día -->
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center px-3 pt-3 pb-2">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-muted"></i>Resumen de Servicios del Día</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="servicios_table" style="font-size:0.82rem;">
                                    <thead style="background:#f8f9fa;">
                                        <tr>
                                            <th class="px-3 py-2">Servicio</th>
                                            <th class="text-center py-2">Cantidad</th>
                                            <th class="text-end py-2">Efectivo</th>
                                            <th class="text-end py-2">Tarjeta</th>
                                            <th class="text-end py-2 pe-3">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="servicios_tbody">
                                        <tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>
                                    </tbody>
                                    <tfoot id="servicios_tfoot" style="background:#f8f9fa; font-weight:700; display:none;">
                                        <tr>
                                            <td class="px-3">Total</td>
                                            <td class="text-center" id="sf_pagos">0</td>
                                            <td class="text-end" id="sf_efectivo">$0</td>
                                            <td class="text-end" id="sf_tarjeta">$0</td>
                                            <td class="text-end pe-3" id="sf_total">$0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards por cajero: efectivo, tarjeta, lavados -->
            <div class="row g-3 mt-1">
                <!-- AQUA01 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card cajero-card aqua01">
                        <div class="cajero-header"><i class="bi bi-hdd-rack me-2"></i>AQUA01</div>
                        <div class="card-body py-2 px-3">
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-cash me-1"></i>Efectivo</span>
                                <span class="stat-value efectivo" id="aqua01_efectivo">$0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-credit-card me-1"></i>Tarjeta</span>
                                <span class="stat-value tarjeta" id="aqua01_tarjeta">$0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-box me-1"></i>Lavados Paquete</span>
                                <span class="stat-value paquete" id="aqua01_lavados_paquete">0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-person-check me-1"></i>Lavados Membresía</span>
                                <span class="stat-value membresia" id="aqua01_lavados_membresia">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AQUA02 -->
                <div class="col-lg-4 col-md-6">
                    <div class="card cajero-card aqua02">
                        <div class="cajero-header"><i class="bi bi-hdd-rack me-2"></i>AQUA02</div>
                        <div class="card-body py-2 px-3">
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-cash me-1"></i>Efectivo</span>
                                <span class="stat-value efectivo" id="aqua02_efectivo">$0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-credit-card me-1"></i>Tarjeta</span>
                                <span class="stat-value tarjeta" id="aqua02_tarjeta">$0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-box me-1"></i>Lavados Paquete</span>
                                <span class="stat-value paquete" id="aqua02_lavados_paquete">0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-person-check me-1"></i>Lavados Membresía</span>
                                <span class="stat-value membresia" id="aqua02_lavados_membresia">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOTAL -->
                <div class="col-lg-4 col-md-12">
                    <div class="card cajero-card total">
                        <div class="cajero-header"><i class="bi bi-calculator me-2"></i>Total General</div>
                        <div class="card-body py-2 px-3">
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-cash me-1"></i>Efectivo Total</span>
                                <span class="stat-value efectivo" id="total_efectivo">$0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-credit-card me-1"></i>Tarjeta Total</span>
                                <span class="stat-value tarjeta" id="total_tarjeta">$0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-box me-1"></i>Lavados Paquete</span>
                                <span class="stat-value paquete" id="total_lavados_paquete">0</span>
                            </div>
                            <div class="cajero-stat">
                                <span class="stat-label"><i class="bi bi-person-check me-1"></i>Lavados Membresía</span>
                                <span class="stat-value membresia" id="total_lavados_membresia">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Membresías Activas -->
            <div class="row g-3 mt-1 align-items-stretch">
                <!-- Card total -->
                <div class="col-auto">
                    <div class="mini-membership-card total h-100">
                        <i class="bi bi-star-fill mini-icon"></i>
                        <div>
                            <div class="mini-label">Membresías Activas</div>
                            <div class="mini-count" id="total_active_memberships">0</div>
                        </div>
                    </div>
                </div>
                <!-- Badges por paquete -->
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm px-3 py-2 d-flex flex-row flex-wrap align-items-center gap-3">
                        <div class="membership-badge express">
                            <span class="badge-dot"></span>
                            <span class="badge-label">Express</span>
                            <span class="badge-count" id="count_express">0</span>
                        </div>
                        <div class="membership-badge basico">
                            <span class="badge-dot"></span>
                            <span class="badge-label">Básico</span>
                            <span class="badge-count" id="count_basico">0</span>
                        </div>
                        <div class="membership-badge ultra">
                            <span class="badge-dot"></span>
                            <span class="badge-label">Ultra</span>
                            <span class="badge-count" id="count_ultra">0</span>
                        </div>
                        <div class="membership-badge delux">
                            <span class="badge-dot"></span>
                            <span class="badge-label">Delux</span>
                            <span class="badge-count" id="count_delux">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="row mt-3">
                <!-- Gráfica de ventas por hora -->
                <div class="col-lg-8">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5 class="chart-title"><i class="bi bi-clock me-2 text-muted"></i>Ventas por Hora del Día</h5>
                            <div style="position: relative; height: 300px;">
                                <canvas id="hourlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Membresías vs Paquetes + Métodos de Pago apilados -->
                <div class="col-lg-4 d-flex flex-column gap-3">
                    <div class="card dashboard-card flex-fill">
                        <div class="card-body">
                            <h5 class="chart-title"><i class="bi bi-pie-chart me-2 text-muted"></i>Membresías vs Paquetes</h5>
                            <div style="position: relative; height: 180px;">
                                <canvas id="membershipChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card dashboard-card flex-fill">
                        <div class="card-body">
                            <h5 class="chart-title"><i class="bi bi-credit-card me-2 text-muted"></i>Métodos de Pago</h5>
                            <div style="position: relative; height: 180px;">
                                <canvas id="paymentMethodsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cajeros -->
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5 class="chart-title"><i class="bi bi-person-workspace me-2 text-muted"></i>Cajeros del Día</h5>
                            <div style="position: relative; height: 160px;">
                                <canvas id="cajerosChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script src="{{ asset('assets/js/dashboard.js') }}"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Cargar datos iniciales
                loadDashboardData();
                loadActiveMemberships();
                
                // Actualizar automáticamente cada 5 minutos
                // setInterval(loadDashboardData, 300000);
                
                // Actualizar cuando cambie la fecha
                document.getElementById('dashboard_date').addEventListener('change', function() {
                    loadDashboardData();
                    loadActiveMemberships();
                });
            });

            // Función para cargar membresías activas
            
        </script>
    </main>

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>AquaCar Club</span></strong>. All Rights Reserved
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    @include('layout.footer')

</body>