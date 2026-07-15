let dashClientesTable = null;
let dashClientesDataCache = null;

const dashMemColors = {
    'Express': 'badge-express',
    'Básico':  'badge-basico',
    'Ultra':   'badge-ultra',
    'Delux':   'badge-delux',
    'N/A':     'badge-na',
};

// vista: 'activas' (membresía vigente) | 'domiciliadas' (vigente + recurrente_procepago = Sí)
function abrirModalClientes(vista) {
    const titulo = vista === 'domiciliadas' ? 'Domiciliaciones (membresía activa)' : 'Membresías Activas';
    document.getElementById('modalClientesDashboardTitulo').textContent = titulo;

    const modalEl = document.getElementById('modalClientesDashboard');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    cargarDashClientes(vista);
}

function filtrarFilasDash(data, vista) {
    return data.filter(r => {
        if (r.estatus_membresia !== 'Vigente') return false;
        if (vista === 'domiciliadas') return r.recurrente_procepago === 'Sí';
        return true;
    });
}

function cargarDashClientes(vista) {
    if (dashClientesTable) {
        dashClientesTable.destroy();
        $('#dash_clientes_table tbody').empty();
    }

    if (dashClientesDataCache) {
        dashClientesTable = buildDashClientesTable(filtrarFilasDash(dashClientesDataCache, vista));
        return;
    }

    dashClientesTable = $('#dash_clientes_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        paging: true,
        pageLength: 25,
        order: [[9, 'desc']],
        ajax: {
            url: '/indicadores/clientes/table',
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            dataSrc: function (json) {
                dashClientesDataCache = json.data || [];
                return filtrarFilasDash(dashClientesDataCache, vista);
            },
            error: function () {
                alert('No se pudo cargar el listado de clientes.');
            }
        },
        columns: dashClientesColumns(),
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    });
}

function buildDashClientesTable(data) {
    return $('#dash_clientes_table').DataTable({
        destroy: true,
        paging: true,
        pageLength: 25,
        order: [[9, 'desc']],
        data: data,
        columns: dashClientesColumns(),
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    });
}

function dashClientesColumns() {
    return [
        {
            data: '_id',
            render: (d, type) => type !== 'display' ? (d || '') : `<code style="font-size:.68rem;color:#6c757d;">${d}</code>`
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
            render: (d, type, row) => [row.brand || '', row.model || '', row.color || ''].filter(Boolean).join(' ') || ''
        },
        { data: 'phone' },
        {
            data: 'email',
            render: (d, type) => type !== 'display' ? (d || '') : (d ? `<a href="mailto:${d}">${d}</a>` : '')
        },
        {
            data: 'tipo_membresia',
            render: (d, type) => type !== 'display' ? (d || '') : `<span class="badge ${dashMemColors[d] || 'badge-na'}">${d}</span>`
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
                const color = d < hoy ? 'text-danger fw-bold' : '';
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
    ];
}
