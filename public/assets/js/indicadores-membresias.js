// assets/js/indicadores-membresias.js

let membershipsTable;
let membershipsData = [];

// Variables globales para las gráficas
let membershipDistributionChart;
let ordersPerMembershipChart;
let topClientsChart;

// Colores para las gráficas
const membershipColors = {
    'Express': '#ffc107',
    'Básico': '#17a2b8', 
    'Ultra': '#28a745',
    'Delux': '#dc3545',
    'N/A': '#6c757d'
};

/**
 * Función principal para cargar y mostrar la tabla de indicadores de membresías
 */
function indicadoresMembresiasTable() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFinal = document.getElementById('fecha_final').value;
    
    if (!fechaInicio || !fechaFinal) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos requeridos',
            text: 'Por favor selecciona ambas fechas'
        });
        return;
    }

    if (fechaInicio > fechaFinal) {
        Swal.fire({
            icon: 'error',
            title: 'Error en fechas',
            text: 'La fecha de inicio no puede ser mayor a la fecha final'
        });
        return;
    }

    // Mostrar loading
    showLoading();

    // Destruir tabla existente si existe
    if (membershipsTable) {
        membershipsTable.destroy();
    }

    // Inicializar DataTable
    membershipsTable = $('#indicadores_membresias_table').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,


        paging: false,
        info: true,           // opcional: muestra "Mostrando X registros"
        lengthChange: false,  // oculta el selector de "mostrar N"
        scrollY: '60vh',      // alto del área scroll; ajústalo a gusto (px, %, vh)
        scrollCollapse: true, // colapsa si hay pocos registros
        scrollX: true,        // opcional: scroll horizontal si hay muchas columnas
        deferRender: true,    // performance con muchos registros
        fixedHeader: false,   
        ajax: {
             url: '/indicadores/indicadores_membresias_table',
            // url: '/api/indicadores/membresias',
            type: 'POST',
            data: {
                fecha_inicio: fechaInicio,
                fecha_final: fechaFinal,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataSrc: function(json) {
                console.log('Datos recibidos:', json.data);
                membershipsData = Array.isArray(json.data) ? json.data : [];
                updateQuickSummary(membershipsData);
                // 🔁 siempre refrescar las gráficas con los nuevos datos
                if (membershipsData.length) {
                    generateCharts();   // cada generate* hace destroy() antes de crear
                }
                return membershipsData;
            },
            error: function(xhr, error, thrown) {
                console.error('Error al cargar datos:', error);
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar datos',
                    text: 'No se pudieron cargar los indicadores de membresías'
                });
            }
        },
        columns: [
            {data: 'cliente',name: 'cliente',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const clienteName = data || 'Sin nombre';
                        return `<div class="fw-bold text-truncate" title="${clienteName}" style="max-width: 200px;">
                                    ${clienteName === 'Sin nombre' ? '<span class="text-muted">Sin nombre</span>' : clienteName}
                                </div>`;
                    }
                    return data || 'Sin nombre';
                }
            },
            {data: 'UserId',name: 'UserId',},
            {data: 'package',name: 'package',
                render: function(data, type, row) {
                    const packageName = data || 'Sin paquete';
                    return packageName === 'Sin paquete' ? '<span class="text-muted">Sin paquete</span>' : packageName;
                }
            },
            { 
                data: 'package_name',
                name: 'package_name',
                render: function(data, type, row) {
                    // Asegurar que data sea un string válido
                    const membershipType = (data || 'N/A').toString();
                    let badgeClass = 'secondary';
                    
                    switch(membershipType) {
                        case 'Express':
                            badgeClass = 'warning';
                            break;
                        case 'Básico':
                            badgeClass = 'info';
                            break;
                        case 'Ultra':
                            badgeClass = 'success';
                            break;
                        case 'Delux':
                            badgeClass = 'danger';
                            break;
                        default:
                            badgeClass = 'secondary';
                    }
                    
                    return `<span class="badge bg-${badgeClass} membership-badge">${membershipType}</span>`;
                }
            },
            { 
                data: 'total_ordenes',
                name: 'total_ordenes',
                className: 'text-center fw-bold',
                render: function(data, type, row) {
                    // Manejar tanto 'total_ordenes' como 'total' por compatibilidad
                    const totalValue = parseInt(data || row.total || 0);
                    return `<span class="badge badge-counter bg-primary">${totalValue.toLocaleString()}</span>`;
                }
            },
            {data:'ticket_promedio', name:"ticket_promedio"}
        ],
            
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                className: 'btn btn-success buttons-excel',
                filename: function() {
                    return `Indicadores_Membresias_${fechaInicio}_${fechaFinal}`;
                },
                title: `Indicadores de Membresías del ${fechaInicio} al ${fechaFinal}`,
                // exportOptions: {
                //     columns: [0, 1, 2, 3, 4],
                //     format: {
                //         body: function(data, row, column, node) {
                //             // Limpiar HTML para Excel
                //             return $(node).text();
                //         }
                //     }
                // }
            },
            {
                extend: 'pdf',
                text: '<i class="ti ti-file-type-pdf me-1"></i>PDF',
                className: 'btn btn-danger buttons-pdf',
                filename: function() {
                    return `Indicadores_Membresias_${fechaInicio}_${fechaFinal}`;
                },
                title: `Indicadores de Membresías`,
                messageTop: `Período: ${fechaInicio} al ${fechaFinal}`,
                orientation: 'landscape',
            },
            {
                extend: 'copy',
                text: '<i class="ti ti-copy me-1"></i>Copiar',
                className: 'btn btn-warning buttons-copy',
            },
            {
                text: '<i class="ti ti-chart-pie me-1"></i>Gráficas',
                className: 'btn btn-primary',
                action: function() {
                    if (membershipsData.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin datos',
                            text: 'No hay datos disponibles para generar gráficas'
                        });
                        return;
                    }
                    generateCharts();
                    toggleChartsMemberships();
                }
            }
        ],
        responsive: true,
        fixedHeader: true,
        initComplete: function() {
            hideLoading();
            
            // Aplicar estilos personalizados a las celdas según el tipo de membresía
            $('#indicadores_membresias_table tbody tr').each(function() {
                const membershipCell = $(this).find('td:nth-child(4)');
            });
        }
    });
}

