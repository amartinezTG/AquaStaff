let facturacionTable = null;
let historialTable   = null;
let selectedIds      = new Set();

// ─────────────────────────────────────────────────────────────────────────────
// TABLA TRANSACCIONES
// ─────────────────────────────────────────────────────────────────────────────

function buscarTransacciones() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFinal  = document.getElementById('fechaFinal').value;
    const paymentType = document.getElementById('paymentType').value;
    const cajero      = document.getElementById('cajeroFiltro').value.trim();

    if (!fechaInicio || !fechaFinal) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Selecciona fecha de inicio y fin.' });
        return;
    }

    Swal.fire({ title: 'Cargando transacciones...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    if (facturacionTable) {
        facturacionTable.destroy();
        $('#facturacion_table tbody').empty();
    }
  
    selectedIds.clear();
    actualizarSeleccionInfo();
  
    facturacionTable = $('#facturacion_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        paging: true,
        pageLength: 100,
        deferRender: true,
        order: [[1, 'desc']],
        ajax: {
            url: '/facturacion/transacciones',
            type: 'POST', 
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                fecha_inicio: fechaInicio,
                fecha_final:  fechaFinal,
                payment_type: paymentType,
                cajero:       cajero,
            },
            dataSrc: function(json) {
                Swal.close();
                const data = json.data || [];
                actualizarResumen(data);
                document.getElementById('summaryCards').style.display = 'flex';
                return data;
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las transacciones.' });
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(d, t, row) {
                    if (row.bloqueada) return '';
                    const checked = selectedIds.has(row.local_transaction_id) ? 'checked' : '';
                    return `<input type="checkbox" class="row-check" data-id="${row.local_transaction_id}" data-total="${row.total}" ${checked}>`;
                }
            },
            { data: 'fecha' },
            { data: 'hora' },
            { data: '_id', render: (d) => d ? `<code style="font-size:.68rem;">${d}</code>` : '' },
            { data: 'cajero', render: (d) => d ? `<code>${d}</code>` : '' },
            { data: 'transaction_type_nombre' },
            { data: 'payment_type_nombre' },
            {
                data: 'total',
                className: 'text-end fw-bold',
                render: (d) => '$' + parseFloat(d).toLocaleString('es-MX', { minimumFractionDigits: 2 })
            },
            {
                data: 'estatus_factura',
                className: 'text-center',
                render: (d, t, row) => {
                    if (d === 'pendiente') return '<span class="badge badge-pendiente">Pendiente</span>';
                    if (d === 'global')    return `<span class="badge badge-global">En factura global<br><small>${row.factura_global_nombre || ''}</small></span>`;
                    return '<span class="badge badge-individual">Ya facturada</span>';
                }
            },
            {
                data: 'cadena_facturacion',
                render: (d) => d ? `<small class="text-muted" style="font-size:.68rem;word-break:break-all;">${d.substring(0,40)}...</small>` : ''
            },
        ],
        rowCallback: function(row, data) {
            if (data.bloqueada) $(row).addClass('bloqueada');
        },
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                className: 'btn btn-success buttons-excel',
                filename: 'Transacciones_Facturacion',
                exportOptions: { columns: [1,2,3,4,5,6,7,8] }
            },
            {
                extend: 'copy',
                text: '<i class="ti ti-copy me-1"></i>Copiar',
                className: 'btn btn-warning buttons-copy',
            },
        ],
        initComplete: function() { Swal.close(); }
    });

    // Delegación de eventos para checkboxes
    $('#facturacion_table tbody').off('change', '.row-check').on('change', '.row-check', function() {
        const id    = parseInt($(this).data('id'));
        const total = parseFloat($(this).data('total'));
        if (this.checked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
            document.getElementById('selectAll').checked = false;
        }
        actualizarSeleccionInfo();
    });

    // Seleccionar todos los pendientes visibles
    document.getElementById('selectAll').onchange = function() {
        const checked = this.checked;
        $('#facturacion_table tbody .row-check').each(function() {
            const id    = parseInt($(this).data('id'));
            const total = parseFloat($(this).data('total'));
            if (checked) {
                this.checked = true;
                selectedIds.add(id);
            } else {
                this.checked = false;
                selectedIds.delete(id);
            }
        });
        actualizarSeleccionInfo();
    };
}

function actualizarResumen(data) {
    const total      = data.length;
    const pendiente  = data.filter(r => r.estatus_factura === 'pendiente').length;
    const global     = data.filter(r => r.estatus_factura === 'global').length;
    const individual = data.filter(r => r.estatus_factura === 'individual').length;

    document.getElementById('statTotal').textContent      = total.toLocaleString();
    document.getElementById('statPendiente').textContent  = pendiente.toLocaleString();
    document.getElementById('statGlobal').textContent     = global.toLocaleString();
    document.getElementById('statIndividual').textContent = individual.toLocaleString();
}

function actualizarSeleccionInfo() {
    const count = selectedIds.size;
    const btnGenerar = document.getElementById('btnGenerar');
    const infoDiv    = document.getElementById('seleccionInfo');
    const infoTexto  = document.getElementById('seleccionTexto');

    if (count > 0) {
        // Calcular total seleccionado
        let totalSel = 0;
        if (facturacionTable) {
            facturacionTable.rows().data().each(function(row) {
                if (selectedIds.has(row.local_transaction_id)) {
                    totalSel += parseFloat(row.total);
                }
            });
        }
        const fmt = '$' + totalSel.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        infoTexto.textContent = `${count} transacción(es) seleccionada(s) — ${fmt}`;
        infoDiv.style.display    = 'block';
        btnGenerar.style.display = 'block';
    } else {
        infoDiv.style.display    = 'none';
        btnGenerar.style.display = 'none';
    }
}

