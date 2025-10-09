@include('layout.shared')

<style type="text/css">
  #your_div_id{
    display: none;
  }


  .bd-example-modal-lg .modal-dialog{
    display: table;
    position: relative;
    margin: 0 auto;
    top: calc(50% - 24px);
  }
  
  .bd-example-modal-lg .modal-dialog .modal-content{
    background-color: transparent;
    border: none;
  }
</style>
<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="#" class="logo d-flex align-items-center">
        <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
        <span class="d-none d-lg-block" style="color:#4CB8B8;"></span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

@include('layout.nav-header')

  </header><!-- End Header -->

  
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Integración COMPAQ</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Facturación</li>
            </ol>
        </nav>
    </div><section class="section dashboard">

        <div class="row">

            <div class="col-12">
                <div class="card">
                    <div class="col-12">
                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('compaq') }}">
                                @csrf
                                <div class="col-md-3">
                                    <label class="col-sm-12 col-form-label">Fecha Inicio</label>
                                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="col-sm-12 col-form-label">Fecha Final</label>
                                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="col-sm-12 col-form-label">Sucursal </label>
                                    <select class="form-select" name="facility" id="facility">
                                        <option value="">--</option>
                                        @foreach ($catalogs->facility_type as $key => $value_facility)
                                            <option value="{{$value_facility}}" @if($facility == $value_facility) selected @endif>{{$value_facility}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="col-sm-12 col-form-label">Tipo de Pago: </label>
                                    <select class="form-select" name="payment_type" id="payment_type">
                                        <option value="">--</option>
                                        @foreach ($catalogs->folio_payment_type as $key => $payment_type)
                                            <option value="{{$key}}" @if($paymentType == $key) selected @endif>{{$payment_type}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="col-sm-12 col-form-label">Folio: </label>
                                    <select class="form-select" name="fiscal_invoice" id="fiscal_invoice">
                                        <option value="">--</option>
                                        <option value="Factura" @if($fiscal_invoice == 'Factura') selected @endif>Factura</option>
                                        <option value="No Facturado" @if($fiscal_invoice == 'No Facturado') selected @endif>No Facturado</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <button class="btn btn-warning w-100 submitBtn" tabindex="6" type="submit">Consultar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="clientes">
            <div class="col-12">
                <div class="col-lg-12">
                    <div class="row" >

                        <div class="col-8">
                            <div class="card top-selling">
                                <div class="card-body pb-0">
                                    <h5 class="card-title">INTEGRACION COMPAQ <span></span></h5>
                                    <form id="global-invoice-form" method="POST" action="{{ route('process_global_invoice') }}">
                                        @csrf
                                        <table id="datatable" class="table table-striped table-bordered datatable">
                                            <thead>
                                                <tr>
                                                    <th>Seleccionar todos<br><input type="checkbox" id="select-all" class="form-check-input"></th>
                                                    <th>Folio</th>
                                                    <th>Fecha</th>
                                                    <th>Total</th>
                                                    <th>Tipo de pago</th>
                                                    <th>Sucursal</th>
                                                    <th>Ticket Fiscal</th>
                                                    <th>Factura Fiscal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($Invoices as $invoices)
                                                    <tr>
                                                        <td>
                                                            @if($invoices->fiscal_invoice)
                                                            @else
                                                                <input type="checkbox" data-transaction-id="{{$invoices->local_transaction_id}}" value="{{$invoices->local_transaction_id}}" class="form-check-input transaction-checkbox">
                                                            @endif
                                                        </td>
                                                        <td>{{$invoices->_id}}</td>
                                                        <td>{{$invoices->TransationDate}}</td>
                                                        <td>${{ number_format($invoices->Total,2) }}</td>
                                                        <td>{{$catalogs->folio_payment_type[$invoices->PaymentType]}}</td>
                                                        <td>{{$invoices->facility}}</td>
                                                        <td>{{$invoices->PaymentFolio}}</td>
                                                        <td>{{$invoices->fiscal_invoice}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="mt-3">
                                            <button type="button" id="submit-invoices" class="btn btn-success submitBtn">Generar Factura Global</button>
                                        </div>
                                    </form>
                                </div>
                            </div></div>

                        <div class="col-4" >
                            <div class="card top-selling">
                                <div class="card-body pb-0">
                                    <table class="table table-borderless ">
                                        <tfoot>
                                            <tr>
                                                <td colspan="6">
                                                    <button type="submit" id="submit-button" class="btn btn-warning w-100 submitBtn">Generar Archivos COMPAQ</button>
                                                    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6">
                                                    <div id="download_file"></div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="card top-selling">
                                <div class="card-body pb-0">
                                    <h6 class="card-title">FACTURAS GLOBALES GENERADAS<span></span></h6>
                                    <table id="global-invoices-table" class="table table-borderless datatable">
                                        <thead>
                                            <tr>
                                                <th>Detalle</th>
                                                <th>Descargar</th>
                                                <th>Fecha Generación</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Global_Invoice as $globalInvoice)
                                                <tr>
                                                    <td><a href="/global_invoice_detalle/{{$globalInvoice->id}}">Detalle</a></td>
                                                    <td><a href="{{ route('global_invoice_download.xml',$globalInvoice->name) }}">XML</a> - <a href="{{ route('global_invoice_download.pdf',$globalInvoice->name) }}">PDF</a></td>
                                                    <td>{{$globalInvoice->updated_at}}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div></div>
        </section>

</main><div class="modal fade bd-example-modal-lg" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="width: 48px">
            <span class="fa fa-spinner fa-spin fa-3x"></span>
        </div>
    </div>
</div>

<footer id="footer" class="footer">
    <div class="copyright">
        &copy; Copyright <strong><span></span></strong>. All Rights Reserved
    </div>
    <div class="credits">
        </div>
</footer><a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

@include('layout.footer')

<script type="text/javascript">

$(document).ready(function(){

    function isDateValidForGlobalInvoice(dateString) {
        if (!dateString) {
            return false;
        }
        const transactionDate = new Date(dateString);
        const now = new Date();
        const firstDayOfLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const endOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);

        // Caso 1: Dentro del mes anterior
        if (transactionDate >= firstDayOfLastMonth && transactionDate <= endOfLastMonth) {
            return true;
        }

        // Caso 2: Misma semana calendario (implementación básica, podría necesitar ajustes más finos)
        const firstSelectedDate = getFirstSelectedTransactionDate();
        if (firstSelectedDate) {
            const transactionWeek = getWeekNumber(transactionDate);
            const firstWeek = getWeekNumber(firstSelectedDate);
            return transactionWeek === firstWeek;
        }

        return false;
    }

    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
        const weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
        return weekNo;
    }

    function getFirstSelectedTransactionDate() {
        const checkedCheckboxes = $('.transaction-checkbox:checked');
        if (checkedCheckboxes.length > 0) {
            const firstCheckedRow = checkedCheckboxes.closest('tr').first();
            const dateCell = firstCheckedRow.find('td:nth-child(3)').text(); // Assuming date is in the 3rd column
            return dateCell ? new Date(dateCell) : null;
        }
        return null;
    }

    function checkSelectedTransactions() {
        const checkedCheckboxes = $('.transaction-checkbox:checked');
        let allDatesValid = true;

        if (checkedCheckboxes.length > 0) {
            checkedCheckboxes.each(function() {
                const row = $(this).closest('tr');
                const dateString = row.find('td:nth-child(3)').text(); // Assuming date is in the 3rd column

                if (!isDateValidForGlobalInvoice(dateString)) {
                    allDatesValid = false;

                    return false; // Break out of the each loop
                }
                
            });
        } else {
            allDatesValid = false; // No transactions selected
        }

        $('#submit-invoices').prop('disabled', !allDatesValid);
    }

    $('#submit-invoices').on('click', function() {
        $(this).prop("disabled",true);
        $('#submit-button').prop("disabled",true);

        var selectedIds = [];
        $('.transaction-checkbox:checked').each(function() {
            selectedIds.push($(this).data('transaction-id'));
        });

        // Enviar los datos al controlador usando AJAX
        $.ajax({
            url: '/process_global_invoice',
            method: 'POST',
            data: {
                selectedIds: selectedIds, _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log(response);
                $(this).prop("disabled",false);
                $('#submit-button').prop("disabled",false);
                window.location.reload(true);
            },
            error: function(error) {
                console.error(error);
                $(this).prop("disabled",false);
                $('#submit-button').prop("disabled",false);
                alert('Ocurrió un error al generar la factura global.');
            }
        });
    });

    $('#submit-button').on('click', function() {
        $(this).prop("disabled",true);
        $('#submit-invoices').prop("disabled",true);

        var selectedIds = [];
        $('.transaction-checkbox:checked').each(function() {
            var localTransactionId = $(this).data('transaction-id');
            selectedIds.push(localTransactionId);
        });

        $.ajax({
            url: '/process_compaq',
            method: 'POST',
            data: {
                selectedIds: selectedIds, _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log(response);
                $('#download_file').html('Archivo creado ');
                $(this).prop("disabled",false);
                $('#submit-invoices').prop("disabled",false);
                window.location.reload(true);
            },
            error: function(error) {
                console.error(error);
                $(this).prop("disabled",false);
                $('#submit-invoices').prop("disabled",false);
                alert('Ocurrió un error al generar los archivos de COMPAQ.');
            }
        });
    });

    // Habilitar/Deshabilitar el botón de Factura Global al cambiar la selección de checkboxes
    $(document).on('change', '.transaction-checkbox', checkSelectedTransactions);
    $(document).on('change', '#select-all', function() {
        $('.transaction-checkbox').prop('checked', this.checked);
        checkSelectedTransactions();
    });
    checkSelectedTransactions(); // Verificar al cargar la página

});

</script>