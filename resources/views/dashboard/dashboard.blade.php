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
            /* =============================================
               PALETA PROFESIONAL — acento #1a56db (azul)
               fondo cards blanco, textos #1e293b / #64748b
            ============================================= */
            :root {
                --accent:      #1a56db;
                --accent-dark: #1040a8;
                --text-main:   #1e293b;
                --text-muted:  #64748b;
                --border:      #e2e8f0;
                --bg-subtle:   #f8fafc;
                --shadow-sm:   0 1px 4px rgba(0,0,0,0.07);
                --shadow-md:   0 4px 12px rgba(0,0,0,0.09);
            }

            /* Cards base */
            .dashboard-card {
                border-radius: 10px;
                border: 1px solid var(--border);
                box-shadow: var(--shadow-sm);
                background: #fff;
                transition: box-shadow 0.2s ease;
            }
            .dashboard-card:hover {
                box-shadow: var(--shadow-md);
            }

            /* KPI cards — borde izquierdo de acento, fondo blanco */
            .metric-card {
                background: #fff;
                color: var(--text-main);
                border-left: 4px solid var(--accent) !important;
            }
            .metric-card.sales       { border-left-color: #0f766e !important; }
            .metric-card.revenue     { border-left-color: var(--accent) !important; }
            .metric-card.memberships { border-left-color: #7c3aed !important; }
            .metric-card.avg-ticket  { border-left-color: #b45309 !important; }

            .metric-value {
                font-size: 2.2rem;
                font-weight: 700;
                margin: 0;
                color: var(--text-main);
            }
            .metric-label {
                font-size: 0.8rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: var(--text-muted);
                margin-bottom: 4px;
            }
            .metric-change { font-size: 0.78rem; color: var(--text-muted); }
            .metric-icon   { font-size: 2.4rem; opacity: 0.12; color: var(--text-main); }

            /* Botón actualizar */
            .refresh-btn {
                background: var(--accent);
                border: none;
                border-radius: 6px;
                color: white;
                padding: 6px 16px;
                font-weight: 600;
                font-size: 0.85rem;
                transition: background 0.2s ease;
            }
            .refresh-btn:hover { background: var(--accent-dark); color: white; }

            /* Títulos de sección dentro de cards */
            .chart-title, .card-section-title {
                font-size: 0.82rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: var(--text-muted);
                margin-bottom: 14px;
            }

            /* Tabla de servicios */
            #servicios_table thead th {
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: var(--text-muted);
                border-bottom: 2px solid var(--border);
                background: var(--bg-subtle);
                padding: 10px 12px;
            }
            #servicios_table tbody td {
                font-size: 0.83rem;
                color: var(--text-main);
                padding: 9px 12px;
                vertical-align: middle;
                border-bottom: 1px solid var(--border);
            }
            #servicios_table tbody tr:last-child td { border-bottom: none; }
            #servicios_table tbody tr:hover td { background: var(--bg-subtle); }
            #servicios_table tfoot td {
                font-size: 0.83rem;
                font-weight: 700;
                color: var(--text-main);
                padding: 10px 12px;
                border-top: 2px solid var(--border);
                background: var(--bg-subtle);
            }

            /* Cards cajeros */
            .cajero-card {
                border-radius: 10px;
                border: 1px solid var(--border);
                box-shadow: var(--shadow-sm);
                overflow: hidden;
                transition: box-shadow 0.2s ease;
            }
            .cajero-card:hover { box-shadow: var(--shadow-md); }
            .cajero-card .cajero-header {
                padding: 10px 16px;
                font-weight: 700;
                font-size: 0.82rem;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: white;
                background: var(--text-main);
            }
            .cajero-card.aqua01 .cajero-header { background: #1e3a5f; }
            .cajero-card.aqua02 .cajero-header { background: #2d4a6e; }
            .cajero-card.total  .cajero-header { background: #0f766e; }
            .cajero-stat {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid var(--border);
                font-size: 0.83rem;
            }
            .cajero-stat:last-child { border-bottom: none; }
            .cajero-stat .stat-label { color: var(--text-muted); }
            .cajero-stat .stat-value { font-weight: 700; color: var(--text-main); }
            .cajero-stat .stat-value.efectivo  { color: #0f766e; }
            .cajero-stat .stat-value.tarjeta   { color: var(--accent); }
            .cajero-stat .stat-value.membresia { color: #7c3aed; }
            .cajero-stat .stat-value.paquete   { color: #b45309; }

            /* Membresías activas — card total */
            .mini-membership-card {
                border-radius: 10px;
                padding: 16px 20px;
                color: white;
                display: flex;
                align-items: center;
                gap: 14px;
                background: #1e3a5f;
            }
            .mini-membership-card .mini-icon   { font-size: 2rem; opacity: 0.6; }
            .mini-membership-card .mini-label  { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; opacity: 0.85; margin-bottom: 2px; }
            .mini-membership-card .mini-count  { font-size: 2rem; font-weight: 700; line-height: 1; }

            /* Badges por paquete */
            .membership-badge {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 14px;
                border-radius: 8px;
                background: var(--bg-subtle);
                border: 1px solid var(--border);
            }
            .membership-badge .badge-dot   { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
            .membership-badge .badge-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; flex: 1; }
            .membership-badge .badge-count { font-size: 1rem; font-weight: 700; color: var(--text-main); }
            .membership-badge.express .badge-dot { background: var(--accent); }
            .membership-badge.basico  .badge-dot { background: #0f766e; }
            .membership-badge.ultra   .badge-dot { background: #7c3aed; }
            .membership-badge.delux   .badge-dot { background: #b45309; }

            /* Shimmer */
            .loading-skeleton {
                background: linear-gradient(90deg, #f0f0f0 25%, transparent 37%, #f0f0f0 63%);
                background-size: 400% 100%;
                animation: shimmer 1.5s ease-in-out infinite;
            }
            @keyframes shimmer {
                0%   { background-position: 100% 0; }
                100% { background-position: -100% 0; }
            }

            @media (max-width: 768px) {
                .metric-value { font-size: 1.8rem; }
                .metric-icon  { font-size: 1.8rem; }
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

            <!-- Fila 2: Tabla servicios + Membresías activas -->
            <div class="row g-3 mt-1 align-items-stretch">
                <!-- Tabla servicios col-8 -->
                <div class="col-lg-8">
                    <div class="card dashboard-card h-100">
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

                <!-- Membresías activas col-4 -->
                <div class="col-lg-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body d-flex flex-column">
                            <h6 class="fw-bold mb-3"><i class="bi bi-star-fill me-2 text-warning"></i>Membresías Activas</h6>
                            <!-- Total grande -->
                            <div class="text-center mb-3">
                                <div class="mini-membership-card total d-inline-flex mx-auto" style="width:100%; justify-content:center; padding: 18px 20px;">
                                    <i class="bi bi-people-fill mini-icon" style="font-size:2.2rem;"></i>
                                    <div>
                                        <div class="mini-label" style="font-size:0.85rem; font-weight:600; opacity:1;">Total Activas</div>
                                        <div class="mini-count" id="total_active_memberships" style="font-size:2.4rem;">0</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Badges por paquete -->
                            <div class="d-flex flex-column gap-2 mt-auto">
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
                </div>
            </div>

            <!-- Fila 3: Cards por cajero -->
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

            <!-- Fila 4: Gráfica ingresos por hora + lavados por hora -->
            <div class="row g-3 mt-1 align-items-stretch">
                <div class="col-lg-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <h5 class="chart-title">Ingresos por Hora</h5>
                            <div style="position: relative; height: 250px;">
                                <canvas id="hourlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <h5 class="chart-title">Lavados por Hora</h5>
                            <div style="position: relative; height: 250px;">
                                <canvas id="hourlyLavadosChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 5: Cajeros del día + Donuts -->
            <div class="row g-3 mt-1 align-items-stretch">
                <div class="col-lg-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <h5 class="chart-title">Cajeros del Día</h5>
                            <div style="position: relative; height: 220px;">
                                <canvas id="cajerosChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <h5 class="chart-title">Membresías vs Paquetes</h5>
                            <div style="position: relative; height: 220px;">
                                <canvas id="membershipChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body">
                            <h5 class="chart-title">Métodos de Pago</h5>
                            <div style="position: relative; height: 220px;">
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