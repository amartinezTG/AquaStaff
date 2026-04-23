let individualTable = null;
let selectedIds     = new Set();
let rfcSearchTimer  = null;

// ─────────────────────────────────────────────────────────────────────────────
// BUSCAR TRANSACCIONES
// ─────────────────────────────────────────────────────────────────────────────
  
function buscarTransacciones() {
    const fechaInicio      = document.getElementById('fechaInicio').value;
    const fechaFinal       = document.getElementById('fechaFinal').value;
    const transactionType  = document.getElementById('transactionType').value;
    const paymentType      = document.getElementById('paymentType').value;
    const cajero           = document.getElementById('cajeroFiltro').value.trim();
    const estatus          = document.getElementById('estatusFiltro').value;

    if (!fechaInicio || !fechaFinal) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Selecciona fecha de inicio y fin.' });
        return;
    }

    Swal.fire({ title: 'Cargando...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    if (individualTable) {
        individualTable.destroy();
        $('#individual_table tbody').empty();
    } 

    selectedIds.clear();
    actualizarSeleccionInfo();

    individualTable = $('#individual_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        paging: true,
        scrollY: '55vh',
        scrollCollapse: true,
        scroller: true,
        deferRender: true,
        order: [[1, 'desc']],
        ajax: {
            url: '/facturacion-individual/transacciones',
            type: 'POST',
            data: {
                _token:           $('meta[name="csrf-token"]').attr('content'),
                fecha_inicio:     fechaInicio,
                fecha_final:      fechaFinal,
                transaction_type: transactionType,
                payment_type:     paymentType,
                cajero:           cajero,
            },
            dataSrc: function (json) {
                Swal.close();
                const all = json.data || [];
                actualizarResumen(all);
                document.getElementById('summaryCards').style.display = 'flex';
                return estatus ? all.filter(r => r.estatus_factura === estatus) : all;
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las transacciones.' });
            }
        },
        columns: [
            {
                data: null, orderable: false, className: 'text-center',
                render: function (d, t, row) {
                    if (row.bloqueada) return '';
                    const checked = selectedIds.has(row.local_transaction_id) ? 'checked' : '';
                    return `<input type="checkbox" class="row-check"
                                data-id="${row.local_transaction_id}"
                                data-total="${row.total}"
                                data-tipo="${row.transaction_type}"
                                ${checked}>`;
                }
            },
            { data: 'fecha' },
            { data: 'hora' },
            { data: '_id', render: (d) => d ? `<code style="font-size:.68rem;">${d}</code>` : '' },
            { data: 'cajero', render: (d) => d ? `<code>${d}</code>` : '<span class="text-muted">—</span>' },
            {
                data: 'transaction_type_nombre',
                render: (d, t, row) => {
                    const colors = { 0: '#198754', 1: '#0d6efd', 2: '#fd7e14' };
                    const c = colors[row.transaction_type] || '#6c757d';
                    return `<span class="badge" style="background:${c};">${d}</span>`;
                }
            },
            { data: 'payment_type_nombre' },
            {
                data: 'total', className: 'text-end fw-bold',
                render: (d) => '$' + parseFloat(d).toLocaleString('es-MX', { minimumFractionDigits: 2 })
            },
            {
                data: 'estatus_factura', className: 'text-center',
                render: (d) => {
                    if (d === 'pendiente')  return '<span class="badge badge-pendiente">Pendiente</span>';
                    if (d === 'global')     return '<span class="badge badge-global">En factura global</span>';
                    return '<span class="badge badge-individual">Ya facturada</span>';
                }
            },
            {
                data: null, className: 'text-center',
                render: (d, t, row) => {
                    if (row.estatus_factura === 'individual') {
                        const rfc    = row.facturado_rfc    || '';
                        const nombre = row.facturado_nombre || '';
                        const fname  = encodeURIComponent(row.cadena_facturacion || 'IND_' + row.local_transaction_id);
                        return `<small style="font-size:.70rem;">
                                    <span class="fw-bold text-purple">${rfc}</span><br>${nombre}
                                </small>
                                <div class="mt-1">
                                    <a href="/facturacion-individual/download/pdf/${fname}" class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size:.68rem;" title="PDF">PDF</a>
                                    <a href="/facturacion-individual/download/xml/${fname}" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size:.68rem;" title="XML">XML</a>
                                    <button onclick="cancelarFacturaIndividual(${row.local_transaction_id})" class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:.68rem;" title="Cancelar factura">Cancelar</button>
                                </div>`;
                    }
                    if (row.estatus_factura === 'global') {
                        return '<small class="text-warning fw-semibold" style="font-size:.70rem;">En factura global</small>';
                    }
                    return '<span class="text-muted" style="font-size:.72rem;">—</span>';
                }
            },
        ],
        createdRow: function (row, data) {
            if (data.bloqueada) row.classList.add('bloqueada');
        },
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                className: 'btn btn-success buttons-excel',
                filename: 'Facturacion_Individual',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'copy',
                text: '<i class="ti ti-copy me-1"></i>Copiar',
                className: 'btn btn-warning buttons-copy',
            },
        ],
        initComplete: function () { Swal.close(); }
    });

    // Eventos checkboxes
    $('#individual_table tbody').off('change', '.row-check').on('change', '.row-check', function () {
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

    document.getElementById('selectAll').onchange = function () {
        if (this.checked) {
            individualTable.rows().data().each(function (row) {
                if (!row.bloqueada) selectedIds.add(row.local_transaction_id);
            });
            $('#individual_table tbody .row-check').prop('checked', true);
        } else {
            selectedIds.clear();
            $('#individual_table tbody .row-check').prop('checked', false);
        }
        actualizarSeleccionInfo();
    };
}

function actualizarResumen(data) {
    const filtered = data; // mostramos el total de lo que vino del servidor
    document.getElementById('statTotal').textContent      = filtered.length.toLocaleString();
    document.getElementById('statPendiente').textContent  = filtered.filter(r => r.estatus_factura === 'pendiente').length.toLocaleString();
    document.getElementById('statGlobal').textContent     = filtered.filter(r => r.estatus_factura === 'global').length.toLocaleString();
    document.getElementById('statIndividual').textContent = filtered.filter(r => r.estatus_factura === 'individual').length.toLocaleString();
}

function actualizarSeleccionInfo() {
    const count      = selectedIds.size;
    const btnFacturar = document.getElementById('btnFacturar');
    const infoDiv    = document.getElementById('seleccionInfo');

    if (count > 0) {
        let totalSel = 0;
        if (individualTable) {
            individualTable.rows().data().each(function (row) {
                if (selectedIds.has(row.local_transaction_id)) totalSel += parseFloat(row.total);
            });
        }
        const fmt = '$' + totalSel.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        document.getElementById('seleccionTexto').textContent = `${count} transacción(es) — ${fmt}`;
        infoDiv.style.display     = 'block';
        btnFacturar.style.display = 'block';
    } else {
        infoDiv.style.display     = 'none';
        btnFacturar.style.display = 'none';
    }
}

function limpiarSeleccion() {
    selectedIds.clear();
    $('#individual_table tbody .row-check').prop('checked', false);
    document.getElementById('selectAll').checked = false;
    actualizarSeleccionInfo();
}

// ─────────────────────────────────────────────────────────────────────────────
// MODAL FACTURAR
// ─────────────────────────────────────────────────────────────────────────────

function abrirModalFacturar() {
    if (selectedIds.size === 0) return;

    let totalSel = 0;
    if (individualTable) {
        individualTable.rows().data().each(function (row) {
            if (selectedIds.has(row.local_transaction_id)) totalSel += parseFloat(row.total);
        });
    }
    const fmt = '$' + totalSel.toLocaleString('es-MX', { minimumFractionDigits: 2 });

    // Fecha default: ahora menos 3 minutos (mismo margen que el controller)
    const ahoraLocal = new Date(new Date().getTime() - 3 * 60 * 1000);
    const pad = n => String(n).padStart(2, '0');
    const fechaDefault = `${ahoraLocal.getFullYear()}-${pad(ahoraLocal.getMonth()+1)}-${pad(ahoraLocal.getDate())}T${pad(ahoraLocal.getHours())}:${pad(ahoraLocal.getMinutes())}`;

    Swal.fire({
        title: 'Generar Facturas Individuales',
        width: 720,
        html: `
            <div class="text-start" style="font-size:.85rem;">
                <div class="mb-3 p-2 rounded" style="background:#f3eeff;">
                    <span class="text-muted">Transacciones:</span> <strong>${selectedIds.size}</strong>
                    &nbsp;|&nbsp;
                    <span class="text-muted">Total:</span> <strong>${fmt}</strong>
                    &nbsp;|&nbsp;
                    <span class="text-warning fw-semibold">Se generará 1 CFDI por transacción</span>
                </div>

                <div class="mb-2">
                    <label class="form-label mb-1 fw-semibold" style="font-size:.8rem;">
                        Fecha de emisión del CFDI <span class="text-danger">*</span>
                        <span class="text-muted fw-normal">(máx. 72 hrs atrás desde ahora)</span>
                    </label>
                    <input type="datetime-local" id="swal-fecha-emision" class="form-control form-control-sm" value="${fechaDefault}">
                </div>

                <hr class="my-2">
                <h6 class="fw-bold" style="color:#6f42c1;">Datos fiscales del receptor</h6>

                <!-- Buscador RFC -->
                <div class="mb-2 position-relative">
                    <label class="form-label mb-1 fw-semibold" style="font-size:.8rem;">Buscar RFC o razón social existente</label>
                    <input type="text" id="swal-rfc-search" class="form-control form-control-sm"
                        placeholder="Escribe mínimo 3 caracteres..." autocomplete="off">
                    <div id="rfc-suggestions"></div>
                </div>

                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label mb-1" style="font-size:.78rem;">RFC <span class="text-danger">*</span></label>
                        <input type="text" id="swal-rfc" class="form-control form-control-sm text-uppercase"
                            placeholder="RFC del receptor" maxlength="13">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label mb-1" style="font-size:.78rem;">Razón Social <span class="text-danger">*</span></label>
                        <input type="text" id="swal-razon" class="form-control form-control-sm text-uppercase"
                            placeholder="Nombre o razón social">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1" style="font-size:.78rem;">Régimen Fiscal <span class="text-danger">*</span></label>
                        <select id="swal-regimen" class="form-select form-select-sm">
                            <option value="601">601 - General de Ley PM</option>
                            <option value="605">605 - Sueldos y Salarios</option>
                            <option value="606">606 - Arrendamiento</option>
                            <option value="612">612 - PF Act. Emp. y Prof.</option>
                            <option value="616">616 - Sin obligaciones fiscales</option>
                            <option value="621">621 - Incorporación Fiscal</option>
                            <option value="626">626 - RESICO</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1" style="font-size:.78rem;">Uso CFDI <span class="text-danger">*</span></label>
                        <select id="swal-cfdi-use" class="form-select form-select-sm">
                            <option value="G01">G01 - Adquisición de mercancias</option>
                            <option value="G03" selected>G03 - Gastos en general</option>
                            <option value="P01">P01 - Por definir</option>
                            <option value="S01">S01 - Sin efectos fiscales</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1" style="font-size:.78rem;">C.P. <span class="text-danger">*</span></label>
                        <input type="text" id="swal-cp" class="form-control form-control-sm"
                            placeholder="Código postal" maxlength="5" value="32030">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label mb-1" style="font-size:.78rem;">Email (para envío de archivos)</label>
                        <input type="email" id="swal-email" class="form-control form-control-sm"
                            placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <hr class="my-2">
                <div class="mb-1">
                    <label class="form-label mb-1" style="font-size:.78rem;">Concepto (opcional — si vacío, se usa el tipo de transacción)</label>
                    <div class="d-flex flex-wrap gap-1 mb-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                            onclick="document.getElementById('swal-concepto').value='RENOVACION DE MEMBRESIA'">Renovación</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                            onclick="document.getElementById('swal-concepto').value='COMPRA DE MEMBRESIA'">Compra membresía</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                            onclick="document.getElementById('swal-concepto').value='SERVICIO DE LAVADO'">Lavado</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"
                            onclick="document.getElementById('swal-concepto').value=''">Auto</button>
                    </div>
                    <input type="text" id="swal-concepto" class="form-control form-control-sm text-uppercase"
                        placeholder="Dejar vacío para automático por tipo de Tx" maxlength="100">
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Generar facturas',
        cancelButtonText:  'Cancelar',
        confirmButtonColor: '#6f42c1',
        didOpen: () => {
            // Autocomplete para RFC
            const searchInput = document.getElementById('swal-rfc-search');
            const suggestionsBox = document.getElementById('rfc-suggestions');

            searchInput.addEventListener('input', function () {
                clearTimeout(rfcSearchTimer);
                const q = this.value.trim();
                if (q.length < 3) {
                    suggestionsBox.innerHTML = '';
                    return;
                }
                rfcSearchTimer = setTimeout(() => {
                    $.ajax({
                        url: '/facturacion-individual/buscar-cuenta',
                        type: 'GET',
                        data: { _token: $('meta[name="csrf-token"]').attr('content'), q },
                        success: function (data) {
                            suggestionsBox.innerHTML = '';
                            if (!data.length) {
                                suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">Sin resultados</div>';
                                return;
                            }
                            data.forEach(function (item) {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.innerHTML = `<strong>${item.rfc}</strong> — ${item.company_name}`;
                                div.onclick = function () {
                                    document.getElementById('swal-rfc').value       = item.rfc;
                                    document.getElementById('swal-razon').value     = item.company_name;
                                    document.getElementById('swal-regimen').value   = item.tax_regime   || '601';
                                    document.getElementById('swal-cfdi-use').value  = item.cfdi_use     || 'G03';
                                    document.getElementById('swal-cp').value        = item.zip_code     || '32030';
                                    document.getElementById('swal-email').value     = item.email        || '';
                                    suggestionsBox.innerHTML = '';
                                    searchInput.value = item.rfc + ' — ' + item.company_name;
                                };
                                suggestionsBox.appendChild(div);
                            });
                        }
                    });
                }, 300);
            });
        },
        willClose: () => {
            document.getElementById('rfc-suggestions').innerHTML = '';
        },
        preConfirm: () => {
            const rfc           = document.getElementById('swal-rfc').value.trim().toUpperCase();
            const companyName   = document.getElementById('swal-razon').value.trim().toUpperCase();
            const taxRegime     = document.getElementById('swal-regimen').value;
            const cfdiUse       = document.getElementById('swal-cfdi-use').value;
            const zipCode       = document.getElementById('swal-cp').value.trim();
            const email         = document.getElementById('swal-email').value.trim();
            const concepto      = document.getElementById('swal-concepto').value.trim().toUpperCase();
            const fechaEmision  = document.getElementById('swal-fecha-emision').value;

            if (!rfc) {
                Swal.showValidationMessage('El RFC es requerido.');
                return false;
            }
            if (!companyName) {
                Swal.showValidationMessage('La razón social es requerida.');
                return false;
            }
            if (!zipCode || zipCode.length < 5) {
                Swal.showValidationMessage('El código postal es requerido (5 dígitos).');
                return false;
            }
            if (!fechaEmision) {
                Swal.showValidationMessage('La fecha de emisión es requerida.');
                return false;
            }
            // Validar que no sea mayor a 72 horas atrás
            const ahora    = new Date();
            const fechaSel = new Date(fechaEmision);
            const diffHrs  = (ahora - fechaSel) / 1000 / 3600;
            if (diffHrs > 72) {
                Swal.showValidationMessage('La fecha de emisión no puede ser mayor a 72 horas antes de ahora (límite del SAT).');
                return false;
            }
            if (fechaSel > ahora) {
                Swal.showValidationMessage('La fecha de emisión no puede ser futura.');
                return false;
            }

            return { rfc, companyName, taxRegime, cfdiUse, zipCode, email, concepto, fechaEmision };
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        const { rfc, companyName, taxRegime, cfdiUse, zipCode, email, concepto, fechaEmision } = result.value;

        Swal.fire({
            title: 'Generando facturas...',
            html: `<p style="font-size:.85rem;">Timbrando ${selectedIds.size} CFDI(s). Por favor espera.</p>`,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '/facturacion-individual/generar',
            type: 'POST',
            data: {
                _token:       $('meta[name="csrf-token"]').attr('content'),
                ids:           Array.from(selectedIds),
                rfc:           rfc,
                company_name:  companyName,
                tax_regime:    taxRegime,
                cfdi_use:      cfdiUse,
                zip_code:      zipCode,
                email:         email,
                concepto:      concepto,
                fecha_emision: fechaEmision,
            },
            success: function (resp) {
                Swal.close();
                const count = resp.generadas ? resp.generadas.length : 0;
                let linksHtml = '';
                if (resp.generadas && resp.generadas.length > 0) {
                    linksHtml = '<div class="mt-2" style="max-height:150px;overflow-y:auto;">';
                    resp.generadas.forEach(function (g) {
                        const fname = encodeURIComponent(g.file_name);
                        linksHtml += `<div class="d-flex justify-content-between align-items-center border-bottom py-1" style="font-size:.78rem;">
                            <span><code>${g._id || g.local_transaction_id}</code> — $${parseFloat(g.total).toFixed(2)}</span>
                            <span>
                                <a href="/facturacion-individual/download/pdf/${fname}" class="btn btn-sm btn-outline-danger py-0 px-1 me-1" style="font-size:.68rem;">PDF</a>
                                <a href="/facturacion-individual/download/xml/${fname}" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size:.68rem;">XML</a>
                            </span>
                        </div>`;
                    });
                    linksHtml += '</div>';
                }
                Swal.fire({
                    icon: 'success',
                    title: `${count} factura(s) generada(s)`,
                    html: `<p style="font-size:.85rem;">${resp.message}</p>${linksHtml}`,
                    width: 640,
                }).then(() => {
                    limpiarSeleccion();
                    buscarTransacciones();
                });
            },
            error: function (xhr) {
                Swal.close();
                const msg = xhr.responseJSON?.error || 'Error al generar las facturas.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// CANCELAR FACTURA INDIVIDUAL
// ─────────────────────────────────────────────────────────────────────────────

function cancelarFacturaIndividual(localTransactionId) {
    Swal.fire({
        icon: 'warning',
        title: '¿Cancelar factura?',
        html: `<p style="font-size:.85rem;">Se enviará la solicitud de cancelación al SAT.<br>Esta acción <strong>no se puede deshacer</strong>.</p>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        confirmButtonColor: '#dc3545',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Cancelando...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '/facturacion-individual/cancelar/' + localTransactionId,
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (resp) {
                Swal.fire({ icon: 'success', title: 'Cancelada', text: resp.message })
                    .then(() => buscarTransacciones());
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.error || 'Error al cancelar la factura.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });
}
