let facturacionTable = null;
let historialTable   = null;
let selectedIds      = new Set();
let allLoadedData    = [];

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
                const filtered = estatus ? all.filter(r => r.estatus_factura === estatus) : all;
                // Guardar copia completa para selectAll y grupos (rows().data() solo devuelve DOM visible con Scroller)
                allLoadedData = filtered;
                return filtered;
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

    // Seleccionar todos los pendientes (usando allLoadedData, no rows().data() que es limitado por Scroller)
    document.getElementById('selectAll').onchange = function() {
        const checked = this.checked;
        if (checked) {
            allLoadedData.forEach(function(row) {
                if (!row.bloqueada) selectedIds.add(row.local_transaction_id);
            });
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
        let totalSel = 0;
        allLoadedData.forEach(function(row) {
            if (selectedIds.has(row.local_transaction_id)) {
                totalSel += parseFloat(row.total);
            }
        });
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

    const nombresPago = { 0: 'Efectivo', 1: 'Débito', 2: 'Crédito' };
    const conceptosPago = {
        0: 'INGRESOS POR SERVICIOS DE LAVADO EN EFECTIVO',
        1: 'INGRESOS POR SERVICIOS DE LAVADO CON TARJETA DE DEBITO',
        2: 'INGRESOS POR SERVICIOS DE LAVADO CON TARJETA DE CREDITO',
    };
    const sugerencias = {
        '0': conceptosPago[0],
        '1': conceptosPago[1],
        '2': conceptosPago[2],
        '':  'INGRESOS POR SERVICIOS DE LAVADO',
    };

    const conceptoDefault = sugerencias[paymentType] ?? sugerencias[''];
    const pad = n => String(n).padStart(2, '0');
    // Default: último día del período filtrado a las 23:59, no la hora actual
    const fechaFinalFiltro = document.getElementById('fechaFinal').value;
    const baseDate = fechaFinalFiltro ? new Date(fechaFinalFiltro + 'T23:59') : new Date();
    const fechaDefault = `${baseDate.getFullYear()}-${pad(baseDate.getMonth()+1)}-${pad(baseDate.getDate())}T23:59`;

    // Agrupar IDs seleccionados por PaymentType usando allLoadedData (no rows().data() que Scroller limita al DOM)
    const grupos = { 0: [], 1: [], 2: [] };
    allLoadedData.forEach(function(row) {
        if (selectedIds.has(row.local_transaction_id)) {
            const pt = parseInt(row.payment_type);
            if (grupos[pt] !== undefined) grupos[pt].push(row.local_transaction_id);
        }
    });
    const gruposActivos = Object.entries(grupos).filter(([, ids]) => ids.length > 0);

    Swal.fire({
        title: 'Generar Factura Global',
        width: 660,
        html: `
            <div class="text-start" style="font-size:.85rem;">
                <div class="mb-3 p-2 rounded" style="background:#f8f9fa;">
                    <span class="text-muted">Transacciones:</span> <strong>${selectedIds.size}</strong>
                    &nbsp;|&nbsp;
                    <span class="text-muted">Grupos:</span> <strong>${gruposActivos.length}</strong>
                    (${gruposActivos.map(([pt, ids]) => `${nombresPago[pt]}: ${ids.length}`).join(', ')})
                    &nbsp;|&nbsp;
                    <span class="text-muted">Periodicidad:</span> <strong>${periodicidad}</strong>
                </div>
                <div class="mb-3">
                    <label class="fw-bold mb-1">Fecha de emisión del CFDI</label>
                    <input type="datetime-local" id="swal-fecha" class="form-control form-control-sm" value="${fechaDefault}">
                    <small class="text-muted">Debe estar dentro de las últimas 72 hrs. Por defecto: último día del período a las 23:59.</small>
                </div>
                <hr class="my-2">
                <label class="fw-bold mb-1">Concepto base del CFDI</label>
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
                <div class="text-muted mt-1" style="font-size:.72rem;">
                    Máx. 255 caracteres. Se enviará una factura por grupo de forma de pago.
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText:  'Cancelar',
        confirmButtonColor: '#198754',
        preConfirm: () => {
            const fecha    = document.getElementById('swal-fecha').value;
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
    }).then(async result => {
        if (!result.isConfirmed) return;

        const { fecha: fechaEmision, concepto } = result.value;
        const token    = $('meta[name="csrf-token"]').attr('content');
        const total    = gruposActivos.length;
        let generadas  = 0;
        let facturas   = [];

        for (let i = 0; i < gruposActivos.length; i++) {
            const [pt, ids] = gruposActivos[i];
            const nombreGrupo = nombresPago[pt] ?? 'Grupo';
            const conceptoGrupo = conceptosPago[pt] ?? concepto;

            Swal.fire({
                title: `Generando ${i + 1} de ${total}...`,
                html: `<span style="font-size:.85rem;">
                    <strong>${nombreGrupo}</strong> — ${ids.length.toLocaleString()} transacciones
                    <br><small class="text-muted">Por favor espera, esto puede tardar unos segundos.</small>
                </span>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const resp = await $.ajax({
                    url:  '/facturacion/generar',
                    type: 'POST',
                    timeout: 300000,
                    data: {
                        _token:        token,
                        ids:           JSON.stringify(ids),
                        periodicidad:  periodicidad,
                        fecha_emision: fechaEmision,
                        concepto:      conceptoGrupo,
                    },
                });

                generadas++;
                facturas = facturas.concat(resp.invoices || []);

                // Pausa entre grupos para no saturar
                if (i < gruposActivos.length - 1) {
                    await new Promise(r => setTimeout(r, 1500));
                }

            } catch (xhr) {
                Swal.close();
                const msg = xhr.responseJSON?.error || `Error al generar grupo ${nombreGrupo}.`;
                Swal.fire({
                    icon: 'error',
                    title: `Error en grupo ${i + 1} de ${total} (${nombreGrupo})`,
                    html: `${msg}${generadas > 0 ? `<br><small class="text-muted">Los ${generadas} grupo(s) anteriores ya fueron guardados.</small>` : ''}`,
                });
                if (generadas > 0) {
                    limpiarSeleccion();
                    buscarTransacciones();
                }
                return;
            }
        }

        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Factura(s) generada(s)',
            html: `Se generaron <strong>${facturas.length}</strong> factura(s) global(es) en <strong>${generadas}</strong> grupo(s).<br><small class="text-muted">Las transacciones ahora aparecen bloqueadas.</small>`,
        }).then(() => {
            window.location.reload();
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

document.getElementById('btnRecargarHistorial')?.addEventListener('click', function() {
    if (historialTable) {
        historialTable.destroy();
        historialTable = null;
    }
    cargarHistorial();
});

function cargarHistorial() {
    Swal.fire({ title: 'Cargando historial...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(f => f._historialCanceladas !== true);

    const filtroCanceladas = function(settings, data, dataIndex, rowData) {
        if (settings.nTable.id !== 'historial_table') return true;
        const ocultar = document.getElementById('ocultarCanceladas')?.checked;
        return ocultar ? !rowData.cancelada_at : true;
    };
    filtroCanceladas._historialCanceladas = true;
    $.fn.dataTable.ext.search.push(filtroCanceladas);

    historialTable = $('#historial_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        paging: true,
        pageLength: 25,
        orderCellsTop: true,
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
            { data: 'created_at',    render: (d) => d ? d.substring(0, 16).replace('T', ' ') : '' },
            { data: 'fecha_emision', render: (d) => d ? d.substring(0, 16).replace('T', ' ') : '<span class="text-muted">—</span>' },
            {
                data: 'generado_por',
                className: 'text-center',
                render: (d) => d ? `<small>${d}</small>` : '<span class="text-muted">—</span>'
            },
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
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
            },
        ],
        initComplete: function() {
            Swal.close();
            // Conectar inputs de la fila de filtros a cada columna
            $('#historial-filters th').each(function(i) {
                const input = $(this).find('input, select');
                if (!input.length) return;
                input.on('keyup change', function() {
                    historialTable.column(i).search(this.value).draw();
                });
            });
        }
    });

    // Checkbox ocultar canceladas
    document.getElementById('ocultarCanceladas')?.addEventListener('change', function() {
        historialTable.draw();
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