function limpiarSeleccion() {
    selectedIds.clear();
    $('#facturacion_table tbody .row-check').prop('checked', false);
    document.getElementById('selectAll').checked = false;
    actualizarSeleccionInfo();
}

// ─────────────────────────────────────────────────────────────────────────────
// GENERAR FACTURA GLOBAL
// ─────────────────────────────────────────────────────────────────────────────

function generarFactura() {
    if (selectedIds.size === 0) return;

    const periodicidad = document.getElementById('periodicidad').value;

    Swal.fire({
        title: '¿Generar Factura Global?',
        html: `Se facturarán <strong>${selectedIds.size}</strong> transacción(es) con periodicidad <strong>${periodicidad}</strong>.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText:  'Cancelar',
        confirmButtonColor: '#198754',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Generando factura...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url:  '/facturacion/generar',
            type: 'POST',
            data: {
                _token:       $('meta[name="csrf-token"]').attr('content'),
                ids:          Array.from(selectedIds),
                periodicidad: periodicidad,
            },
            success: function(resp) {
                Swal.close();
                const count = resp.invoices ? resp.invoices.length : 0;
                Swal.fire({
                    icon: 'success',
                    title: 'Factura(s) generada(s)',
                    html: `Se generaron <strong>${count}</strong> factura(s) global(es).<br><small class="text-muted">Las transacciones ahora aparecen bloqueadas.</small>`,
                }).then(() => {
                    limpiarSeleccion();
                    buscarTransacciones();
                });
            },
            error: function(xhr) {
                Swal.close();
                const msg = xhr.responseJSON?.error || 'Error al generar la factura.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB HISTORIAL
// ─────────────────────────────────────────────────────────────────────────────

document.getElementById('tabHistorialLink')?.addEventListener('click', function() {
    if (!historialTable) {
        cargarHistorial();
    }
});

function cargarHistorial() {
    Swal.fire({ title: 'Cargando historial...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    historialTable = $('#historial_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        paging: true,
        pageLength: 25,
        order: [[7, 'desc']],
        ajax: {
            url: '/facturacion/historial',
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            dataSrc: function(json) {
                Swal.close();
                return json.data || [];
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el historial.' });
            }
        },
        columns: [
            { data: 'id', className: 'text-center' },
            {
                data: 'uuid',
                render: (d) => d ? `<small style="font-size:.68rem;">${d}</small>` : '<span class="text-muted">—</span>'
            },
            { data: 'payment_type_nombre' },
            {
                data: 'total',
                className: 'text-end fw-bold',
                render: (d) => '$' + parseFloat(d).toLocaleString('es-MX', { minimumFractionDigits: 2 })
            },
            { data: 'num_transacciones', className: 'text-center' },
            { data: 'start_date_group', render: (d) => d ? d.substring(0, 10) : '' },
            { data: 'end_date_group',   render: (d) => d ? d.substring(0, 10) : '' },
            { data: 'created_at',       render: (d) => d ? d.substring(0, 16).replace('T', ' ') : '' },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(d, t, row) {
                    const fname = encodeURIComponent(row.file_name || row.name);
                    let btns = '';

                    if (row.cancelada_at) {
                        btns += `<span class="badge bg-danger">Cancelada</span>`;
                    } else {
                        btns += `
                            <a href="/facturacion/download/xml/${fname}" class="btn btn-sm btn-outline-primary me-1" title="XML">
                                <i class="bi bi-file-code"></i> XML
                            </a>
                            <a href="/facturacion/download/pdf/${fname}" class="btn btn-sm btn-outline-danger me-1" title="PDF">
                                <i class="bi bi-file-pdf"></i> PDF
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="cancelarFactura(${row.id}, '${row.uuid || ''}')" title="Cancelar">
                                <i class="bi bi-x-circle"></i>
                            </button>`;
                    }
                    return btns;
                }
            },
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                className: 'btn btn-success buttons-excel',
                filename: 'Historial_Facturas_Globales',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
        ],
        initComplete: function() { Swal.close(); }
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// CANCELAR FACTURA GLOBAL
// ─────────────────────────────────────────────────────────────────────────────

function cancelarFactura(id, uuid) {
    const uuidInfo = uuid ? `<br><small class="text-muted">UUID: ${uuid}</small>` : '<br><small class="text-warning">Sin UUID registrado — puede fallar.</small>';

    Swal.fire({
        title: '¿Cancelar factura?',
        html: `Esta acción cancelará la factura en el SAT con motivo <strong>02</strong> (error sin relación) y liberará las transacciones para que puedan refacturarse.${uuidInfo}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText:  'No',
        confirmButtonColor: '#dc3545',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Cancelando...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url:  `/facturacion/cancelar/${id}`,
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(resp) {
                Swal.fire({ icon: 'success', title: 'Cancelada', text: resp.message });
                // Recargar historial
                historialTable.destroy();
                historialTable = null;
                cargarHistorial();
            },
            error: function(xhr) {
                Swal.close();
                const resp = xhr.responseJSON || {};
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cancelar',
                    html: `<b>${resp.error || 'Error desconocido'}</b>` +
                          (resp.detalle ? `<br><pre style="font-size:.7rem;text-align:left;max-height:200px;overflow:auto;">${JSON.stringify(resp.detalle, null, 2)}</pre>` : ''),
                });
            }
        });
    });
}
