function membresiasTable() {
    if ($.fn.DataTable.isDataTable('#membresias_table')) {
        $('#membresias_table').DataTable().clear().destroy();
    }

    var fecha_inicio = $('#fecha_inicio').val();
    var fecha_final = $('#fecha_final').val();

    var membresias_table = $('#membresias_table').DataTable({
        colReorder: true,
        layout: {
            topStart: ['buttons'],
            bottomStart: ['pageLength', 'info'],
            bottomEnd: 'paging'
        },
        pageLength: 50,
        order: [[0, 'desc'], [1, 'desc']],
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'buttons-excel',
                text: '<i class="ti ti-file-type-xls"></i> Excel',
                exportOptions: { columns: ':visible' },
                title: 'Membresías Cajero - ' + fecha_inicio + ' al ' + fecha_final
            },
            {
                extend: 'pdf',
                className: 'buttons-pdf',
                text: '<i class="ti ti-file-type-pdf"></i> PDF',
                title: 'Membresías Cajero',
                orientation: 'landscape'
            },
            {
                extend: 'copy',
                className: 'buttons-copy',
                text: '<i class="ti ti-copy"></i> Copiar'
            },
            {
                text: '<i class="ti ti-refresh"></i>',
                className: 'btn-sm btn-success',
                action: function (e, dt, node, config) {
                    membresias_table.clear().draw();
                    membresias_table.ajax.reload();
                    $('.table-responsive').addClass('loader_iiee');
                }
            },
            {
                text: '<i class="ti ti-chart-line"></i> Gráficas',
                className: 'btn-sm btn-primary',
                action: function (e, dt, node, config) {
                    toggleChartsMembresias();
                }
            }
        ],
        ajax: {
            method: 'POST',
            url: '/membresias/membresias_cajero_table',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            data: {
                fecha_inicio: fecha_inicio,
                fecha_final: fecha_final
            },
            error: function() {
                $('.table-responsive').removeClass('loader_iiee');
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los datos",
                });
            },
            beforeSend: function() {
                $('.table-responsive').addClass('loader_iiee');
            },
            complete: function () {
                $('.table-responsive').removeClass('loader_iiee');
            }
        },
        rowId: '_id',
        columns: [
            { data: 'fecha' },
            { data: 'hora' },
            { 
                data: 'tipo_transaccion',
                render: function(data) {
                    if (data === 'Compra') {
                        return '<span class="badge-compra">' + data + '</span>';
                    } else if (data === 'Renovacion') {
                        return '<span class="badge-renovacion">' + data + '</span>';
                    }
                    return data;
                }
            },
            { 
                data: 'tipo_pago',
                render: function(data) {
                    var badgeClass = '';
                    switch(data) {
                        case 'Efectivo':
                            badgeClass = 'badge-efectivo';
                            break;
                        case 'Tarjeta Debito':
                            badgeClass = 'badge-debito';
                            break;
                        case 'Tarjeta Credito':
                            badgeClass = 'badge-credito';
                            break;
                        case 'Cortesia':
                            badgeClass = 'badge-cortesia';
                            break;
                        default:
                            badgeClass = 'badge-secondary';
                    }
                    return '<span class="' + badgeClass + '">' + data + '</span>';
                }
            },
            { 
                data: 'paquete',
                render: function(data) {
                    var badgeClass = '';
                    switch(data) {
                        case 'Express':
                            badgeClass = 'badge-express';
                            break;
                        case 'Básico':
                            badgeClass = 'badge-basico';
                            break;
                        case 'Ultra':
                            badgeClass = 'badge-ultra';
                            break;
                        case 'Delux':
                            badgeClass = 'badge-delux';
                            break;
                        default:
                            badgeClass = 'badge-secondary';
                    }
                    return '<span class="' + badgeClass + '">' + data + '</span>';
                }
            },
            { 
                data: 'total',
                render: function(data) {
                    return '<span class="total-amount">$' + $.fn.dataTable.render.number(',', '.', 2).display(data) + '</span>';
                }
            },
            { data: 'atm' }
        ],
        createdRow: function (row, data, dataIndex) {
            if (data.tipo_transaccion === 'Compra') {
                $(row).addClass('table-success-light');
            }
        },
        initComplete: function () {
            $('.table-responsive').removeClass('loader_iiee');
            console.log('DataTable de membresías inicializada correctamente');
            // Crear gráficas con los datos cargados
            createChartsMembresias(this.api());
        },
        language: {
            "decimal": "",
            "emptyTable": "No hay información disponible",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });
}

// Variables globales para las gráficas
let chartIngresosDia, chartCantidadMembresias, chartTipoPago, chartPaquetes;

// Función para crear todas las gráficas
function createChartsMembresias(api) {
    const data = api.rows({ search: 'applied' }).data().toArray();
    
    if (data.length === 0) {
        console.log('No hay datos para graficar');
        return;
    }

    // Destruir gráficas existentes
    destroyChartsMembresias();

    // Crear cada gráfica
    createIngresosDiaChart(data);
    createCantidadMembresiasChart(data);
    createTipoPagoChart(data);
    createPaquetesChart(data);
}

