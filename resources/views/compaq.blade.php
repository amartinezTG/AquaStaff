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

  <?php //include('assets/includes/nav-bar.inc.php');?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Integración COMPAQ</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/facturacion">Dashboard</a></li>
          <li class="breadcrumb-item active">Facturación</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

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

      <!-- Left side FACTURAS -->
      <div class="row" id="clientes">

        <div class="col-12">
          <!-- Left side columns -->
          <div class="col-lg-12">
            <div class="row" >

              <!-- -->
              <div class="col-8">
                <div class="card top-selling">
                  <form class="row g-3" method="POST" action="{{ route('compaq') }}">           
                  @csrf
                  <div class="card-body pb-0">
                    <h5 class="card-title">INTEGRACION COMPAQ <span></span></h5>
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
                      <!-- Paquetes  -->
                     
                        @foreach ($Invoices as $invoices)
                          <tr>
                            <td>
                              @if($invoices->fiscal_invoice)
                              @else
                              <input type="checkbox" data-transaction-id="{{$invoices->local_transaction_id}}" value="{{$invoices->local_transaction_id}}" class="form-check-input">
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

                  </div>
                </form>
                </div><!-- End Promociones y descuentos -->

              </div>

              <div class="col-4" >

                <div class="card top-selling" id="your_div_id">
                  <div class="card-body pb-0">
                    <table  class="table table-borderless ">
                      <tfoot>
                          <tr>
                            <td colspan="6">
                              <button type="submit" id="submit-invoices" class="btn btn-success w-100 submitBtn">Generar Factura Global</button>
                              
                            </td>
                          </tr>

                          <tr>
                            <td colspan="6">
                              <button type="submit" id="submit-button" class="btn btn-warning w-100 submitBtn">Generar Archivos Compaq</button>
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
                    <table  class="table table-borderless ">
                      <tfoot>
                          <tr>
                            <td colspan="6">
                              <h6 class="card-title">ARCHIVOS GENERADOS COMPAQ<span></span></h6>
                            </td>
                          </tr>
                          <tr>
                            <td colspan="6">
                              
                            <table id="datatable" class="table table-borderless datatable">
                              <thead>
                                <tr>
                                  
                                  <th>Descargar</th>
                                  <th>Fecha</th>
                                  <th></th>                    
                                </tr>
                              </thead>
                              <tbody>
                                <!-- Downloads  -->
                                @foreach ($Downloads as $download_file)
                                <tr>
                                  <td><a href="{{ route('download.txt',$download_file->name) }}">{{$download_file->name}}</a></td>
                                  <td>{{$download_file->updated_at}}</td>
                                  <td><a href="/compaq_detalle/{{$download_file->id}}">Detalle</a></td>
                                </tr>
                                @endforeach  
                              </tbody>
                            </table>

                            </td>
                          </tr>
                      </tfoot>
                    </table>
                  </div>

                </div>

                <div class="card top-selling">
                  <div class="card-body pb-0">
                    <table  class="table table-borderless ">
                      <tfoot>
                          <tr>
                            <td colspan="6">
                              <h6 class="card-title">FACTURAS GLOBALES<span></span></h6>
                            </td>
                          </tr>
                          <tr>
                            <td colspan="6">
                              
                            <table id="datatable" class="table table-borderless datatable">
                              <thead>
                                <tr>
                                  <th></th>        
                                  <th>Descargar</th>
                                  <th>Fecha</th>
                                              
                                </tr>
                              </thead>
                              <tbody>
                                <!-- Downloads  -->

                                @foreach ($Global_Invoice as $globalInvoice)
                                <tr>
                                  <td><a href="/global_invoice_detalle/{{$globalInvoice->id}}">Detalle</a></td>
                                  <td><a href="{{ route('global_invoice_download.xml',$globalInvoice->name) }}">XML</a> - <a href="{{ route('global_invoice_download.pdf',$globalInvoice->name) }}">PDF</a></td>
                                  <td>{{$globalInvoice->updated_at}}</td>
                                  
                                </tr>
                                @endforeach  
                              </tbody>
                            </table>

                            </td>
                          </tr>
                      </tfoot>
                    </table>
                  </div>

                </div>

              </div>

            </div>
          </div>
        </div><!-- End Left side columns -->
          
      </div>
      <!-- End CLIENTES -->

    </section>

  </main><!-- End #main -->

<div class="modal fade bd-example-modal-lg" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="width: 48px">
            <span class="fa fa-spinner fa-spin fa-3x"></span>
        </div>
    </div>
</div>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span></span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ 
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>-->
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

@include('layout.footer')

<script type="text/javascript">
  
  $(document).ready(function(){

    $('#submit-invoices').on('click', function() {
      $(this).prop("disabled",true);
      $('#submit-button').prop("disabled",true);

      var selectedIds = [];
      $('input[type="checkbox"]:checked').each(function() {
        //selectedIds.push($(this).closest('tr').find('td:eq(1)').text()); // Suponiendo que el folio está en la segunda columna
        var $tr = $(this).closest('tr');
        var localTransactionId = $(this).data('transaction-id');
        selectedIds.push(localTransactionId);

      });

      //alert(selectedIds);
      /// Enviar los datos al controlador usando AJAX
      $.ajax({
        url: '/process_global_invoice', // Reemplaza con la ruta correcta
        method: 'POST',
          data: {
            selectedIds: selectedIds, _token: "{{ csrf_token() }}"
          },
          success: function(response) {
            // Manejar la respuesta del servidor
            console.log(response);
            // Puedes recargar la página o mostrar un mensaje de éxito
            //$('#download_file').html('Archivo creado ');
            //alert(response);
            $(this).prop("disabled",false);
            $('#submit-button').prop("disabled",false);
            window.location.reload(true);
          },
          error: function(error) {
            // Manejar errores
            console.error(error);
          }
      });
    });


    $('#submit-button').on('click', function() {

      $(this).prop("disabled",true);
      $('#submit-invoices').prop("disabled",true);

      var selectedIds = [];
      
      $('input[type="checkbox"]:checked').each(function() {
        //selectedIds.push($(this).closest('tr').find('td:eq(1)').text()); // Suponiendo que el folio está en la segunda columna
        var $tr = $(this).closest('tr');
        var localTransactionId = $(this).data('transaction-id');
        selectedIds.push(localTransactionId);

      });

      // Enviar los datos al controlador usando AJAX
      $.ajax({
        url: '/process_compaq', // Reemplaza con la ruta correcta
        method: 'POST',
          data: {
            selectedIds: selectedIds, _token: "{{ csrf_token() }}"
          },
          success: function(response) {
            // Manejar la respuesta del servidor
            console.log(response);
            // Puedes recargar la página o mostrar un mensaje de éxito
            $('#download_file').html('Archivo creado ');
            //location.reload();
            //$('#datatable').DataTable().ajax.reload();
            $(this).prop("disabled",false);
            $('#submit-invoices').prop("disabled",false);
            window.location.reload(true);
          },
          error: function(error) {
            // Manejar errores
            console.error(error);
          }
      });
    });


  });

</script>