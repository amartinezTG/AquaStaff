@include('layout.shared')
@include('layout.includes')

<style>
    #PromocionesTable thead th {
        background-color: #198754 !important;
        color: #ffffff !important;
    }
    .badge-sync-si  { background-color: #198754; }
    .badge-sync-no  { background-color: #dc3545; }
    .badge-type     { background-color: #0d6efd; }
    .btn-accion { padding: 3px 8px; font-size: 12px; }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        <div class="pagetitle">
            <h1>Promociones</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Promociones</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-left: 5px solid #198754;">
                        <div class="card-body p-4">

                            {{-- Tabs de navegación --}}
                            <ul class="nav nav-tabs mb-4" id="promoTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-ind-btn"
                                            data-bs-toggle="tab" data-bs-target="#tab-individual"
                                            type="button" role="tab">
                                        <i class="bi bi-tag me-1"></i>Promociones
                                        <span class="badge bg-secondary ms-1" id="totalCount">—</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-bulk-btn"
                                            data-bs-toggle="tab" data-bs-target="#tab-bulk"
                                            type="button" role="tab">
                                        <i class="bi bi-qr-code me-1"></i>QR Masivo
                                        <span class="badge bg-primary ms-1" id="bulkCount">—</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">

                                {{-- ═══ TAB 1: Promociones individuales ═══ --}}
                                <div class="tab-pane fade show active" id="tab-individual" role="tabpanel">
                                    <div class="d-flex justify-content-end mb-3">
                                        <button class="btn btn-success btn-sm" onclick="$('#modalNueva').modal('show')">
                                            <i class="bi bi-plus-circle me-1"></i>Nueva Promoción
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="PromocionesTable"
                                               class="table table-striped table-hover table-bordered w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Código</th>
                                                    <th>Tipo</th>
                                                    <th>Precio</th>
                                                    <th>Usos</th>
                                                    <th>Estatus</th>
                                                    <th>Expiración</th>
                                                    <th>Sincronizado</th>
                                                    <th>Última Sync</th>
                                                    <th>Usuario Promo</th>
                                                    <th>Orden de Compra</th>
                                                    <th>Paquete</th>
                                                    <th>Error</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- ═══ TAB 2: QR Masivo ═══ --}}
                                <div class="tab-pane fade" id="tab-bulk" role="tabpanel">

                                    <div class="row g-3 mb-4">

                                        {{-- Crear lote de QR --}}
                                        <div class="col-lg-7">
                                            <div class="card h-100" style="border-left:4px solid #0d6efd;">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-plus-circle me-1 text-primary"></i>Generar lote de QR
                                                    </h6>
                                                    <div class="row g-2">
                                                        <div class="col-12">
                                                            <label class="form-label fw-semibold">Nombre del proyecto</label>
                                                            <input type="text" class="form-control" id="bulk_nombre"
                                                                   placeholder="ej. Cliente XYZ Mayo 2026">
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <label class="form-label fw-semibold">Paquete</label>
                                                            <select class="form-select" id="bulk_package">
                                                                <option value="Deluxe" selected>Deluxe</option>
                                                                <option value="Express">Express</option>
                                                                <option value="Básico">Básico</option>
                                                                <option value="Ultra">Ultra</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <label class="form-label fw-semibold">Precio</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" class="form-control" id="bulk_price"
                                                                       min="0" step="0.01" placeholder="0.00">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-semibold">Usos por código</label>
                                                            <input type="number" class="form-control" id="bulk_uses"
                                                                   min="1" step="1" value="1">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-semibold">Fecha expiración</label>
                                                            <input type="date" class="form-control" id="bulk_expiration">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-semibold">Cantidad de QR</label>
                                                            <input type="number" class="form-control" id="bulk_cantidad"
                                                                   min="1" max="1000" step="1" placeholder="100">
                                                        </div>
                                                        <div class="col-12 mt-1">
                                                            <button class="btn btn-primary w-100" id="btnGenerarBulk">
                                                                <i class="bi bi-qr-code me-1"></i>Generar QR
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Descargar por proyecto --}}
                                        <div class="col-lg-5">
                                            <div class="card h-100" style="border-left:4px solid #198754;">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-download me-1 text-success"></i>Descargar PDF por proyecto
                                                    </h6>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Filtrar por proyecto</label>
                                                        <select class="form-select" id="downloadProyecto">
                                                            <option value="">— selecciona un proyecto —</option>
                                                        </select>
                                                    </div>
                                                    <div class="alert alert-light border py-2 small mb-3" id="proyectoInfo" style="display:none;">
                                                        <i class="bi bi-info-circle me-1 text-info"></i>
                                                        <span id="proyectoInfoText"></span>
                                                    </div>
                                                    <div class="mt-auto">
                                                        <button class="btn btn-success w-100" id="btnDescargarPDF" disabled>
                                                            <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF con QR
                                                        </button>
                                                        <button class="btn btn-outline-danger w-100 mt-2" id="btnEliminarProyecto" disabled>
                                                            <i class="bi bi-trash3 me-1"></i>Eliminar todos los QR del proyecto
                                                        </button>
                                                        <p class="text-muted small mt-2 mb-0">
                                                            <i class="bi bi-printer me-1"></i>8 tarjetas por página, A4 con guías de corte.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Tabla de QR bulk --}}
                                    <div class="table-responsive">
                                        <table id="BulkTable" class="table table-striped table-hover table-bordered w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Proyecto</th>
                                                    <th>Código</th>
                                                    <th>Paquete</th>
                                                    <th>Precio</th>
                                                    <th>Usos</th>
                                                    <th>Expiración</th>
                                                    <th>QR</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>

                                </div>{{-- /tab-bulk --}}

                            </div>{{-- /tab-content --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- ======= Modal sEditar ======= -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header" style="background:#198754; color:#fff;">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Promoción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código</label>
                        <input type="text" class="form-control" id="edit_code" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Paquete</label>
                        <select class="form-select" id="edit_package">
                            <option value="Deluxe">Deluxe</option>
                            <option value="Express">Express</option>
                            <option value="Básico">Básico</option>
                            <option value="Ultra">Ultra</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_price" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usos disponibles</label>
                        <input type="number" class="form-control" id="edit_uses" min="0" step="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Expiración</label>
                        <input type="date" class="form-control" id="edit_expiration">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarEdicion">
                        <i class="bi bi-save me-1"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======= Modal Nueva Promoción ======= -->
    <div class="modal fade" id="modalNueva" tabindex="-1" aria-labelledby="modalNuevaLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header" style="background:#198754; color:#fff;">
                    <h5 class="modal-title" id="modalNuevaLabel">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Promoción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Paquete</label>
                        <select class="form-select" id="new_package">
                            <option value="Deluxe">Deluxe</option>
                            <option value="Express">Express</option>
                            <option value="Básico">Básico</option>
                            <option value="Ultra">Ultra</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="new_price" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usos disponibles</label>
                        <input type="number" class="form-control" id="new_uses" min="1" step="1" placeholder="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Expiración</label>
                        <input type="date" class="form-control" id="new_expiration">
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        El código UUID se generará automáticamente. El tipo será <strong>BUSINESS</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarNueva">
                        <i class="bi bi-save me-1"></i>Crear Promoción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======= Footer s======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span></span></strong>. All Rights Reserved
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    @include('layout.footer')

    <script>
    const csrf = $('meta[name="csrf-token"]').attr('content');
    var dtTable, dtBulk;

    // ── DataTable de promociones individuales ─────────────────────────
    $(document).ready(function () {
        dtTable = $('#PromocionesTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("promociones.tabla") }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'data'
            },
            columns: [
                { data: null, render: function(data, type, row, meta) { return meta.row + 1; }, orderable: false },
                { data: 'code', render: function(d) { return '<code class="text-success">' + d + '</code>'; } },
                { data: 'type', render: function(d) { return '<span class="badge badge-type">' + d + '</span>'; } },
                { data: 'price', render: function(d) { return '$' + parseFloat(d).toFixed(2); } },
                { data: 'uses' },
                {
                    data: 'status',
                    render: function(d) {
                        if (d === null || d === 'N/A' || d === '') {
                            return '<span class="badge bg-secondary">Sin estatus</span>';
                        }
                        return '<span class="badge bg-info text-dark">' + d + '</span>';
                    }
                },
                {
                    data: 'expiration',
                    render: function(d) {
                        if (!d || d === '—') return '<span class="text-muted">—</span>';
                        var expired = new Date(d) < new Date();
                        return expired
                            ? '<span class="badge bg-danger">' + d + '</span>'
                            : '<span class="badge bg-success">' + d + '</span>';
                    }
                },
                {
                    data: 'IsSync',
                    render: function(d) {
                        var cls = d === 'Sí' ? 'badge-sync-si' : 'badge-sync-no';
                        return '<span class="badge ' + cls + '">' + d + '</span>';
                    }
                },
                { data: 'lastSync' },
                { data: 'promotion_user', render: function(d) { return '<small class="text-muted">' + d + '</small>'; } },
                { data: 'purchase_order', render: function(d) { return '<small class="text-muted">' + d + '</small>'; } },
                {
                    data: 'package',
                    render: function(d) {
                        var colors = {
                            'Deluxe':  'bg-warning text-dark',
                            'Express': 'bg-primary',
                            'Básico':  'bg-secondary',
                            'Ultra':   'bg-danger',
                        };
                        var cls = colors[d] || 'bg-dark';
                        return '<span class="badge ' + cls + '">' + d + '</span>';
                    }
                },
                {
                    data: 'error',
                    render: function(d) {
                        if (!d || d === '—') return '<span class="text-muted">—</span>';
                        return '<span class="text-danger">' + d + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return '<div class="d-flex gap-1">'
                            + '<button class="btn btn-warning btn-accion btn-editar" '
                            +     'data-id="'         + row.id         + '" '
                            +     'data-code="'       + row.code       + '" '
                            +     'data-price="'      + row.price      + '" '
                            +     'data-uses="'       + row.uses       + '" '
                            +     'data-package="'    + row.package    + '" '
                            +     'data-expiration="' + row.expiration + '" '
                            +     'title="Editar">'
                            +     '<i class="bi bi-pencil"></i>'
                            + '</button>'
                            + '<a href="/promociones/' + row.id + '/pdf" target="_blank" '
                            +    'class="btn btn-danger btn-accion" title="Descargar PDF con QR">'
                            +    '<i class="bi bi-file-earmark-pdf"></i>'
                            + '</a>'
                            + '</div>';
                    }
                },
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[6, 'asc']],
            initComplete: function() {
                $('#totalCount').text(this.api().rows().count() + ' registros');
            }
        });
    });

    // Abrir modal con datos de la fila
    $(document).on('click', '.btn-editar', function () {
        var btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_code').val(btn.data('code'));
        $('#edit_price').val(btn.data('price'));
        $('#edit_uses').val(btn.data('uses'));
        $('#edit_package').val(btn.data('package'));

        // La fecha viene como "YYYY-MM-DD HH:ii:ss", solo tomamos la parte de fecha
        var exp = btn.data('expiration') || '';
        $('#edit_expiration').val(exp.substring(0, 10));

        $('#modalEditar').modal('show');
    });

    // Crear nueva promoción
    $('#btnGuardarNueva').on('click', function () {
        var expiration = $('#new_expiration').val();
        var price      = $('#new_price').val();
        var uses       = $('#new_uses').val();

        if (!expiration || !price || !uses) {
            Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Completa todos los campos antes de continuar.' });
            return;
        }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

        $.ajax({
            url: '{{ route("promociones.store") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                package:    $('#new_package').val(),
                price:      price,
                uses:       uses,
                expiration: expiration,
            },
            success: function (res) {
                $('#modalNueva').modal('hide');
                $('#new_price').val('');
                $('#new_uses').val('');
                $('#new_expiration').val('');
                dtTable.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Creada', text: 'Código generado: ' + res.code, timer: 3500, showConfirmButton: false });
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo crear la promoción.' });
            },
            complete: function() {
                $('#btnGuardarNueva').prop('disabled', false).html('<i class="bi bi-save me-1"></i>Crear Promoción');
            }
        });
    });

    // Guardar cambios vías AJAX
    $('#btnGuardarEdicion').on('click', function () {
        var id = $('#edit_id').val();
        $.ajax({
            url: '/promociones/' + id,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-HTTP-Method-Override': 'PUT'
            },
            data: {
                price:      $('#edit_price').val(),
                uses:       $('#edit_uses').val(),
                package:    $('#edit_package').val(),
                expiration: $('#edit_expiration').val(),
            },
            success: function () {
                $('#modalEditar').modal('hide');
                dtTable.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Guardado', text: 'Promoción actualizada correctamente.', timer: 2000, showConfirmButton: false });
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar. Intenta de nuevo.' });
            }
        });
    });

    // ── QR Masivo ─────────────────────────────────────────────────────

    function initBulkTab() {
        if (dtBulk) return;

        dtBulk = $('#BulkTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url:  '{{ route("promociones.bulk.tabla") }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                dataSrc: 'data'
            },
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1, orderable: false },
                { data: 'proyecto', render: d => '<span class="badge bg-info text-dark">' + d + '</span>' },
                { data: 'code', render: d => '<code class="text-primary" style="font-size:10px">' + d + '</code>' },
                {
                    data: 'package',
                    render: function(d) {
                        var colors = { 'Deluxe':'bg-warning text-dark', 'Express':'bg-primary', 'Básico':'bg-secondary', 'Ultra':'bg-danger' };
                        return '<span class="badge ' + (colors[d] || 'bg-dark') + '">' + d + '</span>';
                    }
                },
                { data: 'price', render: d => '$' + parseFloat(d).toFixed(2) },
                { data: 'uses' },
                {
                    data: 'expiration',
                    render: function(d) {
                        if (!d || d === '—') return '<span class="text-muted">—</span>';
                        return new Date(d) < new Date()
                            ? '<span class="badge bg-danger">'  + d + '</span>'
                            : '<span class="badge bg-success">' + d + '</span>';
                    }
                },
                {
                    data: null, orderable: false,
                    render: (d, t, row) =>
                        '<a href="/promociones/' + row.id + '/pdf" target="_blank" '
                        + 'class="btn btn-danger btn-accion" title="PDF individual">'
                        + '<i class="bi bi-file-earmark-pdf"></i></a>'
                },
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[1, 'asc']],
            initComplete: function () {
                $('#bulkCount').text(this.api().rows().count() + ' registros');
            }
        });

        cargarProyectos();
    }

    $('#tab-bulk-btn').on('shown.bs.tab', function () { initBulkTab(); });

    function cargarProyectos() {
        $.get('{{ route("promociones.bulk.proyectos") }}', function (data) {
            var sel = $('#downloadProyecto');
            sel.find('option:not(:first)').remove();
            data.forEach(function (p) {
                sel.append('<option value="' + p.id + '" data-total="' + p.total + '" data-nombre="' + p.nombre + '">'
                    + p.nombre + ' (' + p.total + ' QR)</option>');
            });
        });
    }

    $('#downloadProyecto').on('change', function () {
        var opt   = $(this).find(':selected');
        var total = opt.data('total');
        var val   = $(this).val();

        if (val) {
            $('#proyectoInfo').show();
            $('#proyectoInfoText').text(total + ' QR en este proyecto. El PDF tendrá ' + Math.ceil(total / 8) + ' página(s).');
            $('#btnDescargarPDF').prop('disabled', false);
            $('#btnEliminarProyecto').prop('disabled', false);
        } else {
            $('#proyectoInfo').hide();
            $('#btnDescargarPDF').prop('disabled', true);
            $('#btnEliminarProyecto').prop('disabled', true);
        }
    });

    $('#btnDescargarPDF').on('click', function () {
        var id = $('#downloadProyecto').val();
        if (!id) return;
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Generando PDF...');
        window.open('{{ route("promociones.bulk.download") }}?proyecto_id=' + id, '_blank');
        setTimeout(function () {
            btn.prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF con QR');
        }, 4000);
    });

    $('#btnEliminarProyecto').on('click', function () {
        var opt    = $('#downloadProyecto').find(':selected');
        var id     = $('#downloadProyecto').val();
        var nombre = opt.data('nombre');
        var total  = opt.data('total');
        if (!id) return;

        Swal.fire({
            title: '¿Eliminar proyecto?',
            html:  'Se eliminarán <strong>' + parseInt(total).toLocaleString('es-MX') + ' QR</strong> del proyecto:<br><code>' + nombre + '</code><br><br>Esta acción <u>no se puede deshacer</u>.',
            icon:  'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar todo', cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var btn = $('#btnEliminarProyecto');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Eliminando...');

            fetch('{{ route("promociones.bulk.delete-proyecto") }}', {
                method:  'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({ proyecto_id: id }),
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminado',
                        text: res.deleted.toLocaleString('es-MX') + ' QR eliminados del proyecto "' + nombre + '".',
                        timer: 2500, showConfirmButton: false });
                    if (dtBulk) dtBulk.ajax.reload(function(s) { $('#bulkCount').text(s.recordsTotal + ' registros'); }, false);
                    cargarProyectos();
                    $('#downloadProyecto').val('');
                    $('#proyectoInfo').hide();
                    $('#btnDescargarPDF, #btnEliminarProyecto').prop('disabled', true);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    btn.prop('disabled', false).html('<i class="bi bi-trash3 me-1"></i>Eliminar todos los QR del proyecto');
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar con el servidor.' });
                btn.prop('disabled', false).html('<i class="bi bi-trash3 me-1"></i>Eliminar todos los QR del proyecto');
            });
        });
    });

    // Generar lote de QR
    $('#btnGenerarBulk').on('click', function () {
        var nombre   = $('#bulk_nombre').val().trim();
        var pack     = $('#bulk_package').val();
        var price    = $('#bulk_price').val();
        var uses     = $('#bulk_uses').val();
        var exp      = $('#bulk_expiration').val();
        var cantidad = $('#bulk_cantidad').val();

        if (!nombre || !price || !uses || !exp || !cantidad) {
            Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Completa todos los campos.' });
            return;
        }

        Swal.fire({
            title: '¿Generar ' + parseInt(cantidad).toLocaleString('es-MX') + ' QR?',
            html:  'Proyecto: <strong>' + nombre + '</strong><br>Paquete: <strong>' + pack + '</strong>',
            icon:  'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, generar', cancelButtonText: 'Cancelar',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var btn = $('#btnGenerarBulk');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Generando...');

            $.ajax({
                url:     '{{ route("promociones.bulk.store") }}',
                type:    'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data:    { nombre: nombre, package: pack, price: price, uses: uses, expiration: exp, cantidad: cantidad },
                success: function (res) {
                    Swal.fire({ icon: 'success', title: '¡Listo!',
                        text: res.created.toLocaleString('es-MX') + ' QR generados para "' + nombre + '".',
                        timer: 3000, showConfirmButton: false });
                    if (dtBulk) dtBulk.ajax.reload(function(s) { $('#bulkCount').text(s.recordsTotal + ' registros'); }, false);
                    cargarProyectos();
                    $('#bulk_nombre, #bulk_price, #bulk_expiration, #bulk_cantidad').val('');
                    $('#bulk_uses').val(1);
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'No se pudo generar el lote.' });
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="bi bi-qr-code me-1"></i>Generar QR');
                }
            });
        });
    });
    </script>

</body>
