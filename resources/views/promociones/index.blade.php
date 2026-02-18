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
            <a href="index.php" class="logo d-flex align-items-center">
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
                            <h5 class="card-title d-flex align-items-center justify-content-between">
                                <span>
                                    <i class="bi bi-tag me-2"></i>Listado de Promociones
                                    <span class="badge bg-secondary ms-2" id="totalCount">—</span>
                                </span>
                                <button class="btn btn-success btn-sm" onclick="$('#modalNueva').modal('show')">
                                    <i class="bi bi-plus-circle me-1"></i>Nueva Promoción
                                </button>
                            </h5>

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
    var dtTable;

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
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
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
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
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
    </script>

</body>
