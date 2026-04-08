@include('layout.shared')
@include('layout.includes')

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="/dashboard" class="logo d-flex align-items-center">
                <img src="assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        @include('layout.nav-header')
    </header>
   
    <main id="main" class="main">
        <style>
            .stat-card {
                border-radius: 12px;
                padding: 18px 20px;
                text-align: center;
                box-shadow: 0 2px 8px rgba(0,0,0,.08);
            }
            .stat-card .num  { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
            .stat-card .lbl  { font-size: 0.75rem; margin-top: 4px; opacity: .8; }
            .stat-card .sub  { font-size: 0.9rem; font-weight: 600; margin-top: 2px; }

            .tab-btn { cursor:pointer; padding:8px 18px; border-radius:20px; font-size:.82rem;
                       font-weight:600; border:2px solid #dee2e6; background:#fff; transition:all .2s; }
            .tab-btn.active { border-color:#2399b7; background:#2399b7; color:#fff; }
            .tab-btn:hover:not(.active) { border-color:#2399b7; color:#2399b7; }

            .diff-table thead th {
                background: linear-gradient(135deg,#2399b7,#1a7a91);
                color:#fff; font-size:.78rem; text-align:center; padding:10px 6px;
            }
            .diff-table tbody td { font-size:.80rem; vertical-align:middle; padding:7px 6px; }
            .diff-table tbody tr:hover { background:#f0faff; }

            .badge-ef  { background:#28a745; color:#fff; padding:2px 9px; border-radius:10px; font-size:.72rem; }
            .badge-tar { background:#007bff; color:#fff; padding:2px 9px; border-radius:10px; font-size:.72rem; }
            .neg { color:#dc3545; font-weight:700; }
            .pos { color:#28a745; font-weight:700; }

            #loader { display:none; text-align:center; padding:40px; color:#2399b7; }
        </style>

        <div class="pagetitle">
            <h1>Análisis Procepago vs AquaAdmin</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item">Cajero</li>
                    <li class="breadcrumb-item active">Análisis Procepago</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">

                <!-- Filtros -->
                <div class="col-12">
                    <div class="card" style="border-left:5px solid #ffc107;">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fi"
                                        value="{{ now()->startOfMonth()->toDateString() }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha Final</label>
                                    <input type="date" class="form-control" id="ff"
                                        value="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Cajero</label>
                                    <select class="form-select" id="cajero">
                                        <option value="">Todos</option>
                                        <option value="AQUA01">AQUA01</option>
                                        <option value="AQUA02">AQUA02</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn w-100 fw-bold" onclick="analizar()"
                                        style="background:linear-gradient(135deg,#ffc107,#f59e0b);border:none;border-radius:25px;">
                                        <i class="bi bi-search me-1"></i> Analizar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loader -->
                <div class="col-12" id="loader">
                    <div class="card"><div class="card-body py-5 text-center">
                        <div class="spinner-border text-info mb-3" style="width:3rem;height:3rem;"></div>
                        <p class="text-muted">Cruzando datos Procepago vs AquaAdmin...</p>
                    </div></div>
                </div>

                <!-- Cards resumen -->
                <div class="col-12" id="resumen-section" style="display:none;">
                    <div class="row g-3 mb-2">
                        <div class="col-md-3">
                            <div class="stat-card" style="background:#e3f6fc;color:#1a7a91;">
                                <div class="lbl">Total Procepago</div>
                                <div class="num" id="res-total-pp">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background:#e8f5e9;color:#2e7d32;">
                                <div class="lbl">Total AquaAdmin</div>
                                <div class="num" id="res-total-aqua">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background:#fce4ec;color:#c62828;">
                                <div class="lbl">Solo en AquaAdmin (sin Procepago)</div>
                                <div class="num" id="res-aqua-n">-</div>
                                <div class="sub" id="res-aqua-m">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background:#fff3e0;color:#e65100;">
                                <div class="lbl">Solo en Procepago (sin AquaAdmin)</div>
                                <div class="num" id="res-pp-n">-</div>
                                <div class="sub" id="res-pp-m">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="stat-card" style="background:#f3e5f5;color:#6a1b9a;">
                                <div class="lbl">Transacciones con monto diferente</div>
                                <div class="num" id="res-dif-n">-</div>
                                <div class="sub" id="res-dif-m">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card" style="background:#e8eaf6;color:#283593;">
                                <div class="lbl">Diferencia neta (Procepago − AquaAdmin)</div>
                                <div class="num" id="res-dif-neta">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card" style="background:#f1f8e9;color:#33691e;">
                                <div class="lbl">Transacciones cruzadas sin diferencia</div>
                                <div class="num" id="res-ok">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs de detalle -->
                <div class="col-12" id="detalle-section" style="display:none;">
                    <div class="card" style="border-left:5px solid #2399b7;">
                        <div class="card-body p-4">
                            <div class="d-flex gap-2 flex-wrap mb-4">
                                <button class="tab-btn active" onclick="showTab('tab-aqua', this)">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Solo en AquaAdmin <span class="badge bg-danger ms-1" id="badge-aqua">0</span>
                                </button>
                                <button class="tab-btn" onclick="showTab('tab-pp', this)">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Solo en Procepago <span class="badge bg-warning text-dark ms-1" id="badge-pp">0</span>
                                </button>
                                <button class="tab-btn" onclick="showTab('tab-dif', this)">
                                    <i class="bi bi-arrow-left-right me-1"></i>
                                    Monto diferente <span class="badge bg-purple ms-1" id="badge-dif" style="background:#6a1b9a!important;">0</span>
                                </button>
                            </div>

                            <!-- Tab: Solo en AquaAdmin -->
                            <div id="tab-aqua">
                                <p class="text-muted small mb-2">Transacciones registradas en AquaAdmin con cobro, pero <strong>sin registro en Procepago</strong>. Posibles cobros no procesados por TPV.</p>
                                <div class="table-responsive">
                                    <table id="tbl-aqua" class="table table-bordered table-hover diff-table w-100">
                                        <thead><tr>
                                            <th>Fecha</th><th>Hora</th><th>Cajero</th>
                                            <th>Total AquaAdmin</th><th>Total Procepago</th><th>Diferencia</th>
                                        </tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab: Solo en Procepago -->
                            <div id="tab-pp" style="display:none;">
                                <p class="text-muted small mb-2">Transacciones en Procepago <strong>sin registro en AquaAdmin</strong>. Posibles transacciones manuales o de sincronización pendiente.</p>
                                <div class="table-responsive">
                                    <table id="tbl-pp" class="table table-bordered table-hover diff-table w-100">
                                        <thead><tr>
                                            <th>Fecha</th><th>Hora</th><th>Cajero</th>
                                            <th>Servicio</th><th>Forma Pago</th>
                                            <th>Total AquaAdmin</th><th>Total Procepago</th><th>Diferencia</th>
                                        </tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab: Monto diferente -->
                            <div id="tab-dif" style="display:none;">
                                <p class="text-muted small mb-2">
                                    Transacciones que <strong>existen en ambos sistemas</strong> pero el precio registrado en AquaAdmin no coincide con lo cobrado por Procepago.<br>
                                    <span class="text-danger fw-semibold">Precio Aqua</span> = precio del servicio en AquaAdmin &nbsp;|&nbsp;
                                    <span class="text-primary fw-semibold">Cobrado Aqua</span> = lo que el cliente entregó menos cambio &nbsp;|&nbsp;
                                    <span class="text-success fw-semibold">Procepago</span> = lo cobrado por el TPV
                                </p>
                                <div class="table-responsive">
                                    <table id="tbl-dif" class="table table-bordered table-hover diff-table w-100">
                                        <thead><tr>
                                            <th>Num Op.</th><th>Fecha</th><th>Hora</th><th>Cajero</th>
                                            <th>Servicio</th><th>Forma Pago</th>
                                            <th>Precio Aqua</th><th>Cobrado Aqua</th><th>Procepago</th>
                                            <th>Dif. Precio</th><th>Dif. Cobrado</th>
                                        </tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

        <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let dtAqua, dtPP, dtDif;
        let _data = null;

        const fmt = v => v === null || v === '-' ? '-' :
            '$' + parseFloat(v).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});

        const difCell = v => {
            if (v === null || v === '-') return '-';
            const n = parseFloat(v);
            const f = fmt(Math.abs(n));
            return n < 0
                ? '<span class="neg">-' + f.replace('$','') + '</span>'
                : '<span class="pos">+' + f + '</span>';
        };

        function showTab(id, btn) {
            ['tab-aqua','tab-pp','tab-dif'].forEach(t => document.getElementById(t).style.display = 'none');
            document.getElementById(id).style.display = 'block';
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function analizar() {
            const fi     = document.getElementById('fi').value;
            const ff     = document.getElementById('ff').value;
            const cajero = document.getElementById('cajero').value;

            document.getElementById('loader').style.display = 'block';
            document.getElementById('resumen-section').style.display = 'none';
            document.getElementById('detalle-section').style.display = 'none';

            fetch('/cajero/analisis-procepago', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                body: JSON.stringify({ fecha_inicio: fi, fecha_final: ff, cajero: cajero })
            })
            .then(r => r.json())
            .then(res => {
                document.getElementById('loader').style.display = 'none';
                _data = res;
                renderResumen(res.resumen);
                renderTablas(res);
                document.getElementById('resumen-section').style.display = 'block';
                document.getElementById('detalle-section').style.display = 'block';
            })
            .catch(() => {
                document.getElementById('loader').style.display = 'none';
                Swal.fire({icon:'error', title:'Error', text:'No se pudo conectar con el servidor.'});
            });
        }

        function renderResumen(r) {
            const difNeta = r.total_pp - r.total_aqua;
            document.getElementById('res-total-pp').textContent   = fmt(r.total_pp);
            document.getElementById('res-total-aqua').textContent = fmt(r.total_aqua);
            document.getElementById('res-aqua-n').textContent     = r.solo_en_aqua_n.toLocaleString('es-MX');
            document.getElementById('res-aqua-m').textContent     = fmt(r.solo_en_aqua_monto);
            document.getElementById('res-pp-n').textContent       = r.solo_en_pp_n.toLocaleString('es-MX');
            document.getElementById('res-pp-m').textContent       = fmt(r.solo_en_pp_monto);
            document.getElementById('res-dif-n').textContent      = r.dif_monto_n.toLocaleString('es-MX');
            document.getElementById('res-dif-m').textContent      = fmt(r.dif_monto_total);
            document.getElementById('res-dif-neta').textContent   = (difNeta >= 0 ? '+' : '') + fmt(difNeta);
            document.getElementById('res-dif-neta').className     = 'num ' + (difNeta >= 0 ? 'pos' : 'neg');

            document.getElementById('badge-aqua').textContent = r.solo_en_aqua_n;
            document.getElementById('badge-pp').textContent   = r.solo_en_pp_n;
            document.getElementById('badge-dif').textContent  = r.dif_monto_n;
        }

        function renderTablas(res) {
            // Destruir DataTables previas
            if (dtAqua) { dtAqua.destroy(); document.querySelector('#tbl-aqua tbody').innerHTML = ''; }
            if (dtPP)   { dtPP.destroy();   document.querySelector('#tbl-pp tbody').innerHTML = ''; }
            if (dtDif)  { dtDif.destroy();  document.querySelector('#tbl-dif tbody').innerHTML = ''; }

            const dtConfig = (extraCols) => ({
                layout: { topStart:['buttons'], bottomStart:['pageLength','info'], bottomEnd:'paging' },
                pageLength: 25,
                order: [[0,'asc']],
                buttons: [
                    { extend:'excelHtml5', text:'<i class="ti ti-file-type-xls"></i> Excel' },
                    { extend:'copy',       text:'<i class="ti ti-copy"></i> Copiar' }
                ],
                language: { emptyTable:'Sin diferencias en este período',
                    info:'_START_ a _END_ de _TOTAL_', search:'Buscar:',
                    lengthMenu:'Mostrar _MENU_',
                    paginate:{first:'Primero',last:'Último',next:'Siguiente',previous:'Anterior'} }
            });

            // ── Tabla: Solo en AquaAdmin ──
            res.solo_en_aqua.forEach(r => {
                document.querySelector('#tbl-aqua tbody').insertAdjacentHTML('beforeend',
                    `<tr>
                        <td>${r.fecha}</td><td>${r.hora}</td>
                        <td class="text-center fw-bold">${r.cajero}</td>
                        <td class="text-end">${fmt(r.total_aqua)}</td>
                        <td class="text-center text-muted">-</td>
                        <td class="text-end">${difCell(r.diferencia)}</td>
                    </tr>`);
            });
            dtAqua = $('#tbl-aqua').DataTable(dtConfig([]));

            // ── Tabla: Solo en Procepago ──
            res.solo_en_procepago.forEach(r => {
                const badge = r.forma_pago === 'EFECTIVO'
                    ? '<span class="badge-ef">Efectivo</span>'
                    : '<span class="badge-tar">Tarjeta</span>';
                document.querySelector('#tbl-pp tbody').insertAdjacentHTML('beforeend',
                    `<tr>
                        <td>${r.fecha}</td><td>${r.hora}</td>
                        <td class="text-center fw-bold">${r.cajero}</td>
                        <td>${r.servicio}</td>
                        <td class="text-center">${badge}</td>
                        <td class="text-center text-muted">-</td>
                        <td class="text-end">${fmt(r.total_pp)}</td>
                        <td class="text-end">${difCell(r.diferencia)}</td>
                    </tr>`);
            });
            dtPP = $('#tbl-pp').DataTable(dtConfig([]));

            // ── Tabla: Monto diferente ──
            res.diferencia_monto.forEach(r => {
                const badge = r.forma_pago === 'EFECTIVO'
                    ? '<span class="badge-ef">Efectivo</span>'
                    : '<span class="badge-tar">Tarjeta</span>';
                document.querySelector('#tbl-dif tbody').insertAdjacentHTML('beforeend',
                    `<tr>
                        <td class="text-center">${r.id}</td>
                        <td>${r.fecha}</td><td>${r.hora}</td>
                        <td class="text-center fw-bold">${r.cajero}</td>
                        <td>${r.servicio}</td>
                        <td class="text-center">${badge}</td>
                        <td class="text-end text-danger fw-bold">${fmt(r.precio_aqua)}</td>
                        <td class="text-end text-primary">${fmt(r.cobrado_aqua)}</td>
                        <td class="text-end text-success fw-bold">${fmt(r.total_pp)}</td>
                        <td class="text-end">${difCell(r.dif_precio)}</td>
                        <td class="text-end">${difCell(r.dif_cobrado)}</td>
                    </tr>`);
            });
            dtDif = $('#tbl-dif').DataTable(dtConfig([]));
        }

        document.addEventListener('DOMContentLoaded', analizar);
        </script>

    </main>

    <footer id="footer" class="footer">
        <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
    </footer>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    @include('layout.footer')
</body>
