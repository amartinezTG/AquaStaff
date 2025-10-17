/**
 * administracion.js
 * Script para gestión de auditoría de transacciones AquaCar
 */
console.log('administracion')



 function selectATM(atm) {
    selectedATM = atm;
    document.querySelectorAll('.atm-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.atm === atm);
    });
}

 async function loadAuditData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    if (!startDate || !endDate) {
        Swal.fire('Error', 'Debes seleccionar ambas fechas', 'error');
        return;
    }

    showLoading(true);

    try {
        const response = await fetch('/administracion/transaction-gaps-summary', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate,
                atm: selectedATM
            })
        });

        if (!response.ok) throw new Error('Error al cargar datos');

        const result = await response.json();
        
        if (result.success) {
            displayResults(result);
        } else {
            throw new Error(result.message || 'Error desconocido');
        }

    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
    } finally {
        showLoading(false);
    }
}
 function displayResults(result) {
    displayStatistics(result.estadisticas);
    displayTables(result.data);
    
    document.getElementById('stats_container').style.display = 'flex';
    document.getElementById('results_container').style.display = 'flex';
}

async function exportData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    if (!startDate || !endDate) {
        Swal.fire('Error', 'Debes seleccionar ambas fechas', 'error');
        return;
    }

    showLoading(true);

    try {
        const url = `/administracion/export-transaction-gaps?start_date=${startDate}&end_date=${endDate}&atm=${selectedATM}`;
        window.location.href = url;

        setTimeout(() => {
            showLoading(false);
            Swal.fire('Éxito', 'El archivo se ha descargado correctamente', 'success');
        }, 2000);

    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo exportar el archivo', 'error');
        showLoading(false);
    }
}

 async function showDetail(cajero, fecha) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();

    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    try {
        const response = await fetch('/administracion/transaction-gaps-detail', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                start_date: fecha,
                end_date: fecha,
                atm: cajero,
                limit: 500
            })
        });

        if (!response.ok) throw new Error('Error al cargar detalle');

        const result = await response.json();
        
        if (result.success) {
            displayDetail(result.data, cajero, fecha);
        }

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('detail_content').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> 
                Error al cargar el detalle de transacciones
            </div>
        `;
    }
}



function generateTable(records, cajero) {
    if (!records || records.length === 0) {
        return `
            <div class="alert alert-success alert-custom">
                <i class="bi bi-check-circle"></i> 
                <strong>¡Excelente!</strong> No se encontraron huecos en las transacciones para este período.
            </div>
        `;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-audit table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>ID Mínimo</th>
                        <th>ID Máximo</th>
                        <th>Trans. Reales</th>
                        <th>Trans. Esperadas</th>
                        <th>Faltantes</th>
                        <th>% Faltante</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;

    records.forEach(record => {
        const percentage = ((record.faltantes / record.esperados) * 100).toFixed(2);
        let badgeClass = 'badge-ok';
        let statusIcon = 'check-circle';
        let statusText = 'OK';

        if (record.faltantes > 0) {
            if (percentage > 10) {
                badgeClass = 'badge-critical';
                statusIcon = 'x-circle';
                statusText = 'CRÍTICO';
            } else if (percentage > 5) {
                badgeClass = 'badge-warning';
                statusIcon = 'exclamation-triangle';
                statusText = 'ALERTA';
            } else {
                badgeClass = 'badge-warning';
                statusIcon = 'info-circle';
                statusText = 'REVISAR';
            }
        }

        html += `
            <tr>
                <td><strong>${formatDate(record.dia)}</strong></td>
                <td>${record.lo}</td>
                <td>${record.hi}</td>
                <td><strong>${record.cnt}</strong></td>
                <td>${record.esperados}</td>
                <td><span class="badge ${badgeClass}">${record.faltantes}</span></td>
                <td>${percentage}%</td>
                <td>
                    <i class="bi bi-${statusIcon} me-1"></i>
                    ${statusText}
                </td>
                <td>
                    ${record.faltantes > 0 ? `
                        <button class="btn btn-sm btn-primary" 
                                onclick="showDetail('${cajero}', '${record.dia}')">
                            <i class="bi bi-eye"></i> Ver Detalle
                        </button>
                    ` : '<span class="text-muted">N/A</span>'}
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    return html;
}


 // Mostrar/ocultar loading
