let clientesTable = null;

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
        deferRender: true,
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
                data: 'cliente',
                render: (d) => `<span class="fw-bold">${d || '<span class="text-muted">Sin nombre</span>'}</span>`
            },
            { data: 'tag', render: (d) => d ? `<code style="font-size:.72rem;">${d}</code>` : '' },
            { data: 'plate', render: (d) => d ? `<span class="fw-bold">${d.toUpperCase()}</span>` : '' },
            {
                data: 'brand',
                render: (d, t, row) => {
                    const brand = row.brand || '';
                    const model = row.model || '';
                    const color = row.color || '';
                    const parts = [brand, model, color].filter(Boolean);
                    return parts.join(' ') || '';
                }
            },
            { data: 'phone' },
            { data: 'email', render: (d) => d ? `<a href="mailto:${d}">${d}</a>` : '' },
            {
                data: 'tipo_membresia',
                render: (d) => {
                    const cls = memColors[d] || 'badge-na';
                    return `<span class="badge ${cls}">${d}</span>`;
                }
            },
            {
                data: 'estatus_membresia',
                render: (d) => {
                    const cls = d === 'Vigente' ? 'badge-vigente' : d === 'Vencida' ? 'badge-vencida' : 'badge-sin';
                    return `<span class="badge ${cls}">${d}</span>`;
                }
            },
            { data: 'start_date' },
            {
                data: 'end_date',
                render: (d, t, row) => {
                    if (!d) return '';
                    const hoy = new Date().toISOString().slice(0, 10);
                    const color = d < hoy ? 'text-danger fw-bold' : (d <= hoy.slice(0,7) + '-31' ? 'text-warning fw-bold' : '');
                    return `<span class="${color}">${d}</span>`;
                }
            },
            { data: 'is_recurrent' },
            { data: 'renewal_count', className: 'text-center' },
            {
                data: 'total_lavados',
                className: 'text-center fw-bold',
                render: (d) => `<span class="badge bg-primary">${d}</span>`
            },
            { data: 'ultimo_lavado' },
            { data: 'prosepago_id' },
            { data: 'banco' },
            { data: 'titular' },
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
                exportOptions: { format: { body: (d, r, c, node) => $(node).text() } }
            },
            {
                extend: 'pdf',
                text: '<i class="ti ti-file-type-pdf me-1"></i>PDF',
                className: 'btn btn-danger buttons-pdf',
                filename: 'Clientes_AquaAdmin',
                title: 'Clientes - AquaAdmin',
                orientation: 'landscape',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13] }
            },
            {
                extend: 'copy',
                text: '<i class="ti ti-copy me-1"></i>Copiar',
                className: 'btn btn-warning buttons-copy',
            },
        ],
        initComplete: function() { Swal.close(); }
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

// Filtro por columna estatus (col 7)
function filtrarEstatus(val) {
    if (!clientesTable) return;
    // Resaltar botón activo
    ['todos','vigente','vencida','sin'].forEach(b => document.getElementById('btn-'+b)?.classList.remove('active'));
    const map = { 'todos': 'btn-todos', 'Vigente': 'btn-vigente', 'Vencida': 'btn-vencida', 'Sin membresía': 'btn-sin' };
    document.getElementById(map[val] || 'btn-todos')?.classList.add('active');

    clientesTable.column(7).search(val === 'todos' ? '' : val, false, false).draw();
}

// Filtro por tipo membresía (col 6)
function filtrarTipo(val) {
    if (!clientesTable) return;
    clientesTable.column(6).search(val, false, false).draw();
}