/**
 * Actualizar cards de resumen rápido
 */
function updateQuickSummary(data) {
    if (!data || data.length === 0) {
        document.getElementById('quickSummaryCards').style.display = 'none';
        return;
    }

    // Mostrar los cards
    document.getElementById('quickSummaryCards').style.display = 'flex';

    // Calcular estadísticas con validaciones
    const totalClientes = data.length;
    const totalOrdenes = data.reduce((sum, item) => {
        const ordenes = parseInt(item.total_ordenes || item.total || 0);
        return sum + (isNaN(ordenes) ? 0 : ordenes);
    }, 0);
    const promedioOrdenes = totalClientes > 0 ? Math.round(totalOrdenes / totalClientes) : 0;

    // Encontrar la membresía más común con validaciones
    const membershipCount = {};
    data.forEach(item => {
        const membership = (item.package_name || 'N/A').toString();
        membershipCount[membership] = (membershipCount[membership] || 0) + 1;
    });

    const membershipTop = Object.keys(membershipCount).length > 0 ? 
        Object.keys(membershipCount).reduce((a, b) => 
            membershipCount[a] > membershipCount[b] ? a : b
        ) : 'N/A';

    // Actualizar valores en los cards
    document.getElementById('totalClientes').textContent = totalClientes.toLocaleString();
    document.getElementById('totalOrdenes').textContent = totalOrdenes.toLocaleString();
    document.getElementById('membershipTop').textContent = membershipTop;
    document.getElementById('promedioOrdenes').textContent = promedioOrdenes.toLocaleString();
}

/**
 * Generar gráficas
 */
function generateCharts() {
    if (!membershipsData || membershipsData.length === 0) {
        return;
    }

    generateMembershipDistributionChart();
    generateOrdersPerMembershipChart();
    generateTopClientsChart();
    generateDetailedStats();
}

/**
 * Gráfica de distribución de membresías (Pie Chart)
 */
