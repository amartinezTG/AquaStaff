// assets/js/dashboard.js

// Variables globales para almacenar datos y gráficas
let dashboardData = {};
let hourlyChart, membershipChart, cajerosChart, paymentMethodsChart;

// Configuración de colores
const COLORS = {
    primary: '#007bff',
    success: '#28a745', 
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#17a2b8',
    purple: '#6f42c1',
    orange: '#fd7e14',
    teal: '#20c997',
    pink: '#e83e8c',
    indigo: '#6610f2'
};

// Configuración de Chart.js
// Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
// Chart.defaults.font.size = 11;
// Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
// Chart.defaults.plugins.tooltip.cornerRadius = 8;

/**
 * Función principal para cargar todos los datos del dashboard
 */
async function loadDashboardData() {
    const selectedDate = document.getElementById('dashboard_date').value;
    
    if (!selectedDate) {
        showError('Por favor selecciona una fecha');
        return;
    }

    try {
        // Mostrar loading en las tarjetas principales
        showLoadingCards();
        
        // Hacer petición al backend
        const response = await fetch('/dashboard/info_dashboard ', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content            },
            body: JSON.stringify({
                date: selectedDate
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            dashboardData = data.data;
            updateDashboard(dashboardData);
             updateLastUpdated();
        } else {
            throw new Error(data.message || 'Error al cargar datos');
        }

    } catch (error) {
        console.error('Error cargando dashboard:', error);
        showError('Error al cargar los datos del dashboard: ' + error.message);
        hideLoadingCards();
    }
}
async function loadActiveMemberships() {
    try {
        const response = await fetch('/dashboard/active_memberships', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content            },
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const data = await response.json();
            if (data) {
                    // Actualizar total
                document.getElementById('total_active_memberships').textContent = data.total || 0;

                // Actualizar contadores por paquete
                document.getElementById('count_express').textContent = data.express || 0;
                document.getElementById('count_basico').textContent = data.basico || 0;
                document.getElementById('count_ultra').textContent = data.ultra || 0;
                document.getElementById('count_delux').textContent = data.delux || 0;

            }
    }catch (error) {
        console.error('Error cargando membresías activas:', error);
        showError('Error al cargar las membresías activas: ' + error.message);
    }
}
/**
 * Actualizar todos los elementos del dashboard con los nuevos datos
 */
function updateDashboard(data) {
    // Actualizar tarjetas principales
    updateMainCards(data.summary || {});
    
    // Actualizar gráficas
    updateHourlyChart(data.hourly || []);
    updateMembershipChart(data.membership_distribution || []);
    updateCajerosChart(data.cajeros || []);
    updatePaymentMethodsChart(data.payment_methods || []);
    
    // Actualizar tabla de resumen
    // updateSummaryTable(data.detailed_summary || []);
    
    // Ocultar loading
    hideLoadingCards();
}

/**
 * Actualizar las tarjetas principales con las métricas
 */
function updateMainCards(summary) {
    // Ingresos del día
    updateCard('total_sales', formatCurrency(summary.total_ingresos || 0));
    updateCard('ingresos_change', formatChange(summary.ingresos_change || 0));
    
    // Órdenes del día
    updateCard('total_ordenes', formatNumber(summary.total_ordenes || 0));
    updateCard('ordenes_change', formatChange(summary.ordenes_change || 0));
    
    // Membresías
    updateCard('total_membresias', formatNumber(summary.total_membresias || 0));
    updateCard('membresias_change', formatChange(summary.membresias_change || 0));
    
    // Ticket promedio
    updateCard('ticket_promedio', formatCurrency(summary.ticket_promedio || 0));
    updateCard('ticket_change', formatChange(summary.ticket_change || 0));
}

/**
 * Actualizar una tarjeta específica
 */
function updateCard(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
       element.innerHTML = value;
        
        // Animación de actualización
        element.style.transform = 'scale(1.05)';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 200);
    }
}

/**
 * Actualizar gráfica de ventas por hora
 */
