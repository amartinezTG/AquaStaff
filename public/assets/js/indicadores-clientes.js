let clientesTable = null;
let _lavadosDT = null;

const memColors = {
    'Express': 'badge-express',
    'Básico':  'badge-basico',
    'Ultra':   'badge-ultra',
    'Delux':   'badge-delux',
    'N/A':     'badge-na',
};

function cargarClientes() {
    if (clientesTable) {
        clientesTable.destroy();
        $('#clientes_table tbody').empty();
    }
    $('#clientes_table thead tr.col-filters').remove();

    Swal.fire({
        title: 'Cargando clientes...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });

    clientesTable = $('#clientes_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        paging: true,
        pageLength: 50,
        // lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'Todos']],
        // scrollX: true,
        // scrollCollapse: true,
        // autoWidth: false,
        order: [[9, 'desc']],
        ajax: {
            url: '/indicadores/clientes/table',
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            dataSrc: function(json) {
                Swal.close();
                const data = json.data || [];
                actualizarResumen(data);
                document.getElementById('summaryCards').style.display = 'flex';
                document.getElementById('filterRow').style.display = 'block';
                return data;
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los clientes.' });
            }
        },
        columns: [
            {
                data: '_id',
                render: (d, type) => {
                    if (type !== 'display') return d || '';
                    return `<div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-primary py-0 px-1 btn-detalle-cliente" data-id="${d}" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <code style="font-size:.68rem;color:#6c757d;">${d}</code>
                    </div>`;
                }
            },
            {
                data: 'cliente',
                render: (d, type) => type !== 'display' ? (d || 'Sin nombre') : `<span class="fw-bold">${d || '<span class="text-muted">Sin nombre</span>'}</span>`
            },
            {
                data: 'tag',
                render: (d, type) => type !== 'display' ? (d || '') : (d ? `<code style="font-size:.72rem;">${d}</code>` : '')
            },
            {
                data: 'plate',
                render: (d, type) => type !== 'display' ? (d ? d.toUpperCase() : '') : (d ? `<span class="fw-bold">${d.toUpperCase()}</span>` : '')
            },
            {
                data: 'brand',
                render: (d, type, row) => {
                    const parts = [row.brand || '', row.model || '', row.color || ''].filter(Boolean);
                    return parts.join(' ') || '';
                }
            },
            { data: 'phone' },
            {
                data: 'email',
                render: (d, type) => type !== 'display' ? (d || '') : (d ? `<a href="mailto:${d}">${d}</a>` : '')
            },
            {
                data: 'tipo_membresia',
                render: (d, type) => type !== 'display' ? (d || '') : `<span class="badge ${memColors[d] || 'badge-na'}">${d}</span>`
            },
            {
                data: 'estatus_membresia',
                render: (d, type) => {
                    if (type !== 'display') return d || '';
                    const cls = d === 'Vigente' ? 'badge-vigente' : d === 'Vencida' ? 'badge-vencida' : 'badge-sin';
                    return `<span class="badge ${cls}">${d}</span>`;
                }
            },
            { data: 'fecha_registro' },
            { data: 'start_date' },
            {
                data: 'end_date',
                render: (d, type) => {
                    if (!d) return '';
                    if (type !== 'display') return d;
                    const hoy = new Date().toISOString().slice(0, 10);
                    const color = d < hoy ? 'text-danger fw-bold' : (d <= hoy.slice(0,7) + '-31' ? 'text-warning fw-bold' : '');
                    return `<span class="${color}">${d}</span>`;
                }
            },
            { data: 'is_recurrent' },
            {
                data: 'recurrente_procepago',
                className: 'text-center',
                render: (d, type) => {
                    if (type !== 'display') return d || 'No';
                    return d === 'Sí'
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-secondary">No</span>';
                }
            },
            { data: 'renewal_count', className: 'text-center' },
            {
                data: 'total_lavados',
                className: 'text-center fw-bold',
                render: (d, type) => type !== 'display' ? (d ?? '') : `<span class="badge bg-primary">${d}</span>`
            },
            { data: 'ultimo_lavado' },
            { data: 'prosepago_id' },
            { data: 'titular' },
            { data: 'tarjeta', className: 'text-center' },
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                className: 'btn btn-success buttons-excel',
                filename: 'Clientes_AquaAdmin',
                title: 'Clientes - AquaAdmin',
                exportOptions: {
                    modifier: { page: 'all', search: 'applied' }
                }
            },
        ],
        initComplete: function() {
            Swal.close();
            agregarFiltrosColumna();
        }
    });
}

