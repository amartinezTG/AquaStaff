// assets/js/dashboard.js

// Variables globales para almacenar datos y gráficas
let dashboardData = {};
let hourlyChart, hourlyLavadosChart, membershipChart, cajerosChart, paymentMethodsChart;
   
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
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
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
    updateHourlyLavadosChart(data.hourly || []);
    updateMembershipChart(data.membership_distribution || []);
    updateCajerosChart(data.cajeros || []);
    updatePaymentMethodsChart(data.payment_methods || []);
    
    // Actualizar cards por cajero
    updateCajeroCards(data.cajeros || []);

    // Actualizar tabla de servicios del día
    updateServiciosTable(data.servicios || []);

    // Actualizar card de lavados por tipo de paquete
    updateLavadosPorTipo(data.servicios || []);

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
    } else {
        hourlyData = hourlyData.filter(item => item.hour >= 6 && item.hour <= 21);
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
 * Gráfica de lavados por hora (cantidad de órdenes)
 */
function updateHourlyLavadosChart(hourlyData = null) {
    const ctx = document.getElementById('hourlyLavadosChart');
    if (!ctx) return;

    if (!hourlyData || hourlyData.length === 0) {
        hourlyData = generateEmptyHourlyData();
    } else {
        hourlyData = hourlyData.filter(item => item.hour >= 6 && item.hour <= 21);
    }

    if (hourlyLavadosChart) hourlyLavadosChart.destroy();

    const labels = hourlyData.map(item => formatHour(item.hour));
    const data   = hourlyData.map(item => item.ordenes || 0);

    hourlyLavadosChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Lavados',
                data,
                borderColor: '#0f766e',
                backgroundColor: 'rgba(15, 118, 110, 0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#0f766e',
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `Lavados: ${ctx.raw}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, callback: v => Number.isInteger(v) ? v : '' },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                },
                x: { grid: { display: false } }
            },
            interaction: { intersect: false, mode: 'index' }
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
 * Poblar las cards de desglose por cajero
 */
function updateCajeroCards(cajerosData) {
    const cajeros = {};
    cajerosData.forEach(c => { cajeros[c.cajero] = c; });

    const totals = cajerosData.reduce((acc, c) => {
        acc.efectivo           += parseFloat(c.efectivo)            || 0;
        acc.tarjeta            += parseFloat(c.tarjeta)             || 0;
        acc.lavados_paquete    += parseInt(c.lavados_paquete)       || 0;
        acc.lavados_membresia  += parseInt(c.lavados_membresia)     || 0;
        acc.compras_membresia  += parseInt(c.compras_membresia)     || 0;
        acc.renovaciones       += parseInt(c.renovaciones)          || 0;
        return acc;
    }, { efectivo: 0, tarjeta: 0, lavados_paquete: 0, lavados_membresia: 0, compras_membresia: 0, renovaciones: 0 });

    // AQUA01
    const a1 = cajeros['AQUA01'] || {};
    setVal('aqua01_efectivo',           formatCurrency(a1.efectivo          || 0));
    setVal('aqua01_tarjeta',            formatCurrency(a1.tarjeta           || 0));
    setVal('aqua01_lavados_paquete',    formatNumber(a1.lavados_paquete     || 0));
    setVal('aqua01_lavados_membresia',  formatNumber(a1.lavados_membresia   || 0));
    setVal('aqua01_compras_membresia',  formatNumber(a1.compras_membresia   || 0));
    setVal('aqua01_renovaciones',       formatNumber(a1.renovaciones        || 0));

    // AQUA02
    const a2 = cajeros['AQUA02'] || {};
    setVal('aqua02_efectivo',           formatCurrency(a2.efectivo          || 0));
    setVal('aqua02_tarjeta',            formatCurrency(a2.tarjeta           || 0));
    setVal('aqua02_lavados_paquete',    formatNumber(a2.lavados_paquete     || 0));
    setVal('aqua02_lavados_membresia',  formatNumber(a2.lavados_membresia   || 0));
    setVal('aqua02_compras_membresia',  formatNumber(a2.compras_membresia   || 0));
    setVal('aqua02_renovaciones',       formatNumber(a2.renovaciones        || 0));

    // Totales
    setVal('total_efectivo',           formatCurrency(totals.efectivo));
    setVal('total_tarjeta',            formatCurrency(totals.tarjeta));
    setVal('total_lavados_paquete',    formatNumber(totals.lavados_paquete));
    setVal('total_lavados_membresia',  formatNumber(totals.lavados_membresia));
    setVal('total_compras_membresia',  formatNumber(totals.compras_membresia));
    setVal('total_renovaciones',       formatNumber(totals.renovaciones));
}

function setVal(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

/**
 * Actualizar tabla de servicios del día
 */
function updateServiciosTable(servicios = []) {
    const tbody = document.getElementById('servicios_tbody');
    const tfoot = document.getElementById('servicios_tfoot');
    if (!tbody) return;

    if (!servicios.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-2">Sin datos</td></tr>';
        if (tfoot) tfoot.style.display = 'none';
        return;
    }

    let totPagos = 0, totEfectivo = 0, totTarjeta = 0, totTotal = 0;
    let html = '';

    servicios.forEach(s => {
        const pagos     = parseInt(s.pagos)     || 0;
        const efectivo  = parseFloat(s.efectivo) || 0;
        const tarjeta   = parseFloat(s.tarjeta)  || 0;
        const total     = parseFloat(s.total)    || 0;

        totPagos    += pagos;
        totEfectivo += efectivo;
        totTarjeta  += tarjeta;
        totTotal    += total;

        html += `<tr>
            <td>${s.servicio}</td>
            <td class="text-center">${formatNumber(pagos)}</td>
            <td class="text-end">${formatCurrency(efectivo)}</td>
            <td class="text-end">${formatCurrency(tarjeta)}</td>
            <td class="text-end fw-semibold">${formatCurrency(total)}</td>
        </tr>`;
    });

    tbody.innerHTML = html;

    if (tfoot) {
        tfoot.style.display = '';
        setVal('sf_pagos',    formatNumber(totPagos));
        setVal('sf_efectivo', formatCurrency(totEfectivo));
        setVal('sf_tarjeta',  formatCurrency(totTarjeta));
        setVal('sf_total',    formatCurrency(totTotal));
    }
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
 * Actualizar card de lavados por tipo de paquete
 * Cuenta TransactionType=2 (lavado paquete + uso membresía), agrupados por paquete
 */
function updateLavadosPorTipo(servicios) {
    const cl  = { express: 0, basico: 0, ultra: 0, delux: 0 }; // Compra Lavado
    const um  = { express: 0, basico: 0, ultra: 0, delux: 0 }; // Uso Membresía
    const ren = { express: 0, basico: 0, ultra: 0, delux: 0 }; // Renovación
    const cm  = { express: 0, basico: 0, ultra: 0, delux: 0 }; // Compra Membresía
    let   gar = 0;                                               // Garantía / Cortesía

    servicios.forEach(s => {
        const n = parseInt(s.pagos) || 0;
        switch (s.servicio) {
            case 'Lavado Express':               cl.express  += n; break;
            case 'Lavado Básico':                cl.basico   += n; break;
            case 'Lavado Ultra':                 cl.ultra    += n; break;
            case 'Lavado Deluxe':                cl.delux    += n; break;
            case 'Uso Membresía Express':  um.express += n; break;
            case 'Uso Membresía Básico':   um.basico  += n; break;
            case 'Uso Membresía Ultra':    um.ultra   += n; break;
            case 'Uso Membresía Deluxe':   um.delux   += n; break;
            case 'Uso Membresía':          um.express += n; break;
            case 'Renovación Membresía Express': ren.express += n; break;
            case 'Renovación Membresía Básico':  ren.basico  += n; break;
            case 'Renovación Membresía Ultra':   ren.ultra   += n; break;
            case 'Renovación Membresía Deluxe':  ren.delux   += n; break;
            case 'Compra Membresía Express':     cm.express  += n; break;
            case 'Compra Membresía Básico':      cm.basico   += n; break;
            case 'Compra Membresía Ultra':       cm.ultra    += n; break;
            case 'Compra Membresía Deluxe':      cm.delux    += n; break;
            case 'Cortesía':                     gar         += n; break;
        }
    });

    // Compra Lavado
    setVal('cl_express', formatNumber(cl.express));
    setVal('cl_basico',  formatNumber(cl.basico));
    setVal('cl_ultra',   formatNumber(cl.ultra));
    setVal('cl_delux',   formatNumber(cl.delux));
    setVal('cl_total',   formatNumber(cl.express + cl.basico + cl.ultra + cl.delux));

    // Uso Membresía
    setVal('um_express', formatNumber(um.express));
    setVal('um_basico',  formatNumber(um.basico));
    setVal('um_ultra',   formatNumber(um.ultra));
    setVal('um_delux',   formatNumber(um.delux));
    setVal('um_total',   formatNumber(um.express + um.basico + um.ultra + um.delux));

    // Renovación
    setVal('ren_express', formatNumber(ren.express));
    setVal('ren_basico',  formatNumber(ren.basico));
    setVal('ren_ultra',   formatNumber(ren.ultra));
    setVal('ren_delux',   formatNumber(ren.delux));
    setVal('ren_total',   formatNumber(ren.express + ren.basico + ren.ultra + ren.delux));

    // Compra Membresía
    setVal('cm_express', formatNumber(cm.express));
    setVal('cm_basico',  formatNumber(cm.basico));
    setVal('cm_ultra',   formatNumber(cm.ultra));
    setVal('cm_delux',   formatNumber(cm.delux));
    setVal('cm_total',   formatNumber(cm.express + cm.basico + cm.ultra + cm.delux));

    // Garantía
    setVal('gar_cortesia', formatNumber(gar));
    setVal('gar_total',    formatNumber(gar));
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
    return `${hourNum.toString().padStart(2, '0')}:00`;
}

/**
 * Generar datos vacíos para gráfica por hora (solo horas operativas 6am-9pm)
 */
function generateEmptyHourlyData() {
    const data = [];
    for (let i = 6; i <= 21; i++) {
        data.push({ hour: i, ingresos: 0, ordenes: 0 });
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
    const element = document.getElementById('last-update');
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