function generateMembershipDistributionChart() {
    const ctx = document.getElementById('membershipDistributionChart').getContext('2d');
    
    // Destruir gráfica existente
    if (membershipDistributionChart) {
        membershipDistributionChart.destroy();
    }

    // Contar membresías
    const membershipCount = {};
    membershipsData.forEach(item => {
        const membership = (item.package_name || 'N/A').toString();
        membershipCount[membership] = (membershipCount[membership] || 0) + 1;
    });

    const labels = Object.keys(membershipCount);
    const data = Object.values(membershipCount);
    const colors = labels.map(label => membershipColors[label] || '#6c757d');

    membershipDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Gráfica de órdenes por membresía (Bar Chart)
 */
function generateOrdersPerMembershipChart() {
    const ctx = document.getElementById('ordersPerMembershipChart').getContext('2d');
    
    // Destruir gráfica existente
    if (ordersPerMembershipChart) {
        ordersPerMembershipChart.destroy();
    }

    // Agrupar órdenes por tipo de membresía
    const membershipOrders = {};
    membershipsData.forEach(item => {
        const membership = (item.package_name || 'N/A').toString();
        const orders = parseInt(item.total_ordenes || item.total || 0);
        membershipOrders[membership] = (membershipOrders[membership] || 0) + orders;
    });

    const labels = Object.keys(membershipOrders);
    const data = Object.values(membershipOrders);
    const colors = labels.map(label => membershipColors[label] || '#6c757d');

    ordersPerMembershipChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Órdenes',
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Total Órdenes: ${context.raw.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

/**
 * Gráfica de top 10 clientes
 */
function generateTopClientsChart() {
  const ctx = document.getElementById('topClientsChart').getContext('2d');

  if (topClientsChart) topClientsChart.destroy();

  // Ordena DESC por total de órdenes (estabas leyendo a/b invertidos)
  const sortedData = [...membershipsData]
    .sort((a, b) => {
      const aOrders = parseInt(a.total_ordenes || a.total || 0);
      const bOrders = parseInt(b.total_ordenes || b.total || 0);
      return bOrders - aOrders; // descendente
    })
    .slice(0, 10);

  const labels = sortedData.map(item => {
    const nombre = item.cliente || 'Sin nombre';
    return nombre.length > 20 ? nombre.substring(0, 17) + '...' : nombre;
  });
  const data = sortedData.map(item => parseInt(item.total_ordenes || item.total || 0));
  const backgroundColors = sortedData.map(item => membershipColors[item.package_name] || '#6c757d');

  topClientsChart = new Chart(ctx, {
    // 🔴 antes: type: 'horizontalBar'
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Número de Órdenes',
        data,
        backgroundColor: backgroundColors,
        borderColor: backgroundColors,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      // Esto hace la barra horizontal en v3/v4
      indexAxis: 'y',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title(context) {
              const originalData = sortedData[context[0].dataIndex];
              return originalData.cliente || 'Sin nombre';
            },
            label(context) {
              const originalData = sortedData[context.dataIndex];
              return [
                `Órdenes: ${context.raw}`,
                `Membresía: ${originalData.package_name || 'N/A'}`,
                `Paquete: ${originalData.package || 'Sin paquete'}`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            callback(value) {
              return value.toLocaleString();
            }
          }
        }
      }
    }
  });
}


/**
 * Generar estadísticas detalladas
 */
function generateDetailedStats() {
    if (!membershipsData || membershipsData.length === 0) {
        return;
    }

    // Calcular estadísticas
    const stats = {
        totalClientes: membershipsData.length,
        totalOrdenes: membershipsData.reduce((sum, item) => {
            const ordenes = parseInt(item.total_ordenes || item.total || 0);
            return sum + (isNaN(ordenes) ? 0 : ordenes);
        }, 0),
        promedioOrdenes: 0,
        medianaOrdenes: 0,
        clienteConMasOrdenes: null,
        membershipStats: {}
    };

    // Promedio
    stats.promedioOrdenes = stats.totalClientes > 0 ? (stats.totalOrdenes / stats.totalClientes).toFixed(1) : 0;

    // Mediana
    const ordenesArray = membershipsData.map(item => {
        const ordenes = parseInt(item.total_ordenes || item.total || 0);
        return isNaN(ordenes) ? 0 : ordenes;
    }).sort((a, b) => a - b);
    
    const middle = Math.floor(ordenesArray.length / 2);
    stats.medianaOrdenes = ordenesArray.length % 2 === 0 ? 
        ((ordenesArray[middle - 1] + ordenesArray[middle]) / 2).toFixed(1) : 
        ordenesArray[middle];

    // Cliente con más órdenes
    const topClient = membershipsData.reduce((max, client) => {
        const maxOrders = parseInt(max.total_ordenes || max.total || 0);
        const clientOrders = parseInt(client.total_ordenes || client.total || 0);
        return clientOrders > maxOrders ? client : max;
    });
    stats.clienteConMasOrdenes = topClient;

    // Estadísticas por membresía
    membershipsData.forEach(item => {
        const membership = (item.package_name || 'N/A').toString();
        if (!stats.membershipStats[membership]) {
            stats.membershipStats[membership] = {
                count: 0,
                totalOrdenes: 0,
                promedio: 0
            };
        }
        stats.membershipStats[membership].count++;
        const ordenes = parseInt(item.total_ordenes || item.total || 0);
        stats.membershipStats[membership].totalOrdenes += isNaN(ordenes) ? 0 : ordenes;
    });

    // Calcular promedios por membresía
    Object.keys(stats.membershipStats).forEach(membership => {
        const membershipStat = stats.membershipStats[membership];
        membershipStat.promedio = (membershipStat.totalOrdenes / membershipStat.count).toFixed(1);
    });

    // Renderizar estadísticas
    renderDetailedStats(stats);
}

/**
 * Renderizar estadísticas detalladas en el DOM
 */
function renderDetailedStats(stats) {
    const container = document.getElementById('detailedStats');
    
    let html = `
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="text-primary">${stats.totalClientes.toLocaleString()}</h5>
                    <p class="mb-0">Total Clientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="text-success">${stats.totalOrdenes.toLocaleString()}</h5>
                    <p class="mb-0">Total Órdenes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5 class="text-info">${stats.promedioOrdenes}</h5>
                    <p class="mb-0">Promedio Órdenes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 class="text-warning">${stats.medianaOrdenes}</h5>
                    <p class="mb-0">Mediana Órdenes</p>
                </div>
            </div>
        </div>
    `;

    // Agregar información del cliente top
    if (stats.clienteConMasOrdenes) {
        const topClientOrders = parseInt(stats.clienteConMasOrdenes.total_ordenes || stats.clienteConMasOrdenes.total || 0);
        html += `
            <div class="col-12 mb-3">
                <div class="card border-dark">
                    <div class="card-header">
                        <h6 class="mb-0">Cliente con Más Órdenes</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nombre:</strong> ${stats.clienteConMasOrdenes.cliente || 'Sin nombre'}
                            </div>
                            <div class="col-md-3">
                                <strong>Órdenes:</strong> <span class="badge bg-primary">${topClientOrders}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Membresía:</strong> <span class="badge bg-secondary">${stats.clienteConMasOrdenes.package_name || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Agregar estadísticas por membresía
    html += `<div class="col-12"><h6>Estadísticas por Tipo de Membresía</h6></div>`;
    
    Object.keys(stats.membershipStats).forEach(membership => {
        const membershipStat = stats.membershipStats[membership];
        const color = membershipColors[membership] || '#6c757d';
        
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card" style="border-left: 4px solid ${color};">
                    <div class="card-body">
                        <h6 class="card-title">${membership}</h6>
                        <div class="row">
                            <div class="col-4 text-center">
                                <small class="text-muted">Clientes</small>
                                <div class="fw-bold">${membershipStat.count}</div>
                            </div>
                            <div class="col-4 text-center">
                                <small class="text-muted">Total Órdenes</small>
                                <div class="fw-bold">${membershipStat.totalOrdenes.toLocaleString()}</div>
                            </div>
                            <div class="col-4 text-center">
                                <small class="text-muted">Promedio</small>
                                <div class="fw-bold">${membershipStat.promedio}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

/**
 * Mostrar/ocultar contenedor de gráficas
 */
let chartsVisible = false;
function toggleChartsMemberships() {
    const container = document.getElementById('chartsMembershipsContainer');
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
        if (membershipsData.length > 0) {
            generateCharts();
        }
    } else {
        container.style.display = 'none';
    }
}

/**
 * Funciones de utilidad para loading
 */
function showLoading() {
    // Mostrar spinner o loading
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Cargando...',
            text: 'Obteniendo indicadores de membresías',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

function hideLoading() {
    // Ocultar loading
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

// Inicialización cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    // Configurar Chart.js defaults
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
        Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
        Chart.defaults.plugins.tooltip.cornerRadius = 6;
    }
});