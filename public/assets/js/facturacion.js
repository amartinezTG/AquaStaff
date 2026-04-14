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
    const estatus     = document.getElementById('estatusFiltro').value;

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
        scrollY: '60vh',
        scrollCollapse: true,
        scroller: true,
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
                const all  = json.data || [];
                actualizarResumen(all);
                document.getElementById('summaryCards').style.display = 'flex';
                // Filtrar por estatus si se seleccionó uno
                return estatus ? all.filter(r => r.estatus_factura === estatus) : all;
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
                render: (d) => {
                    if (d === 'pendiente')  return '<span class="badge badge-pendiente">Pendiente</span>';
                    if (d === 'global')     return '<span class="badge badge-global">En factura global</span>';
                    return '<span class="badge badge-individual">Ya facturada</span>';
                }
            },
            {
                data: 'factura_global_nombre',
                className: 'text-center',
                render: (d, t, row) => {
                    if (row.estatus_factura === 'global' && d) {
                        return `<small class="text-warning fw-semibold" style="font-size:.70rem;word-break:break-all;" title="${d}">${d}</small>`;
                    }
                    if (row.estatus_factura === 'individual') {
                        const rfc    = row.facturado_rfc    || '';
                        const nombre = row.facturado_nombre || '';
                        const uuid   = row.fiscal_invoice   || '';
                        const tip    = uuid ? `UUID: ${uuid}` : '';
                        return `<small class="text-primary" style="font-size:.70rem;" title="${tip}">
                                    <span class="fw-bold">${rfc}</span><br>${nombre}
                                </small>`;
                    }
                    return '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'cadena_facturacion',
                render: (d) => d ? `<small class="text-muted" style="font-size:.68rem;word-break:break-all;">${d.substring(0,40)}...</small>` : ''
            },
        ],
        createdRow: function(row, data) {
            if (data.bloqueada) row.classList.add('bloqueada');
        },
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                className: 'btn btn-success buttons-excel',
                filename: 'Transacciones_Facturacion',
                exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
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

    // Seleccionar todos los pendientes (todos los registros cargados)
    document.getElementById('selectAll').onchange = function() {
        const checked = this.checked;
        if (checked) {
            // Iterar sobre todos los datos del DataTable, no solo el DOM visible
            facturacionTable.rows().data().each(function(row) {
                if (!row.bloqueada) {
                    selectedIds.add(row.local_transaction_id);
                }
            });
            // Marcar checkboxes visibles en el DOM
            $('#facturacion_table tbody .row-check').prop('checked', true);
        } else {
            selectedIds.clear();
            $('#facturacion_table tbody .row-check').prop('checked', false);
        }
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
    const paymentType  = document.getElementById('paymentType').value;

    // Sugerencias por tipo de pago
    const sugerencias = {
        '0': 'INGRESOS POR SERVICIOS DE LAVADO EN EFECTIVO',
        '1': 'INGRESOS POR SERVICIOS DE LAVADO CON TARJETA DE DEBITO',
        '2': 'INGRESOS POR SERVICIOS DE LAVADO CON TARJETA DE CREDITO',
        '':  'INGRESOS POR SERVICIOS DE LAVADO',
    };

    const conceptoDefault = sugerencias[paymentType] ?? sugerencias[''];
    const ahora = new Date();
    const pad   = n => String(n).padStart(2, '0');
    const fechaDefault = `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T${pad(ahora.getHours())}:${pad(ahora.getMinutes())}`;

    Swal.fire({
        title: 'Generar Factura Global',
        width: 660,
        html: `
            <div class="text-start" style="font-size:.85rem;">
                <div class="mb-3 p-2 rounded" style="background:#f8f9fa;">
                    <span class="text-muted">Transacciones:</span> <strong>${selectedIds.size}</strong>
                    &nbsp;|&nbsp;
                    <span class="text-muted">Periodicidad:</span> <strong>${periodicidad}</strong>
                </div>
                <div class="mb-3">
                    <label class="fw-bold mb-1">Fecha de emisión</label>
                    <input type="datetime-local" id="swal-fecha" class="form-control form-control-sm" value="${fechaDefault}">
                </div>
                <hr class="my-2">
                <label class="fw-bold mb-1">Concepto del CFDI</label>
                <div class="mb-2 d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                        onclick="document.getElementById('swal-concepto').value='INGRESOS POR SERVICIOS DE LAVADO EN EFECTIVO'">
                        Efectivo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                        onclick="document.getElementById('swal-concepto').value='INGRESOS POR SERVICIOS DE LAVADO CON TARJETA DE DEBITO'">
                        Tarjeta Débito
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                        onclick="document.getElementById('swal-concepto').value='INGRESOS POR SERVICIOS DE LAVADO CON TARJETA DE CREDITO'">
                        Tarjeta Crédito
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                        onclick="document.getElementById('swal-concepto').value='INGRESOS POR SERVICIOS DE LAVADO'">
                        General
                    </button>
                </div>
                <textarea id="swal-concepto" class="form-control" rows="2" maxlength="255"
                    style="font-size:.82rem; text-transform:uppercase;"
                >${conceptoDefault}</textarea>
                <div class="text-muted mt-1" style="font-size:.72rem;">Máx. 255 caracteres</div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText:  'Cancelar',
        confirmButtonColor: '#198754',
        preConfirm: () => {
            const fecha   = document.getElementById('swal-fecha').value;
            const concepto = document.getElementById('swal-concepto').value.trim().toUpperCase();
            if (!fecha) {
                Swal.showValidationMessage('La fecha de emisión es requerida.');
                return false;
            }
            if (!concepto) {
                Swal.showValidationMessage('El concepto es requerido.');
                return false;
            }
            return { fecha, concepto };
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        const { fecha: fechaEmision, concepto } = result.value;

        Swal.fire({ title: 'Generando factura...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url:  '/facturacion/generar',
            type: 'POST',
            data: {
                _token:        $('meta[name="csrf-token"]').attr('content'),
                ids:           Array.from(selectedIds),
                periodicidad:  periodicidad,
                fecha_emision: fechaEmision,
                concepto:      concepto,
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
