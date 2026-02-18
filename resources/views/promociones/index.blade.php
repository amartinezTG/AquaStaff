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
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <!-- ======= Header ==d===== -->
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
                            <h5 class="card-title">
                                <i class="bi bi-tag me-2"></i>Listado de Promociones
                                <span class="badge bg-secondary ms-2" id="totalCount">—</span>
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

    <!-- ======= Footer ======= -->
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
    $(document).ready(function () {
        var table = $('#PromocionesTable').DataTable({
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
                { data: 'code',           render: function(d) { return '<code class="text-success">' + d + '</code>'; } },
                { data: 'type',           render: function(d) { return '<span class="badge badge-type">' + d + '</span>'; } },
                { data: 'price',          render: function(d) { return '$' + parseFloat(d).toFixed(2); } },
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
                { data: 'expiration' },
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
                { data: 'package',        render: function(d) { return '<small class="text-muted">' + d + '</small>'; } },
                {
                    data: 'error',
                    render: function(d) {
                        if (!d || d === '—') return '<span class="text-muted">—</span>';
                        return '<span class="text-danger">' + d + '</span>';
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
    </script>

</body>
