console.log("Indicadores JS cargado");

let chartInstances = {};

function indicadoresTable(){
        if ($.fn.DataTable.isDataTable('#indicadores_table')) {
            $('#indicadores_table').DataTable().clear().destroy();
        }
        var fecha_inicio = $('#fecha_inicio').val();
        var fecha_final = $('#fecha_final').val();
        var indicadores_table = $('#indicadores_table').DataTable({
            colReorder: true,
            layout: {
                topStart: ['buttons'],
                bottomStart: ['pageLength', 'info'], // izquierda
                bottomEnd: 'paging'                  // derecha
            },
            pageLength: 100,
            order: [
                [0, 'desc']
            ],
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: '',
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    exportOptions: { columns: ':visible' }
                    // className: 'btn btn-sm btn-success',
                    // text: '<i class="ti ti-file-type-xls"></i> Excel',
                    // footer: true,
                    // header:true,
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-info',
                    text: '<i class="ti ti-file-type-pdf"></i> PDF'
                },
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-warning',
                    text: '<i class="ti ti-copy"></i> Copiar'
                },
                {
                    text: '<i class="ti ti-refresh"></i>',
                    className: 'btn-sm btn-success',
                    action: function (e, dt, node, config) {
                        indicadores_table.clear().draw();
                        indicadores_table.ajax.reload();
                        // $('div#loader').addClass('d-none');
                         $('.table-responsive').addClass('loader_iiee');
                    }
                },
                 {
                text: '<i class="ti ti-chart-line"></i> Gráficas',
                className: 'btn-sm btn-primary',
                action: function (e, dt, node, config) {
                    toggleCharts();
                }
            }
            ],
            ajax: {
                method: 'POST',
                url: '/indicadores/indicadores_table',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                data: {
                    fecha_inicio: fecha_inicio,
                    fecha_final: fecha_final
                },
                error: function() {
                    // $('#indicadores_table').waitMe('hide');
                    $('.table-responsive').removeClass('loader_iiee');
                
                    // Swal.fire({
                    //     icon: "error",
                    //     title: "Error",
                    //     text: "No se encontraron resultados!",
                    // });
                },
                beforeSend: function() {
                  $('.table-responsive').addClass('loader_iiee');
                },
                 complete: function () {
                    $('#basic-datatable_wrapper .table-responsive').removeClass('loader_iiee');
                },
            },
            rowId: 'fecha',
                columns: [
                // === Deben coincidir EXACTAMENTE con los alias del SELECT ===
                { data: 'fecha'}, // ya viene como 'YYYY-MM-DD'
                { data: 'total_eventos'},

                { data: 'lavados_paquete'},
                { data: 'lavados_express'},
                { data: 'lavados_basico'},
                { data: 'lavados_ultra'},
                { data: 'lavados_deluxe'},
                 { data: 'promo150'},
                { data: 'promo50'},
                { data: 'suma_total_tipo2', render: $.fn.dataTable.render.number(',', '.', 2)},

                { data: 'lavados_membresia'},
                { data: 'lavados_express_membresia'},
                { data: 'lavados_basico_membresia'},
                { data: 'lavados_ultra_membresia'},
                { data: 'lavados_deluxe_membresia'},
               

                { data: 'compra_membresia'},
                { data: 'renovacion_membresia'},
                { data: 'sum_compra_membresia' ,render: $.fn.dataTable.render.number(',', '.', 2)},
                { data: 'sum__renovacion_membresia', render: $.fn.dataTable.render.number(',', '.', 2)}, // ojo: doble underscore según tu alias
                { data: 'lavados_cortesia'},

                { data: 'suma_total_dia' ,render: $.fn.dataTable.render.number(',', '.', 2)},
                { data: 'suma_total_dia_iva' ,render: $.fn.dataTable.render.number(',', '.', 2)},
                ],
            createdRow: function (row, data, dataIndex) {
            },
            initComplete: function () {
                $('.table-responsive').removeClass('loader_iiee');
                createCharts(this.api());
                console.log('DataTable inicializada correctamente');
            }
        });
}


// Función para crear todas las gráficas
function createCharts(dataTable) {
    const data = dataTable.rows().data().toArray();
    
    if (data.length === 0) {
        console.log('No hay datos para crear gráficas');
        return;
    }

    // Preparar datos para las gráficas
    const chartData = prepareChartData(data);
    
    // Crear diferentes tipos de gráficas
    createTotalEventosChart(chartData);
    createPaquetesChart(chartData);
    createMembresiasChart(chartData);
    createIngresosChart(chartData);
}

