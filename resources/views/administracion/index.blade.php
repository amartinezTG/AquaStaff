@include('layout.shared')
@include('layout.includes')

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center">
                <img src="/assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        @include('layout.nav-header')
    </header>

    <main id="main" class="main">
        <style>
            .audit-card {
                border-radius: 15px;
                border: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }

            .audit-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            .stat-box {
                background: linear-gradient(135deg, var(--stat-color-start), var(--stat-color-end));
                color: white;
                border-radius: 12px;
                padding: 20px;
                text-align: center;
                margin-bottom: 15px;
            }

            .stat-box.warning {
                --stat-color-start: #f39c12;
                --stat-color-end: #e67e22;
            }

            .stat-box.danger {
                --stat-color-start: #e74c3c;
                --stat-color-end: #c0392b;
            }

            .stat-box.success {
                --stat-color-start: #27ae60;
                --stat-color-end: #229954;
            }

            .stat-box.info {
                --stat-color-start: #3498db;
                --stat-color-end: #2980b9;
            }

            .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .stat-label {
                font-size: 0.9rem;
                opacity: 0.95;
                margin-top: 5px;
            }

            .filter-section {
                background: white;
                border-radius: 12px;
                padding: 25px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                margin-bottom: 25px;
            }

            .table-audit {
                background: white;
                border-radius: 12px;
                overflow: hidden;
            }

            .table-audit thead {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .table-audit tbody tr:hover {
                background-color: #f8f9fa;
                transform: scale(1.01);
                transition: all 0.2s ease;
            }

            .badge-gap {
                padding: 8px 15px;
                border-radius: 20px;
                font-weight: 600;
            }

            .badge-critical {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
                color: white;
            }

            .badge-warning {
                background: linear-gradient(135deg, #f39c12, #e67e22);
                color: white;
            }

            .badge-ok {
                background: linear-gradient(135deg, #27ae60, #229954);
                color: white;
            }

            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            .loading-overlay.active {
                display: flex;
            }

            .loading-spinner {
                text-align: center;
                color: white;
            }

            .loading-spinner .spinner-border {
                width: 4rem;
                height: 4rem;
            }

            .atm-selector {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
            }

            .atm-btn {
                flex: 1;
                padding: 15px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
                font-weight: 600;
            }

            .atm-btn:hover {
                border-color: #667eea;
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            }

            .atm-btn.active {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-color: #667eea;
            }

            .export-btn {
                background: linear-gradient(135deg, #27ae60, #229954);
                border: none;
                color: white;
                padding: 12px 25px;
                border-radius: 25px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .export-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
                color: white;
            }

            .alert-custom {
                border-radius: 12px;
                border: none;
                padding: 20px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            }

            .progress-bar-animated {
                animation: progress-animation 1.5s ease-in-out infinite;
            }

            @keyframes progress-animation {
                0% { background-position: 0 0; }
                100% { background-position: 40px 40px; }
            }

            @media (max-width: 768px) {
                .stat-number {
                    font-size: 1.8rem;
                }
                
                .atm-selector {
                    flex-direction: column;
                }
            }
        </style>

        <div class="pagetitle">
            <h1><i class="bi bi-shield-check"></i> Auditoría de Transacciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Administración</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <!-- Filtros de búsqueda -->
            <div class="filter-section">
                <h5 class="mb-4"><i class="bi bi-funnel"></i> Filtros de Consulta</h5>
                
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-01') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Cajero</label>
                        <div class="atm-selector">
                            <button class="atm-btn" data-atm="AQUA01" onclick="selectATM('AQUA01')">
                                <i class="bi bi-cash-coin"></i> AQUA01
                            </button>
                            <button class="atm-btn" data-atm="AQUA02" onclick="selectATM('AQUA02')">
                                <i class="bi bi-cash-coin"></i> AQUA02
                            </button>
                            <button class="atm-btn active" data-atm="TODOS" onclick="selectATM('TODOS')">
                                <i class="bi bi-collection"></i> TODOS
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button class="btn btn-primary btn-lg" onclick="loadAuditData()">
                            <i class="bi bi-search"></i> Consultar
                        </button>
                        <button class="btn export-btn btn-lg ms-2" onclick="exportData()">
                            <i class="bi bi-download"></i> Exportar CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row" id="stats_container" style="display: none;">
                <div class="col-12 mb-3">
                    <h5><i class="bi bi-graph-up"></i> Estadísticas del Período</h5>
                </div>
            </div>

            <!-- Tabs para cada cajero -->
            <div class="card audit-card" id="results_container" style="display: none;">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="atmTabs" role="tablist">
                        <!-- Tabs se generarán dinámicamente -->
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="atmTabContent">
                        <!-- Contenido de tabs se generará dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Modal para ver detalle de huecos -->
            <div class="modal fade" id="detailModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="modal-title text-white">
                                <i class="bi bi-list-ul"></i> Detalle de Transacciones Faltantes
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="detail_content">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-3">Cargando detalle...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Loading overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner">
                <div class="spinner-border" role="status"></div>
                <p class="mt-3">Procesando datos...</p>
            </div>
        </div>
        <script src="{{ asset('assets/js/administracion.js') }}"></script>

        <script>
            let selectedATM = 'TODOS';

            document.addEventListener('DOMContentLoaded', function() {
                // Configurar fechas por defecto (último mes)
                const today = new Date();
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                
                document.getElementById('start_date').value = lastMonth.toISOString().split('T')[0];
                document.getElementById('end_date').value = today.toISOString().split('T')[0];
            });
        </script>
    </main>

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