console.log('Cajero JS cargado correctamente');


function CajerosDaTable(){
    if ($.fn.DataTable.isDataTable("#CajerosTable")) {
        $("#CajerosTable").DataTable().destroy();
        $("#CajerosTable thead .filter").remove();
    }

    $("#CajerosTable thead").prepend(
        $("#CajerosTable thead tr:eq(0)").clone().addClass("filter")
    );

    $("#CajerosTable thead tr.filter th").each(function (index) {
        var title = $(this).text();
        $(this).html(
            '<input type="text" class="form-control form-control-sm" placeholder="' + title + '" />'
        );
    });

    $("#CajerosTable thead tr.filter input").on("keyup change", function () {
        var table = $("#CajerosTable").DataTable();
        // Usar el índice directo del th dentro de su tr, sin :visible
        var colIndex = $(this).closest('th').index();
        table.column(colIndex).search(this.value).draw();
    });
    var fecha_inicio = $('#fecha_inicio').val();
    var fecha_final = $('#fecha_final').val();
    var CajerosTable = $('#CajerosTable').DataTable({
        dom: '<"top"Bf>rt<"bottom"lip>',
        pageLength: 100,
        order: [
            [1, 'desc'],[2, 'desc']
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
            }
                
        ],
        ajax: {
            method: 'POST',
            url: '/cajero/CajerosTable',
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
            { data: '_id'},
            { data: 'local_transaction_id'},
            { data: 'fecha'},
            { data: 'hora'},
            { data: 'cliente'},
            { data: 'package_name'},
            { data: 'Atm'},
            { data: 'method'},
            { data: 'tipo_transaccion'},
            { data: 'Total'},
            { data: 'CadenaFacturacion'},
            { data: 'fiscal_invoice'},
            { data: 'rfc'},
            { data: 'company_name'},
            ],
        createdRow: function (row, data, dataIndex) {
        },
        initComplete: function () {
            console.log('DataTable inicializada correctamente');
        }
    });
}
