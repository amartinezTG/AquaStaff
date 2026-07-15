@include('layout.shared')
@include('layout.includes')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            #dom_table thead th {
                background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                color: #fff;
                font-weight: 600;
                font-size: 0.76rem;
                text-align: center;
                vertical-align: middle;
                white-space: nowrap;
                padding: 10px 8px;
                border: none;
            }
            #dom_table tbody td {
                vertical-align: middle;
                padding: 7px 8px;
                font-size: 0.78rem;
                border-right: 1px solid #e9ecef;
            }
            #dom_table tbody tr:hover { background-color: #e8f0fe; }
            .badge-autorizada  { background-color: #198754; color:#fff; }
            .badge-pendiente   { background-color: #ffc107; color:#212529; }
            .badge-rechazada   { background-color: #dc3545; color:#fff; }
            .badge-normal      { background-color: #6c757d; color:#fff; }
            .badge-prosewash   { background-color: #0d6efd; color:#fff; }
            .dt-button {
                color:#fff !important; border-radius:20px !important;
                font-weight:600 !important; font-size:0.82rem !important;
                padding:7px 18px !important; border:none !important;
            }
            .buttons-excel { background:linear-gradient(135deg,#46b723,#3cb723) !important; }
            .buttons-copy  { background:linear-gradient(135deg,#ffb800,#f59e0b) !important; }
        </style>

        <div class="pagetitle">
            <h1>Procepago — Importación Domiciliaciones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item">Procepago</li>
                    <li class="breadcrumb-item active">Importación Domiciliaciones</li>
                </ol>
            </nav>
        </div>

        <section class="section">

            {{-- Card importar --}}
            <div class="card mb-3" style="border-left:5px solid #0d6efd;">
                <div class="card-body py-3">
                    <h6 class="fw-bold mb-2" style="color:#0d6efd;font-size:.88rem;">
                        <i class="bi bi-cloud-upload me-1"></i>Importar archivo de domiciliaciones Procepago
                    </h6>
                    <p class="text-muted mb-3" style="font-size:.80rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Sube el archivo <code>.xlsx</code> exportado desde Procepago. El sistema sincronizará automáticamente:
                        insertará los nuevos, actualizará los existentes, y eliminará los que ya no aparezcan.
                        Columnas requeridas: <code>Folio, Titular, Importe, Concepto, Referencia, Fecha, Periodicidad, Status, Tipo</code>.
                    </p>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size:.8rem;">Archivo Excel</label>
                            <input type="file" id="archivoInput" class="form-control form-control-sm"
                                accept=".xlsx,.xlsm,.xls">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm w-100 text-white fw-bold" style="background:#0d6efd;"
                                onclick="importar()">
                                <i class="bi bi-arrow-repeat me-1"></i>Sincronizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card" style="border-left:5px solid #0d6efd;">
                <div class="card-body p-3">
                    <p class="text-muted mb-2" style="font-size:.80rem;">
                        <i class="bi bi-info-circle me-1" style="color:#0d6efd;"></i>
                        Domiciliaciones activas sincronizadas desde Procepago. Los registros eliminados en el archivo quedan con soft delete.
                    </p>
                    <table id="dom_table" class="table table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Titular</th>
                                <th>Importe</th>
                                <th>Concepto</th>
                                <th>Referencia</th>
                                <th>Fecha inicio</th>
                                <th>Fecha fin</th>
                                <th>Periodicidad</th>
                                <th>Status</th>
                                <th>Tipo</th>
                                <th>Importado por</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </section>
    </main>

    <footer id="footer" class="footer">
        <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
    </footer>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    @include('layout.footer')

    <script>
    let domTable = null;

    function importar() {
        const file = document.getElementById('archivoInput').files[0];
        if (!file) {
            Swal.fire({ icon: 'warning', title: 'Archivo requerido', text: 'Selecciona un archivo Excel.' });
            return;
        }

        Swal.fire({
            title: 'Sincronizando...',
            html: '<p style="font-size:.85rem;">Leyendo archivo y procesando registros.</p>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        const fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        fd.append('archivo', file);

        $.ajax({
            url: '/procepago/domiciliaciones',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (resp) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sincronización completada',
                    html: `<div style="font-size:.85rem;">
                        <div class="d-flex justify-content-around my-2 flex-wrap gap-2">
                            <div><span class="badge bg-success fs-6">${resp.insertados}</span><br><small>Nuevos</small></div>
                            <div><span class="badge bg-primary fs-6">${resp.actualizados}</span><br><small>Actualizados</small></div>
                            <div><span class="badge bg-info fs-6">${resp.restaurados}</span><br><small>Restaurados</small></div>
                            <div><span class="badge bg-secondary fs-6">${resp.eliminados}</span><br><small>Eliminados</small></div>
                            <div><span class="badge bg-danger fs-6">${resp.errores}</span><br><small>Errores</small></div>
                        </div>
                        ${resp.detalle_errores && resp.detalle_errores.length
                            ? '<small class="text-danger">' + resp.detalle_errores.slice(0, 3).join('<br>') + '</small>'
                            : ''}
                    </div>`,
                    width: 520,
                }).then(() => cargarTabla());
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.error || 'Error al importar.' });
            }
        });
    }

    function cargarTabla() {
        if (domTable) { domTable.destroy(); $('#dom_table tbody').empty(); }

        Swal.fire({ title: 'Cargando...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

        domTable = $('#dom_table').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            paging: true,
            pageLength: 100,
            order: [[0, 'desc']],
            ajax: {
                url: '/procepago/domiciliaciones/table',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                dataSrc: function (json) {
                    Swal.close();
                    return json.data || [];
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los registros.' });
                }
            },
            columns: [
                { data: 'folio',    render: (d, t) => t !== 'display' ? d : `<code style="font-size:.70rem;">${d}</code>` },
                { data: 'titular' },
                { data: 'importe', className: 'text-end fw-bold',
                  render: (d, t) => t !== 'display' ? d : '$' + parseFloat(d).toLocaleString('es-MX', { minimumFractionDigits: 2 }) },
                { data: 'concepto',    render: (d, t) => t !== 'display' ? (d||'') : `<small>${d||'—'}</small>` },
                { data: 'referencia', render: (d, t) => t !== 'display' ? (d||'') : (d ? `<code style="font-size:.68rem;">${d}</code>` : '—') },
                { data: 'fecha_inicio' },
                { data: 'fecha_fin' },
                { data: 'periodicidad', className: 'text-center' },
                {
                    data: 'status',
                    render: (d, t) => {
                        if (t !== 'display') return d || '';
                        const cls = d === 'Autorizada' ? 'badge-autorizada'
                                  : d === 'Rechazada'  ? 'badge-rechazada'
                                  : 'badge-pendiente';
                        return `<span class="badge ${cls}">${d}</span>`;
                    }
                },
                {
                    data: 'tipo',
                    render: (d, t) => {
                        if (t !== 'display') return d || '';
                        if (!d) return '—';
                        const cls = d === 'Prosewash' ? 'badge-prosewash' : 'badge-normal';
                        return `<span class="badge ${cls}">${d}</span>`;
                    }
                },
                { data: 'importado_por_nombre', render: d => d || '—' },
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                    className: 'btn btn-success buttons-excel',
                    filename: 'Procepago_Domiciliaciones',
                    title: 'Domiciliaciones — Procepago',
                    exportOptions: { modifier: { page: 'all', search: 'applied' } }
                },
                {
                    extend: 'copy',
                    text: '<i class="ti ti-copy me-1"></i>Copiar',
                    className: 'btn btn-warning buttons-copy'
                },
            ],
        });
    }

    document.addEventListener('DOMContentLoaded', cargarTabla);
    </script>
</body>