// Función para preparar los datos
function prepareChartData(data) {
    const sortedData = data.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
    
    return {
        fechas: sortedData.map(row => formatDate(row.fecha)),
        totalEventos: sortedData.map(row => parseInt(row.total_eventos) || 0),
        paquetes: {
            lavados: sortedData.map(row => parseInt(row.lavados_paquete) || 0),
            express: sortedData.map(row => parseInt(row.lavados_express) || 0),
            basico: sortedData.map(row => parseInt(row.lavados_basico) || 0),
            ultra: sortedData.map(row => parseInt(row.lavados_ultra) || 0),
            deluxe: sortedData.map(row => parseInt(row.lavados_deluxe) || 0),
            total: sortedData.map(row => parseFloat(row.suma_total_tipo2) || 0)
        },
        membresias: {
            lavados: sortedData.map(row => parseInt(row.lavados_membresia) || 0),
            express: sortedData.map(row => parseInt(row.lavados_express_membresia) || 0),
            basico: sortedData.map(row => parseInt(row.lavados_basico_membresia) || 0),
            ultra: sortedData.map(row => parseInt(row.lavados_ultra_membresia) || 0),
            deluxe: sortedData.map(row => parseInt(row.lavados_deluxe_membresia) || 0),
            compras: sortedData.map(row => parseInt(row.compra_membresia) || 0),
            renovaciones: sortedData.map(row => parseInt(row.renovacion_membresia) || 0)
        },
        ingresos: {
            paquetes: sortedData.map(row => parseFloat(row.suma_total_tipo2) || 0),
            compraMembresia: sortedData.map(row => parseFloat(row.sum_compra_membresia) || 0),
            renovacionMembresia: sortedData.map(row => parseFloat(row.sum__renovacion_membresia) || 0),
            totalDia: sortedData.map(row => parseFloat(row.suma_total_dia) || 0)
        },
        cortesia: sortedData.map(row => parseInt(row.lavados_cortesia) || 0)
    };
}

// Gráfica de Total de Eventos
function createTotalEventosChart(chartData) {
    const ctx = document.getElementById('totalEventosChart');
    if (!ctx) return;

    destroyChart('totalEventos');

    chartInstances.totalEventos = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.fechas,
            datasets: [{
                label: 'Total Eventos',
                data: chartData.totalEventos,
                borderColor: '#2399b7',
                backgroundColor: 'rgba(35, 153, 183, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: getChartOptions('Total de Eventos por Día')
    });
}

// Gráfica de Distribución de Servicios (Paquetes + Membresías + Cortesías)
function createPaquetesChart(chartData) {
    const ctx = document.getElementById('paquetesChart');
    if (!ctx) return;

    destroyChart('paquetes');

    chartInstances.paquetes = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.fechas,
            datasets: [
                // Paquetes (pagos)
                {
                    label: 'Express (Pago)',
                    data: chartData.paquetes.express,
                    backgroundColor: 'rgba(255, 184, 0, 0.9)',
                    borderColor: '#ffb800',
                    borderWidth: 1,
                    stack: 'paquetes'
                },
                {
                    label: 'Básico (Pago)',
                    data: chartData.paquetes.basico,
                    backgroundColor: 'rgba(35, 153, 183, 0.9)',
                    borderColor: '#2399b7',
                    borderWidth: 1,
                    stack: 'paquetes'
                },
                {
                    label: 'Ultra (Pago)',
                    data: chartData.paquetes.ultra,
                    backgroundColor: 'rgba(156, 39, 176, 0.9)',
                    borderColor: '#9c27b0',
                    borderWidth: 1,
                    stack: 'paquetes'
                },
                {
                    label: 'Deluxe (Pago)',
                    data: chartData.paquetes.deluxe,
                    backgroundColor: 'rgba(255, 59, 59, 0.9)',
                    borderColor: '#ff3b3b',
                    borderWidth: 1,
                    stack: 'paquetes'
                },
                // Membresías (uso)
                {
                    label: 'Express (Membresía)',
                    data: chartData.membresias.express,
                    backgroundColor: 'rgba(255, 184, 0, 0.6)',
                    borderColor: '#ffb800',
                    borderWidth: 1,
                    stack: 'membresias'
                },
                {
                    label: 'Básico (Membresía)',
                    data: chartData.membresias.basico,
                    backgroundColor: 'rgba(35, 153, 183, 0.6)',
                    borderColor: '#2399b7',
                    borderWidth: 1,
                    stack: 'membresias'
                },
                {
                    label: 'Ultra (Membresía)',
                    data: chartData.membresias.ultra,
                    backgroundColor: 'rgba(156, 39, 176, 0.6)',
                    borderColor: '#9c27b0',
                    borderWidth: 1,
                    stack: 'membresias'
                },
                {
                    label: 'Deluxe (Membresía)',
                    data: chartData.membresias.deluxe,
                    backgroundColor: 'rgba(255, 59, 59, 0.6)',
                    borderColor: '#ff3b3b',
                    borderWidth: 1,
                    stack: 'membresias'
                },
                // Cortesías
                {
                    label: 'Cortesías',
                    data: chartData.cortesia,
                    backgroundColor: 'rgba(108, 117, 125, 0.8)',
                    borderColor: '#6c757d',
                    borderWidth: 1,
                    stack: 'cortesias'
                }
            ]
        },
        options: getChartOptions('Distribución de Servicios (Paquetes, Membresías y Cortesías)', true)
    });
}

