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

            /* Estilos para membresías activas */
            .membership-package-card {
                background: white;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                border-left: 5px solid;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
                transition: all 0.3s ease;
            }

            .membership-package-card:hover {
                transform: translateX(5px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
            }

            .membership-package-card.express {
                border-left-color: #17a2b8;
            }

            .membership-package-card.basico {
                border-left-color: #28a745;
            }

            .membership-package-card.ultra {
                border-left-color: #6f42c1;
            }

            .membership-package-card.delux {
                border-left-color: #fd7e14;
            }

            .package-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .package-name {
                font-size: 1.2rem;
                font-weight: 600;
                color: #333;
            }

            .package-count {
                font-size: 2rem;
                font-weight: 700;
                color: #007bff;
            }

            .package-badge {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                color: white;
            }

            .package-badge.express { background-color: #17a2b8; }
            .package-badge.basico { background-color: #28a745; }
            .package-badge.ultra { background-color: #6f42c1; }
            .package-badge.delux { background-color: #fd7e14; }

            .total-memberships-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 15px;
                padding: 25px;
                text-align: center;
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }

            .total-memberships-number {
                font-size: 3.5rem;
                font-weight: 700;
                margin: 10px 0;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .total-memberships-label {
                font-size: 1.1rem;
                opacity: 0.95;
                font-weight: 500;
            }

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

        <div class="pagetitle">
            <h1>Dashboard AquaCar</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <!-- Selector de fecha -->
            <div class="row">
                <div class="col-12">
                    <div class="date-selector">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label for="dashboard_date" class="form-label">Fecha de consulta:</label>
                                <input type="date" class="form-control" id="dashboard_date" 
                                       value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button class="btn refresh-btn" onclick="loadDashboardData()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="text-muted">
                                    <small>Última actualización: <span id="last-update">--:--</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- NUEVA SECCIÓN: Membresías Activas -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="card-title mb-0 text-white">
                                <i class="bi bi-card-checklist me-2"></i>Membresías Activas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Total de Membresías -->
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="total-memberships-card">
                                        <i class="bi bi-star-fill" style="font-size: 2rem; opacity: 0.9;"></i>
                                        <div class="total-memberships-label">Total Membresías Activas</div>
                                        <div class="total-memberships-number" id="total_active_memberships">0</div>
                                        <small style="opacity: 0.9;">Vigentes al día de hoy</small>
                                    </div>
                                </div>

                                <!-- Desglose por paquetes -->
                                <div class="col-lg-8 col-md-6">
                                    <div class="row" id="membership_packages_container">
                                        <!-- Express -->
                                        <div class="col-md-6">
                                            <div class="membership-package-card express">
                                                <div class="package-header">
                                                    <div>
                                                        <span class="package-badge express">Express</span>
                                                        <div class="package-name mt-2">Express</div>
                                                    </div>
                                                    <div class="package-count" id="count_express">0</div>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-speedometer2"></i> Servicio rápido
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Básico -->
                                        <div class="col-md-6">
                                            <div class="membership-package-card basico">
                                                <div class="package-header">
                                                    <div>
                                                        <span class="package-badge basico">Básico</span>
                                                        <div class="package-name mt-2">Básico</div>
                                                    </div>
                                                    <div class="package-count" id="count_basico">0</div>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-check-circle"></i> Servicio esencial
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ultra -->
                                        <div class="col-md-6">
                                            <div class="membership-package-card ultra">
                                                <div class="package-header">
                                                    <div>
                                                        <span class="package-badge ultra">Ultra</span>
                                                        <div class="package-name mt-2">Ultra</div>
                                                    </div>
                                                    <div class="package-count" id="count_ultra">0</div>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-gem"></i> Servicio premium
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delux -->
                                        <div class="col-md-6">
                                            <div class="membership-package-card delux">
                                                <div class="package-header">
                                                    <div>
                                                        <span class="package-badge delux">Delux</span>
                                                        <div class="package-name mt-2">Delux</div>
                                                    </div>
                                                    <div class="package-count" id="count_delux">0</div>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-award"></i> Servicio de lujo
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="row">
                <!-- Gráfica de ventas por hora -->
                <div class="col-lg-8">
                    <div class="chart-container">
                        <h5 class="chart-title">Ventas por Hora del Día</h5>
                        <div style="position: relative; height: 350px;">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Distribución Membresías vs Paquetes -->
                <div class="col-lg-4">
                    <div class="chart-container">
                        <h5 class="chart-title">Membresías vs Paquetes</h5>
                        <div style="position: relative; height: 350px;">
                            <canvas id="membershipChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Top Cajeros del Día -->
                <div class="col-lg-6">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-workspace me-2"></i>Top Cajeros del Día
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="cajerosChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <div class="col-lg-6">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-credit-card me-2"></i>Métodos de Pago
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="paymentMethodsChart"></canvas>
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