function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.toggle('active', show);
}
// Formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('es-MX', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function displayTables(data) {
    const tabsContainer = document.getElementById('atmTabs');
    const contentContainer = document.getElementById('atmTabContent');
    
    tabsContainer.innerHTML = '';
    contentContainer.innerHTML = '';

    let isFirst = true;

    Object.keys(data).forEach(cajero => {
        // Crear tab
        const tabId = `tab-${cajero}`;
        tabsContainer.innerHTML += `
            <li class="nav-item" role="presentation">
                <button class="nav-link ${isFirst ? 'active' : ''}" 
                        id="${tabId}-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#${tabId}" 
                        type="button">
                    <i class="bi bi-cash-coin"></i> ${cajero}
                </button>
            </li>
        `;

        // Crear contenido
        const tableHtml = generateTable(data[cajero], cajero);
        
        contentContainer.innerHTML += `
            <div class="tab-pane fade ${isFirst ? 'show active' : ''}" 
                    id="${tabId}" 
                    role="tabpanel">
                ${tableHtml}
            </div>
        `;

        isFirst = false;
    });
}
  function displayStatistics(stats) {
    const container = document.getElementById('stats_container');
    let html = '';

    Object.keys(stats).forEach(cajero => {
        const stat = stats[cajero];
        html += `
            <div class="col-lg-6 mb-4">
                <div class="card audit-card">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, ${cajero === 'AQUA01' ? '#3498db, #2980b9' : '#9b59b6, #8e44ad'});">
                        <h6 class="text-white mb-0"><i class="bi bi-cash-coin"></i> ${cajero}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stat-box ${stat.porcentaje_dias_con_problemas > 50 ? 'danger' : stat.porcentaje_dias_con_problemas > 20 ? 'warning' : 'success'}">
                                    <div class="stat-number">${stat.dias_con_huecos}</div>
                                    <div class="stat-label">Días con Huecos</div>
                                    <small>${stat.porcentaje_dias_con_problemas}% del total</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-box info">
                                    <div class="stat-number">${stat.total_transacciones_faltantes.toLocaleString()}</div>
                                    <div class="stat-label">Transacciones Faltantes</div>
                                    <small>${stat.porcentaje_transacciones_faltantes}% del total esperado</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="mb-2"><strong>Total días analizados:</strong> ${stat.total_dias}</p>
                            <p class="mb-2"><strong>Días sin problemas:</strong> ${stat.dias_sin_huecos}</p>
                            <p class="mb-0"><strong>Transacciones esperadas:</strong> ${stat.total_transacciones_esperadas.toLocaleString()}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    // Añadir después del título
    const existingContent = container.querySelectorAll('.col-12')[0];
    container.innerHTML = '';
    container.appendChild(existingContent);
    container.insertAdjacentHTML('beforeend', html);
}

function displayDetail(data, cajero, fecha) {
    if (!data || data.length === 0) {
        document.getElementById('detail_content').innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                No se encontraron huecos específicos para esta fecha.
            </div>
        `;
        return;
    }

    let html = `
        <div class="mb-3">
            <h6><strong>Cajero:</strong> ${cajero} | <strong>Fecha:</strong> ${formatDate(fecha)}</h6>
            <p class="text-muted">Total de huecos encontrados: ${data.length}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>ID Anterior</th>
                        <th>ID Esperado</th>
                        <th>ID Actual</th>
                        <th>IDs Faltantes</th>
                        <th>Fecha/Hora</th>
                    </tr>
                </thead>
                <tbody>
    `;

    data.forEach((record, index) => {
        const faltantes = [];
        for (let i = record.id_esperado; i < record.id_actual; i++) {
            faltantes.push(i);
        }

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${record.id_anterior}</td>
                <td><span class="badge bg-warning">${record.id_esperado}</span></td>
                <td>${record.id_actual}</td>
                <td>
                    <span class="badge bg-danger">${record.diferencia}</span>
                    <small class="text-muted d-block mt-1">
                        ${faltantes.length <= 5 ? faltantes.join(', ') : 
                            faltantes.slice(0, 5).join(', ') + '...'}
                    </small>
                </td>
                <td>${record.fecha_hora}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('detail_content').innerHTML = html;
}