// Gráfica de Membresías
function createMembresiasChart(chartData) {
    const ctx = document.getElementById('membresiasChart');
    if (!ctx) return;

    destroyChart('membresias');

    chartInstances.membresias = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Express', 'Básico', 'Ultra', 'Deluxe'],
            datasets: [{
                data: [
                    chartData.membresias.express.reduce((a, b) => a + b, 0),
                    chartData.membresias.basico.reduce((a, b) => a + b, 0),
                    chartData.membresias.ultra.reduce((a, b) => a + b, 0),
                    chartData.membresias.deluxe.reduce((a, b) => a + b, 0)
                ],
                backgroundColor: [
                    'rgba(255, 184, 0, 0.8)',
                    'rgba(35, 153, 183, 0.8)',
                    'rgba(156, 39, 176, 0.8)',
                    'rgba(255, 59, 59, 0.8)'
                ],
                borderColor: [
                    '#ffb800',
                    '#2399b7',
                    '#9c27b0',
                    '#ff3b3b'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Uso de Membresías por Tipo',
                    font: { size: 16, weight: 'bold' },
                    color: '#333'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfica de Ingresos
function createIngresosChart(chartData) {
    const ctx = document.getElementById('ingresosChart');
    if (!ctx) return;

    destroyChart('ingresos');

    chartInstances.ingresos = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.fechas,
            datasets: [
                {
                    label: 'Ingresos Paquetes',
                    data: chartData.ingresos.paquetes,
                    borderColor: '#46b723',
                    backgroundColor: 'rgba(70, 183, 35, 0.1)',
                    borderWidth: 2,
                    fill: true
                },
                {
                    label: 'Compra Membresía',
                    data: chartData.ingresos.compraMembresia,
                    borderColor: '#2399b7',
                    backgroundColor: 'rgba(35, 153, 183, 0.1)',
                    borderWidth: 2,
                    fill: true
                },
                {
                    label: 'Renovación Membresía',
                    data: chartData.ingresos.renovacionMembresia,
                    borderColor: '#ff3b3b',
                    backgroundColor: 'rgba(255, 59, 59, 0.1)',
                    borderWidth: 2,
                    fill: true
                }
            ]
        },
        options: getChartOptions('Ingresos por Categoría ($)', false, true)
    });
}

// Función para obtener opciones comunes de gráficas
function getChartOptions(title, stacked = false, isCurrency = false) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: title,
                font: { size: 16, weight: 'bold' },
                color: '#333'
            },
            legend: {
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: isCurrency ? {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString('es-MX', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                } : {}
            }
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Fecha'
                }
            },
            y: {
                display: true,
                stacked: stacked,
                title: {
                    display: true,
                    text: isCurrency ? 'Monto ($)' : 'Cantidad'
                },
                ticks: isCurrency ? {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-MX');
                    }
                } : {}
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    };
}

// Función para destruir gráficas existentes
function destroyChart(chartName) {
    if (chartInstances[chartName]) {
        chartInstances[chartName].destroy();
        delete chartInstances[chartName];
    }
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit'
    });
}

