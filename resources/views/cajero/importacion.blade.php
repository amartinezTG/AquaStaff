@include('layout.shared')
@include('layout.includes')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

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
            .drop-zone {
                border: 3px dashed #2399b7;
                border-radius: 12px;
                padding: 48px 24px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                background: #f8fdff;
            }
            .drop-zone:hover, .drop-zone.dragover {
                background: #e3f6fc;
                border-color: #1a7a91;
            }
            .drop-zone i { font-size: 3rem; color: #2399b7; }
            .drop-zone p  { margin: 12px 0 0; color: #555; font-size: 0.95rem; }

            #resultado-card { display: none; }

            .stat-box {
                border-radius: 10px;
                padding: 16px 20px;
                font-weight: 700;
                font-size: 1.4rem;
                text-align: center;
            }
            .stat-box small { display: block; font-size: 0.75rem; font-weight: 400; margin-top: 4px; }

            #importacion_table thead th {
                background: linear-gradient(135deg, #2399b7 0%, #1a7a91 100%);
                color: #fff;
                font-size: 0.78rem;
                text-align: center;
                padding: 10px 6px;
                white-space: nowrap;
            }
            #importacion_table tbody td {
                font-size: 0.80rem;
                vertical-align: middle;
                padding: 8px 6px;
            }
            .badge-efectivo  { background:#28a745; color:#fff; padding:3px 10px; border-radius:12px; font-size:0.72rem; }
            .badge-tarjeta   { background:#007bff; color:#fff; padding:3px 10px; border-radius:12px; font-size:0.72rem; }
            .monto-col       { font-weight:700; color:#28a745; }
        </style>

        <div class="pagetitle">
            <h1>Importación Procepago</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Cajero</li>
                    <li class="breadcrumb-item active">Importación</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">

                <!-- Card de carga -->
                <div class="col-12">
                    <div class="card" style="border-left:5px solid #2399b7;">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3" style="color:#1a7a91;font-weight:700;">
                                <i class="bi bi-cloud-upload me-2"></i>Cargar Reporte Procepago (.xlsx)
                            </h6>

                            <div class="drop-zone" id="dropZone" onclick="document.getElementById('archivoInput').click()">
                                <i class="bi bi-file-earmark-spreadsheet"></i>
                                <p>Arrastra el archivo aquí o <strong>haz clic para seleccionar</strong></p>
                                <p style="font-size:0.8rem;color:#999;">Solo archivos .xlsx — máx. 20 MB</p>
                            </div>
                            <input type="file" id="archivoInput" accept=".xlsx,.xls" style="display:none">

                            <div id="archivo-seleccionado" class="mt-3" style="display:none;">
                                <div class="alert alert-info d-flex align-items-center justify-content-between py-2">
                                    <span><i class="bi bi-file-earmark-check me-2"></i><strong id="nombre-archivo"></strong></span>
                                    <button class="btn btn-sm btn-warning fw-bold" id="btnImportar" onclick="importar()">
                                        <i class="bi bi-upload me-1"></i> Importar
                                    </button>
                                </div>
                            </div>

                            <!-- Barra de progreso -->
                            <div id="progreso-wrap" class="mt-3" style="display:none;">
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info w-100"></div>
                                </div>
                                <p class="text-center text-muted mt-2" style="font-size:0.85rem;">Procesando archivo, espera...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resultado de importación -->
                <div class="col-12" id="resultado-card">
                    <div class="card" style="border-left:5px solid #28a745;">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3" style="color:#155724;font-weight:700;">
                                <i class="bi bi-check-circle me-2"></i>Resultado de Importación
                            </h6>
                            <div class="row g-3 text-center">
                                <div class="col-md-4">
                                    <div class="stat-box" style="background:#d4edda;color:#155724;">
                                        <span id="res-insertados">0</span>
                                        <small>Registros insertados</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-box" style="background:#fff3cd;color:#856404;">
                                        <span id="res-omitidos">0</span>
                                        <small>Ya existían (omitidos)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-box" style="background:#f8d7da;color:#721c24;">
                                        <span id="res-errores">0</span>
                                        <small>Errores</small>
                                    </div>
                                </div>
                            </div>
                            <div id="errores-lista" class="mt-3" style="display:none;">
                                <h6 class="text-danger">Detalle de errores:</h6>
                                <ul id="errores-ul" class="text-danger small"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de importaciones -->
                @if($importaciones->count())
                <div class="col-12">
                    <div class="card" style="border-left:5px solid #6c757d;">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-3" style="color:#495057;font-weight:700;">
                                <i class="bi bi-clock-history me-2"></i>Últimas Importaciones
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">
                                    <thead style="background:#e9ecef;">
                                        <tr>
                                            <th>Archivo</th>
                                            <th class="text-center">Desde</th>
                                            <th class="text-center">Hasta</th>
                                            <th class="text-center">Registros</th>
                                            <th class="text-center">Monto Total</th>
                                            <th class="text-center">Importado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($importaciones as $imp)
                                        <tr>
                                            <td>{{ $imp->archivo_origen }}</td>
                                            <td class="text-center">{{ $imp->fecha_desde }}</td>
                                            <td class="text-center">{{ $imp->fecha_hasta }}</td>
                                            <td class="text-center fw-bold">{{ number_format($imp->total) }}</td>
                                            <td class="text-center fw-bold text-success">${{ number_format($imp->monto_total, 2) }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($imp->importado_en)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Consulta de registros importados -->
                <div class="col-12">
                    <div class="card" style="border-left:5px solid #ffc107;">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3" style="color:#856404;font-weight:700;">
                                <i class="bi bi-table me-2"></i>Consultar Registros Importados
                            </h6>
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fi" value="{{ now()->startOfMonth()->toDateString() }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha Final</label>
                                    <input type="date" class="form-control" id="ff" value="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Cajero</label>
                                    <select class="form-select" id="cajero-filtro">
                                        <option value="">Todos</option>
                                        <option value="AQUA01">AQUA01</option>
                                        <option value="AQUA02">AQUA02</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn w-100 fw-bold" style="background:linear-gradient(135deg,#ffc107,#f59e0b);border:none;border-radius:25px;" onclick="consultarTabla()">
                                        Consultar
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="importacion_table" class="table table-bordered table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Cajero</th>
                                            <th>Servicio</th>
                                            <th>Forma Pago</th>
                                            <th>Efectivo</th>
                                            <th>Tarjeta</th>
                                            <th>Total</th>
                                            <th>Autorización</th>
                                            <th>Últ. 4</th>
                                            <th>Num Op.</th>
                                            <th>Archivo</th>
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

        <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let archivoFile = null;
        let dtImportacion = null;

        // ── Drag & Drop ──────────────────────────────────────────────
        const dropZone = document.getElementById('dropZone');
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file) seleccionarArchivo(file);
        });

        document.getElementById('archivoInput').addEventListener('change', function () {
            if (this.files[0]) seleccionarArchivo(this.files[0]);
        });

        function seleccionarArchivo(file) {
            archivoFile = file;
            document.getElementById('nombre-archivo').textContent = file.name;
            document.getElementById('archivo-seleccionado').style.display = 'block';
            document.getElementById('resultado-card').style.display = 'none';
        }

        // ── Importar ─────────────────────────────────────────────────
        function importar() {
            if (!archivoFile) return;

            document.getElementById('progreso-wrap').style.display = 'block';
            document.getElementById('btnImportar').disabled = true;

            const fd = new FormData();
            fd.append('_token', csrf);
            fd.append('archivo', archivoFile);

            fetch('/cajero/importacion', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    document.getElementById('progreso-wrap').style.display = 'none';
                    document.getElementById('btnImportar').disabled = false;

                    if (res.error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.error });
                        return;
                    }

                    document.getElementById('res-insertados').textContent = res.insertados.toLocaleString('es-MX');
                    document.getElementById('res-omitidos').textContent   = res.omitidos.toLocaleString('es-MX');
                    document.getElementById('res-errores').textContent    = res.errores.length;
                    document.getElementById('resultado-card').style.display = 'block';

                    if (res.errores.length > 0) {
                        const ul = document.getElementById('errores-ul');
                        ul.innerHTML = res.errores.map(e => '<li>' + e + '</li>').join('');
                        document.getElementById('errores-lista').style.display = 'block';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Importación completa',
                        html: '<b>' + res.insertados.toLocaleString('es-MX') + '</b> insertados &nbsp;|&nbsp; <b>' + res.omitidos.toLocaleString('es-MX') + '</b> omitidos',
                        timer: 3000,
                        showConfirmButton: false
                    });

                    // Recargar tabla si ya está inicializada
                    if (dtImportacion) dtImportacion.ajax.reload();
                })
                .catch(() => {
                    document.getElementById('progreso-wrap').style.display = 'none';
                    document.getElementById('btnImportar').disabled = false;
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar con el servidor.' });
                });
        }

        // ── Tabla de consulta ────────────────────────────────────────
        function consultarTabla() {
            if (dtImportacion) {
                dtImportacion.destroy();
                document.querySelector('#importacion_table tbody').innerHTML = '';
            }

            const fi     = document.getElementById('fi').value;
            const ff     = document.getElementById('ff').value;
            const cajero = document.getElementById('cajero-filtro').value;

            dtImportacion = $('#importacion_table').DataTable({
                layout: {
                    topStart: ['buttons'],
                    bottomStart: ['pageLength', 'info'],
                    bottomEnd: 'paging'
                },
                pageLength: 50,
                order: [[0,'desc'],[1,'desc']],
                buttons: [
                    { extend:'excelHtml5', text:'<i class="ti ti-file-type-xls"></i> Excel',
                      title:'Procepago ' + fi + ' al ' + ff },
                    { extend:'copy',       text:'<i class="ti ti-copy"></i> Copiar' }
                ],
                ajax: {
                    method: 'POST',
                    url: '/cajero/importacion/table',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: { fecha_inicio: fi, fecha_final: ff, cajero: cajero }
                },
                columns: [
                    { data: 'fecha' },
                    { data: 'hora' },
                    { data: 'clave_cajero', render: d => '<span class="fw-bold">' + d + '</span>' },
                    { data: 'servicio' },
                    { data: 'forma_pago', render: d => d === 'EFECTIVO'
                        ? '<span class="badge-efectivo">Efectivo</span>'
                        : '<span class="badge-tarjeta">Tarjeta</span>' },
                    { data: 'monto_efectivo', render: d => d > 0 ? '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:2}) : '-',
                      className: 'text-end' },
                    { data: 'monto_tarjeta',  render: d => d > 0 ? '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:2}) : '-',
                      className: 'text-end' },
                    { data: 'monto_total', className: 'text-end monto-col',
                      render: d => '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:2}) },
                    { data: 'autorizacion', defaultContent: '-' },
                    { data: 'ultimos_4',    defaultContent: '-' },
                    { data: 'num_operacion' },
                    { data: 'archivo_origen', render: d => '<span class="text-muted" style="font-size:0.72rem;">' + d + '</span>' },
                ],
                language: {
                    emptyTable: 'Sin datos — importa un archivo o ajusta el filtro',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Sin registros',
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    paginate: { first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior' }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', consultarTabla);
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