function actualizarResumen(data) {
    const total    = data.length;
    const vigente  = data.filter(r => r.estatus_membresia === 'Vigente').length;
    const vencida  = data.filter(r => r.estatus_membresia === 'Vencida').length;
    const sin      = data.filter(r => r.estatus_membresia === 'Sin membresía').length;

    document.getElementById('statTotal').textContent   = total.toLocaleString();
    document.getElementById('statVigente').textContent = vigente.toLocaleString();
    document.getElementById('statVencida').textContent = vencida.toLocaleString();
    document.getElementById('statSin').textContent     = sin.toLocaleString();
}

function agregarFiltrosColumna() {
    // Limpiar fila anterior si existe
    $('#clientes_table thead tr.col-filters').remove();

    const filterRow = $('<tr class="col-filters"></tr>');
    // Tomar títulos de la primera fila del thead (la de cabeceras)
    $('#clientes_table thead tr:first th').each(function () {
        const title = $(this).text().trim();
        filterRow.append(
            $('<th></th>').html(`<input type="text" class="form-control form-control-sm" placeholder="${title}" />`)
        );
    });
    $('#clientes_table thead').append(filterRow);

    $('#clientes_table thead tr.col-filters input').on('keyup change', function () {
        const colIndex = $(this).closest('th').index();
        clientesTable.column(colIndex).search(this.value).draw();
    });
}

// Filtro por columna estatus (col 7)
function filtrarEstatus(val) {
    if (!clientesTable) return;
    // Resaltar botón activo
    ['todos','vigente','vencida','sin'].forEach(b => document.getElementById('btn-'+b)?.classList.remove('active'));
    const map = { 'todos': 'btn-todos', 'Vigente': 'btn-vigente', 'Vencida': 'btn-vencida', 'Sin membresía': 'btn-sin' };
    document.getElementById(map[val] || 'btn-todos')?.classList.add('active');

    clientesTable.column(8).search(val === 'todos' ? '' : val, false, false).draw();
}

// Filtro por tipo membresía (col 6)
function filtrarTipo(val) {
    if (!clientesTable) return;
    clientesTable.column(7).search(val, false, false).draw();
}

// ── Modal detalle cliente ──────────────────────────────────────────────────

$(document).on('click', '.btn-detalle-cliente', function () {
    const clientId = $(this).data('id');
    const row = clientesTable.row($(this).closest('tr')).data();
    const nombre = row ? (row.cliente || clientId) : clientId;

    $('#modalClienteNombre').text(nombre);
    limpiarModal();
    $('#modalDetalleCliente').modal('show');

    $.ajax({
        url: '/indicadores/clientes/detalle',
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), client_id: clientId },
        success: function (res) {
            if (!res.success) { mostrarErrorModal(res.message); return; }
            renderMembresias(res.membresias);
            renderLavados(res.lavados);
            renderDomiciliaciones(res.domiciliaciones);
            renderTransacciones(res.transacciones);
        },
        error: function () { mostrarErrorModal('Error al obtener los datos.'); }
    });
});

function limpiarModal() {
    ['body-membresias','body-lavados','body-domiciliaciones','body-transacciones'].forEach(id => {
        document.getElementById(id).innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3"><i class="bi bi-hourglass-split me-1"></i>Cargando...</td></tr>';
    });
    ['badge-membresias','badge-lavados','badge-domiciliaciones','badge-transacciones'].forEach(id => {
        document.getElementById(id).textContent = '…';
    });
}

function mostrarErrorModal(msg) {
    ['body-membresias','body-lavados','body-domiciliaciones','body-transacciones'].forEach(id => {
        document.getElementById(id).innerHTML = `<tr><td colspan="10" class="text-center text-danger py-3">${msg}</td></tr>`;
    });
}

function fmtFecha(d) {
    if (!d) return '—';
    return d.slice(0, 16).replace('T', ' ');
}

function renderMembresias(rows) {
    document.getElementById('badge-membresias').textContent = rows.length;
    const tipos = { Express: 'badge-express', 'Básico': 'badge-basico', Ultra: 'badge-ultra', Delux: 'badge-delux' };
    const html = rows.length === 0 ? '<tr><td colspan="8" class="text-center text-muted">Sin membresías</td></tr>' :
        rows.map(m => {
            const tipoCls = tipos[m.tipo] || 'badge-na';
            let estatusBadge;
            if (m.eliminada)     estatusBadge = '<span class="badge bg-secondary">Eliminada</span>';
            else if (m.vigente)  estatusBadge = '<span class="badge badge-vigente">Vigente</span>';
            else                 estatusBadge = '<span class="badge badge-vencida">Vencida</span>';
            return `<tr>
                <td><small class="text-muted">${m.id}</small></td>
                <td><span class="badge ${tipoCls}">${m.tipo}</span></td>
                <td>${fmtFecha(m.start_date)}</td>
                <td>${fmtFecha(m.end_date)}</td>
                <td class="text-center">${m.months ?? '—'}</td>
                <td class="text-center">${m.uses ?? 0}</td>
                <td><small>${m.prosepago_id ?? '—'}</small></td>
                <td>${estatusBadge}</td>
            </tr>`;
        }).join('');
    document.getElementById('body-membresias').innerHTML = html;
}

