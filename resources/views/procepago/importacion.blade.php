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
            #procepago_table thead th {
                background: linear-gradient(135deg, #198754 0%, #146c43 100%);
                color: #fff;
                font-weight: 600;
                font-size: 0.76rem;
                text-align: center;
                vertical-align: middle;
                white-space: nowrap;
                padding: 10px 8px;
                border: none;
            }
            #procepago_table tbody td {
                vertical-align: middle;
                padding: 7px 8px;
                font-size: 0.78rem;
                border-right: 1px solid #e9ecef;
            }
            #procepago_table tbody tr:hover { background-color: #f0fff4; }
            .badge-dom  { background-color: #0d6efd; color:#fff; }
            .badge-pros { background-color: #198754; color:#fff; }
            .dt-button {
                color:#fff !important; border-radius:20px !important;
                font-weight:600 !important; font-size:0.82rem !important;
                padding:7px 18px !important; border:none !important;
            }
            .buttons-excel { background:linear-gradient(135deg,#46b723,#3cb723) !important; }
            .buttons-copy  { background:linear-gradient(135deg,#ffb800,#f59e0b) !important; }
        </style>

        <div class="pagetitle">
            <h1>Procepago — Importación</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
                    <li class="breadcrumb-item active">Procepago</li>
                    <li class="breadcrumb-item active">Importación</li>
                </ol>
            </nav>
        </div>

        <section class="section">

            {{-- Card importar --}}
            <div class="card mb-3" style="border-left:5px solid #198754;">
                <div class="card-body py-3">
                    <h6 class="fw-bold mb-3" style="color:#198754;font-size:.88rem;">
                        <i class="bi bi-cloud-upload me-1"></i>Importar archivo de liquidaciones Procepago
                    </h6>
                    <p class="text-muted mb-3" style="font-size:.80rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Sube el archivo <code>.xlsx</code> / <code>.xlsm</code> y escribe el nombre exacto de la hoja (ej: <code>200426</code>).
                        Los folios ya importados serán ignorados automáticamente.
                    </p>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold mb-1" style="font-size:.8rem;">Archivo Excel</label>
                            <input type="file" id="archivoInput" class="form-control form-control-sm"
                                accept=".xlsx,.xlsm,.xls" onchange="detectarHojas()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1" style="font-size:.8rem;">Hoja</label>
                            <div class="d-flex gap-1">
                                <input type="text" id="hojaInput" class="form-control form-control-sm"
                                    placeholder="Ej: 200426" list="hojas-list">
                                <datalist id="hojas-list"></datalist>
                                <span id="hojaSpinner" class="align-self-center d-none">
                                    <span class="spinner-border spinner-border-sm text-success"></span>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm w-100 text-white fw-bold" style="background:#198754;"
                                onclick="importar()">
                                <i class="bi bi-upload me-1"></i>Importar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros tabla --}}
            <div class="card mb-3" style="border-left:5px solid #198754;">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Fecha inicio</label>
                            <input type="date" id="fechaInicio" class="form-control form-control-sm"
                                value="{{ now()->startOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Fecha fin</label>
                            <input type="date" id="fechaFin" class="form-control form-control-sm"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Servicio</label>
                            <select id="servicioFiltro" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="Domiciliaciones">Domiciliaciones</option>
                                <option value="Prosemático">Prosemático</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold mb-1" style="font-size:.8rem;">Hoja origen</label>
                            <input type="text" id="hojaFiltro" class="form-control form-control-sm" placeholder="Ej: 200426">
                        </div>
                        <div class="col-12 col-md-2">
                            <button class="btn btn-sm w-100 text-white" style="background:#198754;" onclick="cargarTabla()">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card" style="border-left:5px solid #198754;">
                <div class="card-body p-3">
                    <p class="text-muted mb-2" style="font-size:.80rem;">
                        <i class="bi bi-info-circle me-1" style="color:#198754;"></i>
                        Registros importados de liquidaciones Procepago. Columna <strong>Depósito</strong> = Importe neto después de comisión e IVA.
                    </p>
                    <table id="procepago_table" class="table table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Servicio</th>
                                <th>Referencia</th>
                                <th>Fecha</th>
                                <th>Importe</th>
                                <th>Comisión</th>
                                <th>IVA</th>
                                <th>Depósito</th>
                                <th>Hoja</th>
                                <th>Archivo</th>
                                <th>Importado por</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr style="background:linear-gradient(135deg,#146c43,#0f5132);color:#fff;font-weight:700;font-size:.80rem;">
                                <th colspan="4">Total</th>
                                <th id="foot-importe"></th>
                                <th id="foot-comision"></th>
                                <th id="foot-iva"></th>
                                <th id="foot-deposito"></th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
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
    let procTable = null;

    // ── Detectar hojas del archivo para el datalist ──────────────────────────
    let hojaDetectTimer = null;
    function detectarHojas() {
        const file = document.getElementById('archivoInput').files[0];
        if (!file) return;

        const spinner = document.getElementById('hojaSpinner');
        spinner.classList.remove('d-none');

        const fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        fd.append('archivo', file);

        $.ajax({
            url: '/procepago/hojas',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(resp) {
                const dl = document.getElementById('hojas-list');
                dl.innerHTML = '';
                (resp.hojas || []).forEach(function(h) {
                    const opt = document.createElement('option');
                    opt.value = h;
                    dl.appendChild(opt);
                });
                // Si solo hay una hoja de datos, autocompletar
                if (resp.hojas && resp.hojas.length === 1) {
                    document.getElementById('hojaInput').value = resp.hojas[0];
                }
            },
            complete: function() { spinner.classList.add('d-none'); }
        });
    }  

    // ── Importar ─────────────────────────────────────────────────────────────
    function importar() {
        const file = document.getElementById('archivoInput').files[0];
        const hoja = document.getElementById('hojaInput').value.trim();

        if (!file) { Swal.fire({ icon:'warning', title:'Archivo requerido', text:'Selecciona un archivo Excel.' }); return; }
        if (!hoja) { Swal.fire({ icon:'warning', title:'Hoja requerida',   text:'Escribe el nombre de la hoja.' }); return; }

        Swal.fire({ title:'Importando...', html:'<p style="font-size:.85rem;">Leyendo archivo y procesando registros.</p>',
            allowOutsideClick:false, showConfirmButton:false, didOpen:() => Swal.showLoading() });

        const fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        fd.append('archivo', file);
        fd.append('hoja', hoja);

        $.ajax({
            url: '/procepago/importacion',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(resp) {
                Swal.fire({
                    icon: 'success',
                    title: 'Importación completada',
                    html: `<div style="font-size:.85rem;">
                        <div class="d-flex justify-content-around my-2">
                            <div><span class="badge bg-success fs-6">${resp.insertados}</span><br><small>Nuevos</small></div>
                            <div><span class="badge bg-secondary fs-6">${resp.duplicados}</span><br><small>Duplicados</small></div>
                            <div><span class="badge bg-danger fs-6">${resp.errores}</span><br><small>Errores</small></div>
                        </div>
                        ${resp.detalle_errores && resp.detalle_errores.length ? '<small class="text-danger">' + resp.detalle_errores.slice(0,3).join('<br>') + '</small>' : ''}
                    </div>`,
                    width: 480,
                }).then(() => cargarTabla());
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'Error al importar.';
                Swal.fire({ icon:'error', title:'Error', text: msg });
            }
        });
    }

    // ── Cargar tabla ─────────────────────────────────────────────────────────
    function cargarTabla() {
        if (procTable) { procTable.destroy(); $('#procepago_table tbody').empty(); }

        Swal.fire({ title:'Cargando...', allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading() });

        procTable = $('#procepago_table').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            paging: true,
            pageLength: 100,
            order: [[3,'desc']],
            ajax: {
                url: '/procepago/table',
                type: 'POST',
                data: {
                    _token:       $('meta[name="csrf-token"]').attr('content'),
                    fecha_inicio: $('#fechaInicio').val(),
                    fecha_final:  $('#fechaFin').val(),
                    servicio:     $('#servicioFiltro').val(),
                    hoja_origen:  $('#hojaFiltro').val(),
                },
                dataSrc: function(json) {
                    Swal.close();
                    calcularTotales(json.data || []);
                    return json.data || [];
                },
                error: function() {
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudieron cargar los registros.' });
                }
            },
            columns: [
                { data:'folio', render: d => `<code style="font-size:.70rem;">${d}</code>` },
                {
                    data:'servicio',
                    render: d => d === 'Domiciliaciones'
                        ? '<span class="badge badge-dom">Domiciliaciones</span>'
                        : '<span class="badge badge-pros">Prosemático</span>'
                },
                { data:'referencia', render: d => d ? `<code style="font-size:.68rem;">${d}</code>` : '<span class="text-muted">—</span>' },
                { data:'fecha', render: d => d ? d.substring(0,16) : '' },
                { data:'importe',  className:'text-end fw-bold', render: d => '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:2}) },
                { data:'comision', className:'text-end',         render: d => '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:4}) },
                { data:'iva',      className:'text-end',         render: d => '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:4}) },
                { data:'deposito', className:'text-end fw-bold text-success', render: d => '$' + parseFloat(d).toLocaleString('es-MX',{minimumFractionDigits:2}) },
                { data:'hoja_origen', render: d => `<code>${d}</code>` },
                { data:'archivo_origen', render: d => `<small style="font-size:.68rem;">${d}</small>` },
                { data:'importado_por_nombre', render: d => d || '—' },
            ],
            language: { url:'//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            dom: 'Blfrtip',
            buttons: [
                { extend:'excel', text:'<i class="ti ti-file-spreadsheet me-1"></i>Excel', className:'btn btn-success buttons-excel',
                  filename:'Procepago_Liquidaciones', exportOptions:{ columns:[0,1,2,3,4,5,6,7,8,9,10] } },
                { extend:'copy',  text:'<i class="ti ti-copy me-1"></i>Copiar', className:'btn btn-warning buttons-copy' },
            ],
            footerCallback: function() {
                var api  = this.api();
                var fmt  = (v, dec) => '$' + v.toLocaleString('es-MX',{minimumFractionDigits:dec,maximumFractionDigits:dec});
                var toNum = v => parseFloat(String(v).replace(/[$,\s]/g,'')) || 0;
                var sum  = col => api.column(col,{search:'applied'}).data().reduce((a,b) => a + toNum(b), 0);
                document.getElementById('foot-importe').textContent  = fmt(sum(4), 2);
                document.getElementById('foot-comision').textContent = fmt(sum(5), 4);
                document.getElementById('foot-iva').textContent      = fmt(sum(6), 4);
                document.getElementById('foot-deposito').textContent = fmt(sum(7), 2);
            },
        });
    }

    function calcularTotales(data) {
        // totales iniciales antes de cualquier búsqueda — footerCallback los reemplazará al dibujar
        const sum = (key) => data.reduce((a,r) => a + parseFloat(r[key]||0), 0);
        const fmt = (v, dec=2) => '$' + v.toLocaleString('es-MX',{minimumFractionDigits:dec,maximumFractionDigits:dec});
        document.getElementById('foot-importe').textContent  = fmt(sum('importe'));
        document.getElementById('foot-comision').textContent = fmt(sum('comision'),4);
        document.getElementById('foot-iva').textContent      = fmt(sum('iva'),4);
        document.getElementById('foot-deposito').textContent = fmt(sum('deposito'));
    }

    document.addEventListener('DOMContentLoaded', cargarTabla);
    </script>
</body>