// Función para mostrar/ocultar gráficas
function toggleCharts() {
    const chartsContainer = document.getElementById('chartsContainer');
    if (chartsContainer) {
        if (chartsContainer.style.display === 'none') {
            chartsContainer.style.display = 'block';
            // Re-render gráficas después de mostrar
            setTimeout(() => {
                Object.values(chartInstances).forEach(chart => {
                    if (chart) chart.resize();
                });
            }, 100);
        } else {
            chartsContainer.style.display = 'none';
        }
    }
}

// Función para actualizar gráficas cuando cambien los datos
function updateCharts() {
    const dataTable = $('#indicadores_table').DataTable();
    createCharts(dataTable);
}   


///////////////////////////////////


function indicadoresPagosTable(){
    if ($.fn.DataTable.isDataTable('#indicadores_pagos_table')) {
        $('#indicadores_pagos_table').DataTable().clear().destroy();
    }
    
    var fecha_inicio = $('#fecha_inicio').val();
    var fecha_final = $('#fecha_final').val();
    
    var indicadores_pagos_table = $('#indicadores_pagos_table').DataTable({
        colReorder: true,
        layout: {
            topStart: ['buttons'],
            bottomStart: ['pageLength', 'info'],
            bottomEnd: 'paging'
        },
        pageLength: 100,
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'excelHtml5',
                className: '',
                text: '<i class="ti ti-file-type-xls"></i> Excel',
                exportOptions: { columns: ':visible' },
                title: 'Indicadores de Pagos y Cajeros'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-info',
                text: '<i class="ti ti-file-type-pdf"></i> PDF',
                title: 'Indicadores de Pagos y Cajeros'
            },
            {
                extend: 'copy',
                className: 'btn btn-sm btn-warning',
                text: '<i class="ti ti-copy"></i> Copiar'
            },
            {
                text: '<i class="ti ti-refresh"></i>',
                className: 'btn-sm btn-success',
                action: function (e, dt, node, config) {
                    indicadores_pagos_table.clear().draw();
                    indicadores_pagos_table.ajax.reload();
                    $('.table-responsive').addClass('loader_iiee');
                }
            },
            {
                text: '<i class="ti ti-chart-line"></i> Gráficas',
                className: 'btn-sm btn-primary',
                action: function (e, dt, node, config) {
                    toggleChartsPayments();
                }
            }
        ],
        ajax: {
            method: 'POST',
            url: '/indicadores/indicadores_pagos_table',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            data: {
                fecha_inicio: fecha_inicio,
                fecha_final: fecha_final
            },
            error: function() {
                $('.table-responsive').removeClass('loader_iiee');
            },
            beforeSend: function() {
                $('.table-responsive').addClass('loader_iiee');
            },
            complete: function () {
                $('#basic-datatable_wrapper .table-responsive').removeClass('loader_iiee');
            },
        },
        rowId: 'fecha',
        columns: [
            { data: 'fecha', title: 'Fecha'},
            { data: 'total_eventos', title: 'Total Eventos'},
            
            // Efectivo por cajero
            { data: 'suma_total_efectivo', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Total Efectivo'},
            { data: 'suma_total_cajero1', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Efectivo AQUA01'},
            { data: 'suma_total_cajero2', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Efectivo AQUA02'},
            
            // Tarjetas paquetes por cajero
            { data: 'suma_targetas_paquetes', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Total Tarjetas Paquetes'},
            { data: 'suma_targetas_cajero_1', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Tarjetas AQUA01'},
            { data: 'suma_targetas_cajero_2', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Tarjetas AQUA02'},
            
            // Membresías por cajero
            { data: 'suma_compra_membrecias', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Total Membresías'},
            { data: 'suma_comra_membresia_cajero_1', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Compra Memb. AQUA01'},
            { data: 'suma_compra_membresia_cajero_2', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Compra Memb. AQUA02'},
            { data: 'suma_renovacion_membresia_cajero_1', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Renov. Memb. AQUA01'},
            { data: 'suma_renovacion_membresia_cajero_2', render: $.fn.dataTable.render.number(',', '.', 2), title: 'Renov. Memb. AQUA02'},
            
            // Total del día
            { data: 'suma_procepago', render: $.fn.dataTable.render.number(',', '.', 2), title: '$ Total Procepago'},
            { data: 'suma_total_dia', render: $.fn.dataTable.render.number(',', '.', 2), title: '$ Total Día'},
        ],
        createdRow: function (row, data, dataIndex) {
            // Destacar primera columna (fecha)
            $(row).find('td:first-child').addClass('font-weight-bold bg-light');
            
            // Destacar columnas de totales
            // $(row).find('td:nth-child(2)').addClass('text-primary font-weight-bold'); // Total eventos
            // $(row).find('td:nth-child(3)').addClass('text-success font-weight-bold'); // Total efectivo
            // $(row).find('td:nth-child(6)').addClass('text-info font-weight-bold'); // Total tarjetas
            // $(row).find('td:nth-child(9)').addClass('text-warning font-weight-bold'); // Total membresías
            // $(row).find('td:nth-child(14)').addClass('text-success font-weight-bold'); // Total Procepago
            // $(row).find('td:last-child').addClass('text-danger font-weight-bold bg-light'); // Total día
        },
        initComplete: function () {
            $('.table-responsive').removeClass('loader_iiee');
            console.log('DataTable de pagos inicializada correctamente');
            
            // Crear gráficas automáticamente
            createPaymentCharts(this.api());
            generateSummaryStats(this.api());
        }
    });
}

// Función para crear gráficas de pagos
function createPaymentCharts(dataTable) {
    const data = dataTable.rows().data().toArray();
    
    if (data.length === 0) {
        console.log('No hay datos para crear gráficas de pagos');
        return;
    }

    const chartData = preparePaymentChartData(data);
    
    createPaymentMethodChart(chartData);
    createCajeroComparisonChart(chartData);
    createDailyRevenueChart(chartData);
}

// Preparar datos para gráficas
function preparePaymentChartData(data) {
    const sortedData = data.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
    
    return {
        fechas: sortedData.map(row => formatDate(row.fecha)),
        totalEventos: sortedData.map(row => parseInt(row.total_eventos) || 0),
        
        efectivo: {
            total: sortedData.map(row => parseFloat(row.suma_total_efectivo) || 0),
            cajero1: sortedData.map(row => parseFloat(row.suma_total_cajero1) || 0),
            cajero2: sortedData.map(row => parseFloat(row.suma_total_cajero2) || 0)
        },
        
        tarjetas: {
            total: sortedData.map(row => parseFloat(row.suma_targetas_paquetes) || 0),
            cajero1: sortedData.map(row => parseFloat(row.suma_targetas_cajero_1) || 0),
            cajero2: sortedData.map(row => parseFloat(row.suma_targetas_cajero_2) || 0)
        },
        
        membresias: {
            total: sortedData.map(row => parseFloat(row.suma_compra_membrecias) || 0),
            compraCajero1: sortedData.map(row => parseFloat(row.suma_comra_membresia_cajero_1) || 0),
            compraCajero2: sortedData.map(row => parseFloat(row.suma_compra_membresia_cajero_2) || 0),
            renovCajero1: sortedData.map(row => parseFloat(row.suma_renovacion_membresia_cajero_1) || 0),
            renovCajero2: sortedData.map(row => parseFloat(row.suma_renovacion_membresia_cajero_2) || 0)
        },
        
        totalDia: sortedData.map(row => parseFloat(row.suma_total_dia) || 0)
    };
}

// Gráfica de métodos de pago
function createPaymentMethodChart(chartData) {
    const ctx = document.getElementById('paymentMethodChart');
    if (!ctx) return;

    destroyChart('paymentMethod');

    const totalEfectivo = chartData.efectivo.total.reduce((a, b) => a + b, 0);
    const totalTarjetas = chartData.tarjetas.total.reduce((a, b) => a + b, 0);
    const totalMembresias = chartData.membresias.total.reduce((a, b) => a + b, 0);

    chartInstances.paymentMethod = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Efectivo', 'Tarjetas (Paquetes)', 'Membresías'],
            datasets: [{
                data: [totalEfectivo, totalTarjetas, totalMembresias],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(0, 123, 255, 0.8)', 
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    '#28a745',
                    '#007bff',
                    '#ffc107'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Distribución por Método de Pago',
                    font: { size: 16, weight: 'bold' }
                },
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': $' + context.parsed.toLocaleString('es-MX', {
                                minimumFractionDigits: 2
                            }) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Gráfica de comparación entre cajeros
function createCajeroComparisonChart(chartData) {
    const ctx = document.getElementById('cajeroComparisonChart');
    if (!ctx) return;

    destroyChart('cajeroComparison');

    chartInstances.cajeroComparison = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.fechas,
            datasets: [
                {
                    label: 'AQUA01 - Efectivo',
                    data: chartData.efectivo.cajero1,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    stack: 'AQUA01'
                },
                {
                    label: 'AQUA01 - Tarjetas',
                    data: chartData.tarjetas.cajero1,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    stack: 'AQUA01'
                },
                {
                    label: 'AQUA02 - Efectivo',
                    data: chartData.efectivo.cajero2,
                    backgroundColor: 'rgba(0, 123, 255, 0.8)',
                    borderColor: '#007bff',
                    borderWidth: 1,
                    stack: 'AQUA02'
                },
                {
                    label: 'AQUA02 - Tarjetas',
                    data: chartData.tarjetas.cajero2,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: '#007bff',
                    borderWidth: 1,
                    stack: 'AQUA02'
                }
            ]
        },
        options: getChartOptions('Comparación de Ingresos por Cajero', true, true)
    });
}

// Gráfica de movimientos de membresía


// Gráfica de ingresos totales diarios
function createDailyRevenueChart(chartData) {
    const ctx = document.getElementById('dailyRevenueChart');
    if (!ctx) return;

    destroyChart('dailyRevenue');

    chartInstances.dailyRevenue = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.fechas,
            datasets: [
                {
                    label: 'Efectivo Total',
                    data: chartData.efectivo.total,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Tarjetas Total',
                    data: chartData.tarjetas.total,
                    backgroundColor: 'rgba(0, 123, 255, 0.8)',
                    borderColor: '#007bff',
                    borderWidth: 1
                },
                {
                    label: 'Membresías Total',
                    data: chartData.membresias.total,
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: '#ffc107',
                    borderWidth: 1
                }
            ]
        },
        options: getChartOptions('Ingresos Diarios por Método de Pago', true, true)
    });
}

// Funciones auxiliares (reutilizadas del código anterior)
function getChartOptions(title, stacked = false, isCurrency = false) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: title,
                font: { size: 16, weight: 'bold' },
                color: '#333'
            },
            legend: {
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: isCurrency ? {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString('es-MX', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                } : {}
            }
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Fecha'
                }
            },
            y: {
                display: true,
                stacked: stacked,
                title: {
                    display: true,
                    text: isCurrency ? 'Monto ($)' : 'Cantidad'
                },
                ticks: isCurrency ? {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-MX');
                    }
                } : {}
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    };
}

