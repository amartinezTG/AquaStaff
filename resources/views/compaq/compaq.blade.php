@include('layout.shared')

<style type="text/css">
    #your_div_id {
        display: none;
    }

    .bd-example-modal-lg .modal-dialog {
        display: table;
        position: relative;
        margin: 0 auto;
        top: calc(50% - 24px);
    }

    .bd-example-modal-lg .modal-dialog .modal-content {
        background-color: transparent;
        border: none;
    }

    /* Estilos para las pestañas */
    .tab-container {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
    }

    .tab-button {
        padding: 10px 15px;
        cursor: pointer;
        border: none;
        background-color: #f8f9fa;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        margin-right: 5px;
    }

    .tab-button.active {
        background-color: #fff;
        border-top: 2px solid #4CB8B8;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    #global-invoice-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #f8f9fa; /* O un color que contraste */
        padding: 10px;
        text-align: right; /* O la alineación que prefieras */
        box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1); /* Sombra para destacar */
        z-index: 100; /* Asegurar que esté por encima de la tabla */
    }

    #global-invoice-actions button {
        /* Estilos para el botón */
        margin-left: 10px;
    }

    /* Ajustar el padding inferior del contenido principal para evitar que el botón lo tape */
    #integration-area {
        padding-bottom: 60px; /* Ajusta este valor según la altura del contenedor fijo */
    }

    .fab {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #28a745; /* Color verde éxito */
        color: white;
        border-radius: 50%;
        width: 56px;
        height: 56px;
        text-align: center;
        line-height: 56px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        z-index: 100;
    }

    .floating-global-container {
        position: fixed;
        bottom: 20px; /* más arriba que el botón de COMPAQ */
        left: 50%;
        transform: translateX(-50%);
        z-index: 999;
        background-color: white;
        padding: 10px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-align: center;
    }

    .floating-compaq-container {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 999;
        background-color: white;
        padding: 10px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-align: center;
    }

    #message_div{ display:none; }
</style>

<!--

1. en que momento se pueden generar, dentro del mes o al siguiente mes.?

Solo dentro del mes, ( Una factura global la puedes realizar el dia primerode cada mes con los lavados del mes anterior) , Pero en caso de que quieran realizarla por semana los debe de dejar.

2. La factura es con pagos en tarjeta y efectivo juntos?

Separados los efectivos y separados por cajeros de tarjetas Una factura por efectivo y otra por tarjeta, su ubiera otra formad e pago se tendría que generar otra factura adicional

3. en la descripción de la factura es: Ventas Globales Mes #?

venta global de los días que abarca la factura

-->
<body class="toggle-sidebar">

<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
            <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            <span class="d-none d-lg-block" style="color:#4CB8B8;"></span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>@include('layout.nav-header')

