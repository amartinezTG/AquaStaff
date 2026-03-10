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

        <div class="pagetitle">
            <h1><i class="bi bi-arrow-repeat"></i> Monitor de Sincronización</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('administracion.index') }}">Administración</a></li>
                    <li class="breadcrumb-item active">Monitor de Sincronización</li>
                </ol>
            </nav>
        </div>
  
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-database-check"></i> Última recepción por estructura — <code>transactions_log</code></h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="syncTable.ajax.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
                <div class="card-body">
                    <table id="syncMonitorTable" class="table table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th>Estructura</th>
                                <th>Última Recepción</th>
                                <th>Tiempo Transcurrido</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </section>

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

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        var syncTable = $('#syncMonitorTable').DataTable({
            dom: '<"top"f>rt<"bottom"lip>',
            pageLength: 25,
            ordering: false,
            ajax: {
                method: 'POST',
                url: '{{ route("administracion.sync.monitor.table") }}',
                headers: { 'X-CSRF-TOKEN': token },
                error: function(xhr) {
                    var msg = 'Error al cargar datos';
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json.error) msg = json.error;
                    } catch(e) {}
                    $('#syncMonitorTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-danger"><i class="bi bi-exclamation-triangle"></i> ' + msg + '</td></tr>'
                    );
                }
            },
            columns: [
                {
                    data: 'estructura',
                    render: function(data) {
                        return '<code>' + data + '</code>';
                    }
                },
                {
                    data: 'ultima_recepcion',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">—</span>';
                        return data;
                    }
                },
                {
                    data: 'minutos_desde',
                    render: function(data) {
                        if (data === null) return '<span class="text-muted">—</span>';
                        if (data < 60) return data + ' min';
                        if (data < 1440) return Math.floor(data / 60) + 'h ' + (data % 60) + 'min';
                        return Math.floor(data / 1440) + 'd ' + Math.floor((data % 1440) / 60) + 'h';
                    }
                },
                {
                    data: 'estado',
                    render: function(data) {
                        if (data === 'ok')      return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> OK</span>';
                        if (data === 'warning') return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> +1h</span>';
                        if (data === 'danger')  return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> +24h</span>';
                        return '<span class="badge bg-secondary">Sin datos</span>';
                    }
                },
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });
    </script>

</body>