function destroyChart(chartName) {
    if (chartInstances[chartName]) {
        chartInstances[chartName].destroy();
        delete chartInstances[chartName];
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit'
    });
}

function toggleChartsPayments() {
    const chartsContainer = document.getElementById('chartsPaymentsContainer');
    if (chartsContainer) {
        if (chartsContainer.style.display === 'none') {
            chartsContainer.style.display = 'block';
            setTimeout(() => {
                Object.values(chartInstances).forEach(chart => {
                    if (chart) chart.resize();
                });
            }, 100);
        } else {
            chartsContainer.style.display = 'none';
        }
    }
}




        function generateSummaryStats(dataTable) {
            const data = dataTable.rows().data().toArray();
            
            if (data.length === 0) {
                document.getElementById('summaryStats').innerHTML = '<div class="col-12"><p class="text-center text-muted">No hay datos disponibles para mostrar estadísticas.</p></div>';
                return;
            }

            // Calcular totales
            const totales = {
                efectivo: data.reduce((sum, row) => sum + (parseFloat(row.suma_total_efectivo) || 0), 0),
                tarjetas: data.reduce((sum, row) => sum + (parseFloat(row.suma_targetas_paquetes) || 0), 0),
                membresias: data.reduce((sum, row) => sum + (parseFloat(row.suma_compra_membrecias) || 0), 0),
                eventos: data.reduce((sum, row) => sum + (parseInt(row.total_eventos) || 0), 0),
                totalGeneral: data.reduce((sum, row) => sum + (parseFloat(row.suma_total_dia) || 0), 0)
            };

            // Calcular por cajero
            const cajeros = {
                aqua01: {
                    efectivo: data.reduce((sum, row) => sum + (parseFloat(row.suma_total_cajero1) || 0), 0),
                    tarjetas: data.reduce((sum, row) => sum + (parseFloat(row.suma_targetas_cajero_1) || 0), 0),
                    compraMemb: data.reduce((sum, row) => sum + (parseFloat(row.suma_comra_membresia_cajero_1) || 0), 0),
                    renovMemb: data.reduce((sum, row) => sum + (parseFloat(row.suma_renovacion_membresia_cajero_1) || 0), 0)
                },
                aqua02: {
                    efectivo: data.reduce((sum, row) => sum + (parseFloat(row.suma_total_cajero2) || 0), 0),
                    tarjetas: data.reduce((sum, row) => sum + (parseFloat(row.suma_targetas_cajero_2) || 0), 0),
                    compraMemb: data.reduce((sum, row) => sum + (parseFloat(row.suma_compra_membresia_cajero_2) || 0), 0),
                    renovMemb: data.reduce((sum, row) => sum + (parseFloat(row.suma_renovacion_membresia_cajero_2) || 0), 0)
                }
            };

            // Calcular totales por cajero
            cajeros.aqua01.total = cajeros.aqua01.efectivo + cajeros.aqua01.tarjetas + cajeros.aqua01.compraMemb + cajeros.aqua01.renovMemb;
            cajeros.aqua02.total = cajeros.aqua02.efectivo + cajeros.aqua02.tarjetas + cajeros.aqua02.compraMemb + cajeros.aqua02.renovMemb;

            // Calcular promedios
            const diasOperacion = data.length;
            const promedios = {
                efectivoDiario: totales.efectivo / diasOperacion,
                totalDiario: totales.totalGeneral / diasOperacion
            };

            // Calcular porcentajes
            const porcentajes = {
                efectivo: totales.totalGeneral > 0 ? (totales.efectivo / totales.totalGeneral) * 100 : 0,
                tarjetas: totales.totalGeneral > 0 ? (totales.tarjetas / totales.totalGeneral) * 100 : 0,
                membresias: totales.totalGeneral > 0 ? (totales.membresias / totales.totalGeneral) * 100 : 0,
                aqua01: totales.totalGeneral > 0 ? (cajeros.aqua01.total / totales.totalGeneral) * 100 : 0,
                aqua02: totales.totalGeneral > 0 ? (cajeros.aqua02.total / totales.totalGeneral) * 100 : 0
            };

            const summaryContainer = document.getElementById('summaryStats');
            if (summaryContainer) {
                summaryContainer.innerHTML = `
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-success h-100">
                            <div class="card-body">
                                <i class="ti ti-cash display-6 text-success mb-2"></i>
                                <h3 class="text-success">${totales.efectivo.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h3>
                                <p class="card-text">Total Efectivo</p>
                                <small class="text-muted">${porcentajes.efectivo.toFixed(1)}% del total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-primary h-100">
                            <div class="card-body">
                                <i class="ti ti-credit-card display-6 text-primary mb-2"></i>
                                <h3 class="text-primary">${totales.tarjetas.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h3>
                                <p class="card-text">Total Tarjetas</p>
                                <small class="text-muted">${porcentajes.tarjetas.toFixed(1)}% del total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-warning h-100">
                            <div class="card-body">
                                <i class="ti ti-id-badge display-6 text-warning mb-2"></i>
                                <h3 class="text-warning">${totales.membresias.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h3>
                                <p class="card-text">Total Membresías</p>
                                <small class="text-muted">${porcentajes.membresias.toFixed(1)}% del total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-danger h-100">
                            <div class="card-body">
                                <i class="ti ti-chart-line display-6 text-danger mb-2"></i>
                                <h3 class="text-danger">${totales.totalGeneral.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h3>
                                <p class="card-text">Total General</p>
                                <small class="text-muted">${totales.eventos.toLocaleString()} eventos</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comparación por Cajero -->
                    <div class="col-12 mt-4">
                        <h6 class="text-info mb-3"><i class="ti ti-building me-2"></i>Comparación por Cajero</h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white text-center">
                                <h6 class="mb-0">CAJERO AQUA01</h6>
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-success">${cajeros.aqua01.total.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h4>
                                <small class="text-muted">${porcentajes.aqua01.toFixed(1)}% del total</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white text-center">
                                <h6 class="mb-0">CAJERO AQUA02</h6>
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-primary">${cajeros.aqua02.total.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h4>
                                <small class="text-muted">${porcentajes.aqua02.toFixed(1)}% del total</small>
                            </div>
                        </div>
                    </div>
                `;
            }
        }



