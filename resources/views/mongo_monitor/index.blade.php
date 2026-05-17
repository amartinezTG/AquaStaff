@include('layout.shared')
@include('layout.includes')

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
            .mongo-card {
                border-radius: 15px;
                border: none;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .progress { height: 28px; border-radius: 8px; }
            .progress-bar { font-size: 14px; font-weight: 600; transition: width 0.8s ease; }
            .collection-row:hover { background: #f8f9fa; }
            .badge-count { font-size: 11px; }
        </style>

        <div class="pagetitle">
            <h1><i class="bi bi-database-fill me-2" style="color:#0d6efd"></i>Monitor MongoDB Atlas</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item active">MongoDB Monitor</li>
                </ol>
            </nav>
        </div>

        <section class="section">

            {{-- ALERTA DE LÍMITE --}}
            <div id="alertaLimite" class="alert d-none mb-3" role="alert"></div>

            {{-- RESUMEN GENERAL --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card mongo-card">
                        <div class="card-body pt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    Base de datos: <span id="dbName" class="text-primary fw-bold">—</span>
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="cargarStats()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                                </button>
                            </div>

                            {{-- Barra de uso --}}
                            <p class="mb-1 text-muted small">
                                Uso del almacenamiento — límite plan gratuito: <strong>512 MB</strong>
                            </p>
                            <div class="progress mb-3">
                                <div id="barraUso" class="progress-bar bg-success" role="progressbar"
                                     style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    0%
                                </div>
                            </div>

                            {{-- Tarjetas de resumen --}}
                            <div class="row text-center g-3">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="small text-muted">Datos (sin índices)</div>
                                        <div class="fs-5 fw-bold text-info" id="dataSize">—</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="small text-muted">Almacenamiento</div>
                                        <div class="fs-5 fw-bold text-primary" id="storageSize">—</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="small text-muted">Índices</div>
                                        <div class="fs-5 fw-bold text-warning" id="indexSize">—</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="small text-muted">Total usado</div>
                                        <div class="fs-5 fw-bold text-danger" id="totalSize">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA DE COLECCIONES --}}
            <div class="row">
                <div class="col-12">
                    <div class="card mongo-card">
                        <div class="card-body">
                            <h5 class="card-title">Detalle por colección</h5>
                            <div id="loadingCollections" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Consultando MongoDB Atlas...</p>
                            </div>
                            <div id="tablaContainer" class="d-none">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Colección</th>
                                                <th class="text-end">Documentos</th>
                                                <th class="text-end">Datos</th>
                                                <th class="text-end">Almacenamiento</th>
                                                <th class="text-end">Índices</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Avg. doc</th>
                                                <th>Uso relativo</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyCollections"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="errorMsg" class="d-none alert alert-danger mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </main>

    @include('layout.footer')

    <script>
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function cargarStats() {
            document.getElementById('loadingCollections').classList.remove('d-none');
            document.getElementById('tablaContainer').classList.add('d-none');
            document.getElementById('errorMsg').classList.add('d-none');
            document.getElementById('alertaLimite').classList.add('d-none');

            fetch('/mongo-monitor/stats')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('loadingCollections').classList.add('d-none');

                    if (!data.success) {
                        document.getElementById('errorMsg').textContent = 'Error: ' + data.message;
                        document.getElementById('errorMsg').classList.remove('d-none');
                        return;
                    }

                    const s = data.summary;
                    const pct = s.used_percent;

                    // Nombre DB
                    document.getElementById('dbName').textContent = data.db_name;

                    // Barra de uso
                    const barra = document.getElementById('barraUso');
                    barra.style.width = Math.min(pct, 100) + '%';
                    barra.textContent = pct + '%';
                    barra.className = 'progress-bar ' + (pct >= 90 ? 'bg-danger' : pct >= 70 ? 'bg-warning' : 'bg-success');
                    barra.setAttribute('aria-valuenow', pct);

                    // Tarjetas
                    document.getElementById('dataSize').textContent    = formatBytes(s.data_size);
                    document.getElementById('storageSize').textContent  = formatBytes(s.storage_size);
                    document.getElementById('indexSize').textContent    = formatBytes(s.index_size);
                    document.getElementById('totalSize').textContent    = formatBytes(s.total_size);

                    // Alerta
                    const alerta = document.getElementById('alertaLimite');
                    if (pct >= 90) {
                        alerta.className = 'alert alert-danger';
                        alerta.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Atención:</strong> Has usado el ' + pct + '% del almacenamiento gratuito. Limpia colecciones pronto.';
                        alerta.classList.remove('d-none');
                    } else if (pct >= 70) {
                        alerta.className = 'alert alert-warning';
                        alerta.innerHTML = '<i class="bi bi-exclamation-circle-fill me-2"></i>Has usado el ' + pct + '% del almacenamiento gratuito (512 MB). Considera limpiar registros antiguos.';
                        alerta.classList.remove('d-none');
                    }

                    // Tabla de colecciones
                    const tbody = document.getElementById('tbodyCollections');
                    tbody.innerHTML = '';
                    data.collections.forEach(col => {
                        const pctCol = s.total_size > 0
                            ? Math.round((col.total_size / s.total_size) * 100)
                            : 0;
                        tbody.innerHTML += `
                            <tr class="collection-row">
                                <td><code class="text-dark fw-bold">${col.name}</code></td>
                                <td class="text-end"><span class="badge bg-secondary badge-count">${col.count.toLocaleString()}</span></td>
                                <td class="text-end text-info">${formatBytes(col.data_size)}</td>
                                <td class="text-end text-primary">${formatBytes(col.storage_size)}</td>
                                <td class="text-end text-warning">${formatBytes(col.index_size)}</td>
                                <td class="text-end text-danger fw-bold">${formatBytes(col.total_size)}</td>
                                <td class="text-end text-muted small">${formatBytes(col.avg_obj_size)}</td>
                                <td style="min-width:120px">
                                    <div class="progress" style="height:16px">
                                        <div class="progress-bar bg-info" style="width:${pctCol}%;font-size:11px">${pctCol > 8 ? pctCol + '%' : ''}</div>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    document.getElementById('tablaContainer').classList.remove('d-none');
                })
                .catch(err => {
                    document.getElementById('loadingCollections').classList.add('d-none');
                    document.getElementById('errorMsg').textContent = 'Error de conexión: ' + err.message;
                    document.getElementById('errorMsg').classList.remove('d-none');
                });
        }

        document.addEventListener('DOMContentLoaded', cargarStats);
    </script>
</body>