function updateHourlyChart(hourlyData = null) {
    const ctx = document.getElementById('hourlyChart');
    if (!ctx) return;
    
    // Si no se proporciona datos, usar los existentes
    if (!hourlyData && dashboardData.hourly) {
        hourlyData = dashboardData.hourly;
    }
    
    if (!hourlyData || hourlyData.length === 0) {
        hourlyData = generateEmptyHourlyData();
    }
    
    // Determinar qué tipo de gráfica mostrar
    const chartType = document.querySelector('input[name="chart_type"]:checked')?.id || 'chart_revenue';
    const isRevenue = chartType === 'chart_revenue';
    
    // Destruir gráfica existente
    if (hourlyChart) {
        hourlyChart.destroy();
    }
    
    // Preparar datos
    const labels = hourlyData.map(item => formatHour(item.hour));
    const data = hourlyData.map(item => isRevenue ? (item.ingresos || 0) : (item.ordenes || 0));
    const label = isRevenue ? 'Ingresos ($)' : 'Órdenes';
    const color = isRevenue ? COLORS.success : COLORS.primary;
    
    hourlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: color,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
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
                            const value = context.raw;
                            return isRevenue ? 
                                `Ingresos: ${formatCurrency(value)}` :
                                `Órdenes: ${formatNumber(value)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return isRevenue ? formatCurrency(value, true) : formatNumber(value);
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

/**
 * Actualizar gráfica de distribución membresías vs paquetes
 */
function updateMembershipChart(rawMembershipData = []) {
  const ctx = document.getElementById('membershipChart');
  if (!ctx) return;
  if (membershipChart) membershipChart.destroy();

  // Si viene el shape nuevo: [{ type, data:[{package_name,total},…], color }, …]
  // lo convertimos a [{ type, count, color }, …]
  let chartData = rawMembershipData;
  if (chartData.length && Array.isArray(chartData[0].data)) {
    chartData = chartData.map(item => {
      const totalCount = item.data.reduce((sum, row) => sum + (row.total || 0), 0);
      return { type: item.type, count: totalCount, color: item.color };
    });
  }

  // Si no hay datos, ponemos el fallback
  if (!chartData.length) {
    chartData = [
      { type: 'Membresías', count: 0, color: COLORS.purple },
      { type: 'Paquetes',   count: 0, color: COLORS.info   }
    ];
  }

  const labels = chartData.map(i => i.type);
  const data   = chartData.map(i => i.count);
  const colors = chartData.map(i => i.color);

  membershipChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
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
          labels: { padding: 20, usePointStyle: true, font: { size: 11 } }
        },
        tooltip: {
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
              const pct   = total>0 ? ((ctx.raw/total)*100).toFixed(1) : 0;
              return `${ctx.label}: ${ctx.raw} (${pct}%)`;
            }
          }
        }
      },
      cutout: '60%'
    }
  });
}


/**
 * Actualizar gráfica de top cajeros
 */
function updateCajerosChart(cajerosData = []) {
    const ctx = document.getElementById('cajerosChart');
    if (!ctx) return;

    if (cajerosChart) {
        cajerosChart.destroy();
    }

    const topCajeros = cajerosData.slice(0, 5);

    if (topCajeros.length === 0) {
        topCajeros.push({ cajero: 'Sin datos', total: 0 });
    }

    const labels = topCajeros.map(item => item.cajero || 'Sin nombre');
    const data = topCajeros.map(item => item.total || 0);
    const colors = generateColors(topCajeros.length);
    
    cajerosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas ($)',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.8', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Ventas: ${formatCurrency(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value, true);
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Actualizar gráfica de métodos de pago
 */
function updatePaymentMethodsChart(paymentData = []) {
  const ctx = document.getElementById('paymentMethodsChart');
  if (!ctx) return;
  if (paymentMethodsChart) paymentMethodsChart.destroy();

  if (!paymentData.length) {
    paymentData = [
      { method: 'Efectivo', total: 0 },
      { method: 'Tarjeta',  total: 0 },
      { method: 'Garantía', total: 0 }
    ];
  }

  const labels = paymentData.map(i => i.method);
  const data   = paymentData.map(i => i.total);
  const colors = [COLORS.success, COLORS.primary, COLORS.warning];

  paymentMethodsChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } },
        tooltip: {
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
              const pct   = total ? ((ctx.raw/total)*100).toFixed(1) : 0;
              return `${ctx.label}: ${ctx.raw} (${pct}%)`;
            }
          }
        }
      }
    }
  });
}


/**
 * Actualizar tabla de resumen detallado
 */
function updateSummaryTable(summaryData = []) {
    const tbody = document.getElementById('summary_table_body');
    if (!tbody) return;
    
    if (!summaryData || summaryData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted">
                    No hay datos disponibles para el período seleccionado
                </td>
            </tr>
        `;
        return;
    }
    
    const totalGeneral = summaryData.reduce((sum, item) => sum + (item.total || 0), 0);
    
    let html = '';
    summaryData.forEach(item => {
        const percentage = totalGeneral > 0 ? ((item.total / totalGeneral) * 100).toFixed(1) : 0;
        const promedio = item.cantidad > 0 ? (item.total / item.cantidad) : 0;
        
        html += `
            <tr>
                <td class="fw-semibold">${item.concepto || 'N/A'}</td>
                <td class="text-center">${formatNumber(item.cantidad || 0)}</td>
                <td class="text-end">${formatCurrency(promedio)}</td>
                <td class="text-end fw-bold">${formatCurrency(item.total || 0)}</td>
                <td class="text-center">
                    <span class="badge bg-primary">${percentage}%</span>
                </td>
            </tr>
        `;
    });
    
    // Agregar fila de total
    html += `
        <tr class="table-dark">
            <td class="fw-bold">TOTAL</td>
            <td class="text-center fw-bold">${formatNumber(summaryData.reduce((sum, item) => sum + (item.cantidad || 0), 0))}</td>
            <td class="text-end">--</td>
            <td class="text-end fw-bold">${formatCurrency(totalGeneral)}</td>
            <td class="text-center fw-bold">100%</td>
        </tr>
    `;
    
    tbody.innerHTML = html;
}