</header><main id="main" class="main">

    <div class="pagetitle">
        <h1>Facturación Global / Integración COMPAQ</h1>
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
                                            <option value="{{$key}}" @if((string)$paymentType === (string)$key) selected @endif>{{$payment_type}}</option>
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
                                    <label  class="col-sm-12 col-form-label" >Periodicidad:</label>
                                    <select name="periodicity" class="form-select">
                                       
                                        <option value="01" @if($periodicity == '01') selected @endif>Diaria</option>
                                        <option value="02" @if($periodicity == '02') selected @endif>Semanal</option>
                                        <option value="03" @if($periodicity == '03') selected @endif>Quincenal</option>
                                        <option value="04" @if($periodicity == '04') selected @endif>Mensual</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label  class="col-sm-12 col-form-label" >&nbsp;</label>
                                    <button class="btn btn-warning w-100 submitBtn" tabindex="6" type="submit">Consultar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="message_div">

            <div class="col-12">
                <div class="alert alert-warning card">
                    <div class="col-12">
                        <div class="card-body" id="message_block">
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="integration-area">
            <div class="col-12">
                <div class="col-lg-12">
                    <div class="row">

                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Acciones de Integración</h5>
                                    <div class="tab-container">
                                        <button class="tab-button active" data-tab="global-invoice">Generar Factura Global <span id="global-invoice-count">(0)</span></button>
                                        <button class="tab-button" data-tab="compaq-files">Generar Archivos COMPAQ <span id="compaq-files-count">(0)</span></button>
                                        <button class="tab-button" data-tab="global-invoices-list">Facturas Globales Generadas <span id="global-invoices-list-count"></span></button>
                                        <button class="tab-button" data-tab="compaq-history">Archivos Compaq <span id="compaq-history-count"></span></button>
                                    </div>

                                    <div id="global-invoice" class="tab-content active">
                                        <form id="global-invoice-form" method="POST" action="{{ route('process_global_invoice') }}">
                                            @csrf
                                            <table id="datatable-global-invoice" class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Seleccionar todos<br><input type="checkbox" id="select-all-global-invoice" class="form-check-input"></th>
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
                                                    @foreach ($Invoices as $invoice)
                                                        <tr>
                                                            <td>
                                                                @if(!$invoice->fiscal_invoice)
                                                                    <input type="checkbox" data-transaction-id="{{$invoice->local_transaction_id}}" value="{{$invoice->local_transaction_id}}" class="form-check-input transaction-checkbox-global-invoice">
                                                                @endif
                                                            </td>
                                                            <td>{{$invoice->_id}}</td>
                                                            <td>{{$invoice->TransationDate}}</td>
                                                            <td>${{ number_format($invoice->Total,2) }}</td>
                                                            <td>{{$catalogs->folio_payment_type[$invoice->PaymentType]}}</td>
                                                            <td>{{$invoice->facility}}</td>
                                                            <td>{{$invoice->PaymentFolio}}</td>
                                                            <td>{{$invoice->fiscal_invoice}}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div id="global-invoice-floating-btn" class="floating-global-container">
                                                <button type="button" id="submit-invoices" class="btn btn-success submitBtn" disabled>
                                                    <i class="bi bi-receipt"></i> Generar Factura Global 
                                                    <span id="loading-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div id="compaq-files" class="tab-content">
                                        <form id="compaq-form" method="POST" action="{{ route('process_compaq') }}">
                                            @csrf
                                            <table id="datatable-compaq" class="table table-striped table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Seleccionar todos<br><input type="checkbox" id="select-all-compaq" class="form-check-input"></th>
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
                                                @foreach ($Invoices as $invoice)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" data-transaction-id="{{$invoice->local_transaction_id}}" value="{{$invoice->local_transaction_id}}" class="form-check-input transaction-checkbox-compaq">
                                                        </td>
                                                        <td>{{$invoice->_id}}</td>
                                                        <td>{{ $invoice->TransationDate }}</td>
                                                        <td>{{ number_format($invoice->Total,2) }}</td>
                                                        <td>{{$catalogs->folio_payment_type[$invoice->PaymentType]}}</td>
                                                        <td>{{$invoice->facility}}</td>
                                                        <td>{{$invoice->PaymentFolio}}</td>
                                                        <td>{{$invoice->fiscal_invoice}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <div id="compaq-floating-btn" class="floating-compaq-container">
                                                <button type="button" id="submit-button" class="btn btn-warning submitBtn" disabled>
                                                    <i class="bi bi-file-earmark-text"></i> Generar Archivos COMPAQ
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div id="global-invoices-list" class="tab-content">
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

                                    <div id="compaq-history" class="tab-content">
                                        <h3 class="mb-4">Historial de Archivos COMPAQ</h3>
                                        <table class="table table-striped table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre del Archivo</th>
                                                    <th>Fecha de Generación</th>
                                                    <th>Descargar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($compaqFiles as $index => $file)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $file->name }}</td>
                                                        <td>{{ $file->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>
@php
    $filename = 'poliza_38E04D8.txt';
    $url = asset('storage/files/' . $filename);
    $path = storage_path('app/public/files/' . $filename);
@endphp

@if(file_exists($path))
    <a href="/compaq_archivo/{{$file->name}}" target="_blank" class="btn btn-primary btn-sm">Descargar</a>
@else
    <span class="text-muted">No disponible</span>
@endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">No hay archivos generados aún.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

document.addEventListener('DOMContentLoaded', function() {
    const fabButton = document.getElementById('submit-invoices');
    const actualButton = document.querySelector('#global-invoice-form button[type="button"]#submit-invoices');

    if (fabButton && actualButton) {
        fabButton.addEventListener('click', function() {
            //actualButton.click(); // Simular el click en el botón real
        });
    }
});
    
$(document).ready(function() {
    // Funcionalidad de pestañas
    $('.tab-button').on('click', function() {
        const tab = $(this).data('tab');
        $('.tab-button').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + tab).addClass('active');
    });

    // --- JavaScript para Generar Factura Global ---
    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return weekNo;
    }

    function getFirstSelectedGlobalInvoiceDate() {
        const checkedCheckboxes = $('.transaction-checkbox-global-invoice:checked');
        if (checkedCheckboxes.length > 0) {
            const firstCheckedRow = checkedCheckboxes.closest('tr').first();
            const dateCell = firstCheckedRow.find('td:nth-child(3)').text(); // Columna Fecha
            return dateCell ? new Date(dateCell) : null;
        }
        return null;
    }

    function checkIfFirstDayOfMonthSelected() {
        const now = new Date();
        const firstDayOfCurrentMonthString = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
        let isFirstDaySelected = false;
        $('.transaction-checkbox-global-invoice:checked').each(function() {
            const row = $(this).closest('tr');
            const dateString = row.find('td:nth-child(3)').text().split(' ')[0]; // Get only the date part
            if (dateString === firstDayOfCurrentMonthString) {
                isFirstDaySelected = true;
                return false; // Break the loop
            }
        });
        return isFirstDaySelected;
    }

    function checkAllTransactionsSameWeek(checkboxSelector) {
        const checkedCheckboxes = $(checkboxSelector + ':checked');
        if (checkedCheckboxes.length <= 1) {
            return true;
        }
        let firstWeek = null;
        let allSame = true;
        checkedCheckboxes.each(function() {
            const row = $(this).closest('tr');
            const dateString = row.find('td:nth-child(3)').text();
            const currentDate = new Date(dateString);
            const currentWeek = getWeekNumber(currentDate);
            if (firstWeek === null) {
                firstWeek = currentWeek;
            } else if (firstWeek !== currentWeek) {
                allSame = false;
                return false; // Break
            }
        });
        return allSame;
    }

    function isWithinFirstDaysOfMonth(days = 4) {
        const today = new Date();
        return today.getDate() <= days;
    }

    function isDateValidForGlobalInvoice(dateString) {
        if (!dateString) {
            return false;
        }

        const transactionDate = new Date(dateString);
        const now = new Date();
        const firstDayOfLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const endOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);

        const firstSelectedDate = getFirstSelectedGlobalInvoiceDate();
        const allSameWeek = checkAllTransactionsSameWeek('.transaction-checkbox-global-invoice');
        const withinFirst4Days = isWithinFirstDaysOfMonth(4);

        // Permitir facturas del mes anterior durante los primeros 4 días del mes actual
        if (withinFirst4Days && transactionDate >= firstDayOfLastMonth && transactionDate <= endOfLastMonth) {
            return true;
        }

        // O bien si todas las transacciones son de la misma semana
        if (firstSelectedDate && allSameWeek) {
            const transactionWeek = getWeekNumber(transactionDate);
            const firstWeek = getWeekNumber(firstSelectedDate);
            return transactionWeek === firstWeek;
        }

        return false;
    }


    function checkSelectedGlobalInvoices() {
        const checkedCheckboxes = $('.transaction-checkbox-global-invoice:checked');
        let allDatesValid = true;

        if (checkedCheckboxes.length > 0) {
            checkedCheckboxes.each(function() {
                const row = $(this).closest('tr');
                const dateString = row.find('td:nth-child(3)').text(); // Columna Fecha
                if (!isDateValidForGlobalInvoice(dateString)) {
                    allDatesValid = false;
                    return false; // Break
                }
            });
        } else {
            allDatesValid = false;
        }

        $('#submit-invoices').prop('disabled', !allDatesValid);
    }

    $('#submit-invoices').on('click', function() {
        //alert('alert'); 
        $(this).prop("disabled", true).text("Generando...");
        $('#loading-spinner').removeClass('d-none'); // Mostrar el spinner
        $('#submit-button').prop("disabled", true);

        var selectedIds = [];
        $('.transaction-checkbox-global-invoice:checked').each(function() {
            selectedIds.push($(this).data('transaction-id'));
        });

        if (selectedIds.length === 0) {
            alert('Por favor, selecciona al menos una transacción para generar la factura global.');
            $(this).prop("disabled", false);
            $('#submit-button').prop("disabled", false);
            return;
        }

        const periodicity = $('select[name="periodicity"]').val();
        if (!validatePeriodicityJS(periodicity)) {
            alert('Las transacciones seleccionadas no cumplen con la periodicidad especificada.');
            $(this).prop("disabled", false).text("Generar Factura Global");
            $('#loading-spinner').addClass('d-none');
            $('#submit-button').prop("disabled", false);
            return;
        }

        $.ajax({
            url: '/process_global_invoice',
            method: 'POST',
            data: {
                selectedIds: selectedIds,
                periodicity: periodicity,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log(response);
                $(this).prop("disabled", false).text("Generar Factura Global");
                $('#loading-spinner').addClass('d-none'); // Ocultar el spinner
                $('#submit-button').prop("disabled", false);
                window.scrollTo(0, 0);
                $('#message_div').show();
                $('#message_block').html(response.message);
                                      // por si ya estaba visible
                                     // y lo muestras

                // opcional: lo ocultas automáticamente en X segundos
                setTimeout(function() { 
                    $('#message_div').fadeOut(); 
                }, 5000);   

                setTimeout(function() { 
                    window.location.reload(true);
                }, 3000);     

                //alert()
                //window.location.reload(true);
            },
            error: function(error) {
                console.log(error);
                $(this).prop("disabled", false).text("Generar Factura Global");
                $('#loading-spinner').addClass('d-none'); // Ocultar el spinner
                $('#submit-button').prop("disabled", false);
                alert('Ocurrió un error al generar la factura global.');
            }
        });
    });
    

    $(document).on('change', '.transaction-checkbox-global-invoice', checkSelectedGlobalInvoices);
    $(document).on('change', '#select-all-global-invoice', function() {
        $('.transaction-checkbox-global-invoice').prop('checked', this.checked);
        checkSelectedGlobalInvoices();
    });
    checkSelectedGlobalInvoices(); // Inicial check

    // --- JavaScript para Generar Archivos COMPAQ ---
    $('#submit-button').on('click', function() {

        $(this).prop("disabled", true);
        $('#submit-invoices').prop("disabled", true);

        var selectedIds = [];
        $('.transaction-checkbox-compaq:checked').each(function() {
            selectedIds.push($(this).data('transaction-id'));
        });

        if (selectedIds.length === 0) {
            alert('Por favor, selecciona al menos una transacción para generar los archivos de COMPAQ.');
            $(this).prop("disabled", false);
            $('#submit-invoices').prop("disabled", false);
            return;
        }

        $.ajax({
            url: '/process_compaq',
            method: 'POST',
            data: {
                selectedIds: selectedIds,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log(response);
                $('#download_file').html('Archivo creado ');
                $(this).prop("disabled", false);
                $('#submit-invoices').prop("disabled", false);
                window.location.reload(true);
            },
            error: function(error) {
                console.error(error);
                $(this).prop("disabled", false);
                $('#submit-invoices').prop("disabled", false);
                alert('Ocurrió un error al generar los archivos de COMPAQ.');
            }
        });
    });

    $(document).on('change', '.transaction-checkbox-compaq', function() {
        $('#submit-button').prop('disabled', $('.transaction-checkbox-compaq:checked').length === 0);
    });

    $(document).on('change', '#select-all-compaq', function() {
        $('.transaction-checkbox-compaq').prop('checked', this.checked);
        $('#submit-button').prop('disabled', $('.transaction-checkbox-compaq:checked').length === 0);
    });

    $('#submit-button').prop('disabled', $('.transaction-checkbox-compaq:checked').length === 0); // Initial check for Compaq button

    // --- Actualización del conteo de registros en las pestañas ---
    function updateGlobalInvoiceRecordCount() {
        const count = $('#datatable-global-invoice tbody tr').length;
        $('#global-invoice-count').text(`(${count})`);
    }

    function updateCompaqRecordCount() {
        const count = $('#datatable-compaq tbody tr').length;
        $('#compaq-files-count').text(`(${count})`);
    }

    function updateGlobalInvoicesListCount() {
        const count = $('#global-invoices-table tbody tr').length;
        $('#global-invoices-list-count').text(`(${count})`);
    }

    function validatePeriodicityJS(periodicity) {
        const checkedCheckboxes = $('.transaction-checkbox-global-invoice:checked');
        if (checkedCheckboxes.length === 0) return false;

        const dates = [];
        checkedCheckboxes.each(function () {
            const dateStr = $(this).closest('tr').find('td:nth-child(3)').text().trim();
            if (dateStr) {
                dates.push(new Date(dateStr.split(' ')[0]));
            }
        });

        const uniqueDates = [...new Set(dates.map(d => d.toISOString().split('T')[0]))];

        switch (periodicity) {
            case '01': // Diaria
                return uniqueDates.length === 1;

            case '02': // Semanal
                const weekKey = d => {
                    const dt = new Date(d);
                    dt.setUTCDate(dt.getUTCDate() + 4 - (dt.getUTCDay() || 7));
                    const yearStart = new Date(Date.UTC(dt.getUTCFullYear(), 0, 1));
                    return `${Math.ceil((((dt - yearStart) / 86400000) + 1) / 7)}-${dt.getUTCFullYear()}`;
                };
                const weekGroups = [...new Set(dates.map(weekKey))];
                return weekGroups.length === 1;

            case '03': // Quincenal
                const isFirstHalf = dates.every(d => d.getDate() <= 15);
                const isSecondHalf = dates.every(d => d.getDate() > 15);
                return isFirstHalf || isSecondHalf;

            case '04': // Mensual
                const monthGroups = [...new Set(dates.map(d => `${d.getFullYear()}-${d.getMonth()}`))];
                return monthGroups.length === 1;

            default:
                return false;
        }
    }


    // Llama a las funciones de conteo al cargar la página
    updateGlobalInvoiceRecordCount();
    updateCompaqRecordCount();
    updateGlobalInvoicesListCount();

    // Llama a las funciones de conteo cuando cambian las selecciones
    $(document).on('change', '.transaction-checkbox-global-invoice', updateGlobalInvoiceRecordCount);
    $(document).on('change', '#select-all-global-invoice', updateGlobalInvoiceRecordCount);

    $(document).on('change', '.transaction-checkbox-compaq', updateCompaqRecordCount);
    $(document).on('change', '#select-all-compaq', updateCompaqRecordCount);
});

</script>