// Gráfica de Ingresos por Día
function createIngresosDiaChart(data) {
    const ingresosPorDia = {};
    
    data.forEach(row => {
        const fecha = row.fecha;
        if (!ingresosPorDia[fecha]) {
            ingresosPorDia[fecha] = 0;
        }
        ingresosPorDia[fecha] += parseFloat(row.total);
    });

    const fechas = Object.keys(ingresosPorDia).sort();
    const totales = fechas.map(fecha => ingresosPorDia[fecha]);

    const ctx = document.getElementById('ingresosDiaChart');
    if (!ctx) return;

    chartIngresosDia = new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [{
                label: 'Ingresos por Día',
                data: totales,
                borderColor: 'rgb(35, 153, 183)',
                backgroundColor: 'rgba(35, 153, 183, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
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
                            return '$' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-MX');
                        }
                    }
                }
            }
        }
    });
}

// Gráfica de Cantidad de Membresías por Día (Compras y Renovaciones)
function createCantidadMembresiasChart(data) {
    const cantidadPorDia = {};
    
    data.forEach(row => {
        const fecha = row.fecha;
        const tipo = row.tipo_transaccion;
        
        if (!cantidadPorDia[fecha]) {
            cantidadPorDia[fecha] = { Compra: 0, Renovacion: 0 };
        }
        cantidadPorDia[fecha][tipo]++;
    });

    const fechas = Object.keys(cantidadPorDia).sort();
    const compras = fechas.map(fecha => cantidadPorDia[fecha].Compra);
    const renovaciones = fechas.map(fecha => cantidadPorDia[fecha].Renovacion);

    const ctx = document.getElementById('cantidadMembresiasChart');
    if (!ctx) return;

    chartCantidadMembresias = new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [
                {
                    label: 'Compras',
                    data: compras,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Renovaciones',
                    data: renovaciones,
                    borderColor: 'rgb(0, 123, 255)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' membresías';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            }
        }
    });
}

// Gráfica de Tipo de Pago
function createTipoPagoChart(data) {
    const tiposPago = {};
    
    data.forEach(row => {
        const tipo = row.tipo_pago;
        if (!tiposPago[tipo]) {
            tiposPago[tipo] = 0;
        }
        tiposPago[tipo]++;
    });

    const labels = Object.keys(tiposPago);
    const valores = Object.values(tiposPago);
    
    const colores = {
        'Efectivo': 'rgb(40, 167, 69)',
        'Tarjeta Debito': 'rgb(23, 162, 184)',
        'Tarjeta Credito': 'rgb(255, 193, 7)',
        'Cortesia': 'rgb(108, 117, 125)'
    };

    const backgroundColors = labels.map(label => colores[label] || 'rgb(200, 200, 200)');

    const ctx = document.getElementById('tipoPagoChart');
    if (!ctx) return;

    chartTipoPago = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: valores,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Gráfica de Paquetes
function createPaquetesChart(data) {
    const paquetes = {};
    
    data.forEach(row => {
        const paq = row.paquete;
        if (!paquetes[paq]) {
            paquetes[paq] = { cantidad: 0, total: 0 };
        }
        paquetes[paq].cantidad++;
        paquetes[paq].total += parseFloat(row.total);
    });

    const labels = Object.keys(paquetes);
    const cantidades = labels.map(label => paquetes[label].cantidad);
    
    const colores = {
        'Express': 'rgba(25, 118, 210, 0.7)',
        'Básico': 'rgba(123, 31, 162, 0.7)',
        'Ultra': 'rgba(230, 81, 0, 0.7)',
        'Delux': 'rgba(194, 24, 91, 0.7)'
    };

    const backgroundColors = labels.map(label => colores[label] || 'rgba(200, 200, 200, 0.7)');

    const ctx = document.getElementById('paquetesChart');
    if (!ctx) return;

    chartPaquetes = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: cantidades,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const paquete = context.label;
                            const cantidad = context.parsed;
                            const total = paquetes[paquete].total;
                            return [
                                paquete + ': ' + cantidad + ' ventas',
                                'Total: $' + total.toLocaleString('es-MX', {minimumFractionDigits: 2})
                            ];
                        }
                    }
                }
            }
        }
    });
}

// Toggle para mostrar/ocultar gráficas
function toggleChartsMembresias() {
    const chartsContainer = document.getElementById('chartsContainer');
    if (chartsContainer.style.display === 'none') {
        chartsContainer.style.display = 'flex';
        // Scroll suave hacia las gráficas
        chartsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        chartsContainer.style.display = 'none';
    }
}

// Destruir gráficas existentes
function destroyChartsMembresias() {
    if (chartIngresosDia) chartIngresosDia.destroy();
    if (chartCantidadMembresias) chartCantidadMembresias.destroy();
    if (chartTipoPago) chartTipoPago.destroy();
    if (chartPaquetes) chartPaquetes.destroy();
}