/**
 * Funciones de utilidad para formateo
 */
function formatCurrency(amount, short = false) {
    if (!amount && amount !== 0) return '$0';
    
    const formatter = new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
    
    // if (short && Math.abs(amount) >= 1000) {
    //     if (Math.abs(amount) >= 1000000) {
    //         return ' + (amount / 1000000).toFixed(1) + 'M';
    //     } else {
    //         return ' + (amount / 1000).toFixed(1) + 'K';
    //     }
    // }
    
    return formatter.format(amount);
}

function formatNumber(number) {
    if (!number && number !== 0) return '0';
    return new Intl.NumberFormat('es-MX').format(number);
}

function formatChange(change) {
    if (!change && change !== 0) return '0% vs ayer';
    
    const icon = change >= 0 ? 'bi-arrow-up text-success' : 'bi-arrow-down text-danger';
    const sign = change >= 0 ? '+' : '';
    
    return `<i class="bi ${icon}"></i> ${sign}${change.toFixed(1)}% vs ayer`;
}

function formatHour(hour) {
    const hourNum = parseInt(hour);
    if (isNaN(hourNum)) return '00:00';
    
    const nextHour = hourNum + 1;
    return `${hourNum.toString().padStart(2, '0')}:00`;
}

/**
 * Generar datos vacíos para gráfica por hora
 */
function generateEmptyHourlyData() {
    const data = [];
    for (let i = 0; i < 24; i++) {
        data.push({
            hour: i,
            ingresos: 0,
            ordenes: 0
        });
    }
    return data;
}

/**
 * Generar colores para gráficas
 */
function generateColors(count) {
    const colorValues = Object.values(COLORS);
    const colors = [];
    
    for (let i = 0; i < count; i++) {
        const color = colorValues[i % colorValues.length];
        colors.push(color + '80'); // Agregar transparencia
    }
    
    return colors;
}

/**
 * Funciones de estado de carga
 */
function showLoadingCards() {
    const cards = [
        'total_sales', 'total_ordenes', 'total_membresias', 'ticket_promedio'
    ];
    
    cards.forEach(cardId => {
        const element = document.getElementById(cardId);
        if (element) {
            element.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
        }
    });
}

function hideLoadingCards() {
    // Las tarjetas se actualizan con updateMainCards()
}

/**
 * Actualizar timestamp de última actualización
 */
function updateLastUpdated() {
    const element = document.getElementById('last_updated');
    if (element) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-MX', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        element.textContent = `Última actualización: ${timeString}`;
    }
}

/**
 * Mostrar errores
 */
function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    } else {
        console.error('Dashboard Error:', message);
        alert('Error: ' + message);
    }
}

/**
 * Mostrar notificaciones de éxito
 */
function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        console.log('Dashboard Success:', message);
    }
}

/**
 * Función para exportar datos del dashboard
 */
function exportDashboardData() {
    if (!dashboardData || Object.keys(dashboardData).length === 0) {
        showError('No hay datos para exportar');
        return;
    }
    
    const selectedDate = document.getElementById('dashboard_date').value;
    const filename = `Dashboard_AQUACAR_${selectedDate}.json`;
    
    const dataStr = JSON.stringify(dashboardData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = filename;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showSuccess('Datos exportados exitosamente');
}

/**
 * Función para imprimir dashboard
 */
function printDashboard() {
    window.print();
}

/**
 * Función para pantalla completa
 */
// function toggleFullscreen() {
//     if (!document.fullscreenElement) {
//         document.documentElement.requestFullscreen();
//     } else {
//         if (document.exitFullscreen) {
//             document.exitFullscreen();
//         }
//     }
// }

// // Event listeners adicionales
// document.addEventListener('keydown', function(e) {
//     // F5 para actualizar
//     if (e.key === 'F5') {
//         e.preventDefault();
//         loadDashboardData();
//     }
    
//     // F11 para pantalla completa
//     if (e.key === 'F11') {
//         e.preventDefault();
//         toggleFullscreen();
//     }
// });

// Manejo de errores globales
window.addEventListener('error', function(e) {
    console.error('Error global en dashboard:', e.error);
});

// Manejo de promesas rechazadas
window.addEventListener('unhandledrejection', function(e) {
    console.error('Promesa rechazada en dashboard:', e.reason);
});