function renderLavados(rows) {
    if (_lavadosDT) { _lavadosDT.destroy(); _lavadosDT = null; }

    document.getElementById('badge-lavados').textContent = rows.length;
    const tipos = { Express: 'badge-express', 'Básico': 'badge-basico', Ultra: 'badge-ultra', Delux: 'badge-delux' };
    const html = rows.length === 0 ? '' :
        rows.map((l, i) => {
            const tipoCls = tipos[l.tipo] || 'badge-na';
            const precio = l.Price != null ? parseFloat(l.Price).toFixed(2) : '0.00';
            const ts = new Date(l.OrderDate || l.created_at || 0).getTime();
            return `<tr>
                <td class="text-center text-muted">${i + 1}</td>
                <td data-order="${ts}">${fmtFecha(l.OrderDate || l.created_at)}</td>
                <td><span class="badge ${tipoCls}">${l.tipo}</span></td>
                <td data-order="${precio}" class="text-end">$${precio}</td>
            </tr>`;
        }).join('');
    document.getElementById('body-lavados').innerHTML = html;

    _lavadosDT = $('#tbl-lavados').DataTable({
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rt',
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel-fill me-1"></i> Excel',
            className: 'btn btn-success btn-sm',
            title: 'Lavados',
            exportOptions: { columns: [0,1,2,3] }
        }],
        language: {
            search: 'Buscar:',
            lengthMenu: '_MENU_ por página',
            info: '_START_–_END_ de _TOTAL_',
            infoEmpty: 'Sin registros',
            zeroRecords: 'Sin resultados',
            paginate: { previous: '‹', next: '›' }
        },
        order: [[1, 'asc']],
        paging: false,
        autoWidth: false,
        columnDefs: [{ orderable: false, targets: 0 }]
    });
}

function renderDomiciliaciones(rows) {
    document.getElementById('badge-domiciliaciones').textContent = rows.length;
    const html = rows.length === 0 ? '<tr><td colspan="8" class="text-center text-muted">Sin cobros</td></tr>' :
        rows.map(d => {
            const ok = d.prosepago_status == 1;
            const badge = ok
                ? '<span class="badge bg-success">OK</span>'
                : '<span class="badge bg-danger">Error</span>';
            return `<tr>
                <td>${fmtFecha(d.created_at)}</td>
                <td class="text-center"><code>${d.tarjeta ?? '—'}</code></td>
                <td>${d.titular ?? '—'}</td>
                <td class="text-center">${d.banco ?? '—'}</td>
                <td class="text-end">$${parseFloat(d.importe ?? 0).toFixed(2)}</td>
                <td><small>${d.concepto ?? '—'}</small></td>
                <td class="text-center">${badge}</td>
                <td><small>${d.prosepago_mensaje ?? '—'}</small></td>
            </tr>`;
        }).join('');
    document.getElementById('body-domiciliaciones').innerHTML = html;
}

function renderTransacciones(rows) {
    document.getElementById('badge-transacciones').textContent = rows.length;
    const html = rows.length === 0 ? '<tr><td colspan="6" class="text-center text-muted">Sin transacciones</td></tr>' :
        rows.map(t => {
            const ok = t.status == 0 || t.prosepago_status == 1;
            const badge = ok
                ? '<span class="badge bg-success">OK</span>'
                : '<span class="badge bg-danger">Rechazada</span>';
            return `<tr>
                <td>${fmtFecha(t.fecha)}</td>
                <td><small>${t.folio ?? '—'}</small></td>
                <td class="text-end">$${parseFloat(t.importe ?? 0).toFixed(2)}</td>
                <td><small>${t.concepto ?? '—'}</small></td>
                <td class="text-center">${badge}</td>
                <td><small class="text-danger">${t.causa_denegada ?? ''}</small></td>
            </tr>`;
        }).join('');
    document.getElementById('body-transacciones').innerHTML = html;
}
