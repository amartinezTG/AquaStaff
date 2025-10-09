@include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

@include('layout.nav-header')

  </header><!-- End Header -->

  <?php //include('assets/includes/nav-bar.inc.php');?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Reporte</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Portada</a></li>
          <li class="breadcrumb-item "><a href="/transferencias">Transferencias</a> </li>
          <li class="breadcrumb-item active">Transferencia {{$Transfers->transfer_id}}</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">


            <!-- Reports -->
            <!--<div class="col-12">
              <div class="card">
                <div class="filter">
                </div>
                <div class="col-12">
                  <div class="card-body">

                    <form class="row g-3">
                      
                      <div class="col-md-6">
                        <label class="col-sm-4 col-form-label">Fecha Inicio</label>
                        <input type="date" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="col-sm-4 col-form-label">Fecha Final</label>
                        <input type="date" class="form-control">
                      </div>
                      
                      
                    </form>

                  </div>
                </div>

              </div>
            </div> -->

            <!-- Transferencias -->
            <div class="col-8">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Transferencia {{$Transfers->transfer_id}}</h5>

                  <a href="/transferencias"><strong>Regresar</strong></a>
                  @if(session('success'))
                    <div class="alert alert-success">
                      {{ session('success') }}
                    </div>
                  @endif
                    
                    <div class="row">
                       <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label"><strong>Status</strong></label>
                        <div class="col-sm-12">
                          <span class="badge {{$catalogs->transfer_status_color[$Transfers->status]}}">{{$catalogs->transfer_status[$Transfers->status]}}     </span>   
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label"><strong>Fecha creada</strong></label>
                          <div class="col-sm-12">
                            {{$Transfers->created_at}}
                          </div>  
                      </div>


                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label"><strong>Nombre</strong></label>
                        <div class="col-sm-12">
                         {{$Transfers->name}}
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputText" class="col-sm-5 col-form-label"><strong>Número de factura</strong></label>
                        <div class="col-sm-12">
                          {{$Transfers->invoice}}
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label"><strong>Origen</strong></label>
                        <div class="col-sm-12">                          
                          {{$catalogs->facility_type[$Transfers->facility_departure]}}                          
                        </div>
                      </div>                    

                      <div class="col-md-6">
                        <label for="inputEmail" class="col-sm-5 col-form-label"><strong>Entrega a sucursal</strong></label>
                        <div class="col-sm-12">
                          {{$catalogs->facility_type[$Transfers->facility_arrive]}}        
                        </div>
                      </div>

                     
                      <div class="col-md-12">
                        <label for="inputEmail" class="col-sm-5 col-form-label"><strong>Comentarios</strong></label>
                          <div class="col-sm-8">
                            {{$Transfers->comments}}
                          </div>  
                      </div>

                    </div>

                    <div class="row col-12">
                      <h5 class="card-title">Productos</h5>
                      <table class="table table-borderless ">
                        <thead>
                          <tr>                          
                            <th scope="col">Nombre  </th>
                            <th scope="col">Codigo  </th>
                            <th scope="col">Cantidad</th>
                         
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($transferDetails as $detail)
                          <tr>                        
                            <td>{{$detail->product->name}}</td>
                            <td>{{$detail->product->sku}}</td>
                            <td class="col-sm-3">{{$detail->qty}}</td>
                          </tr>
                          @endforeach
                          
                        </tbody>
                      </table>

                    </div>

                    <div class="row">
                      @if (isset($TransfersLogs))


                      <h5 class="card-title">Comentarios</h5>
                      <table class="table table-borderless ">
                        <thead>
                          <tr>                          
                            <th scope="col">Fecha  </th>
                            <th scope="col" >Usuario  </th>
                            <th scope="col">Comentario</th>
                         
                          </tr>
                          <tbody>
                            @foreach ($TransfersLogs as $transferLogs)
                            <tr>                        
                              <td class="col-sm-3">{{$transferLogs->created_at}}</td>
                              <td class="col-sm-3">{{$transferLogs->user_id}}</td>
                              <td >{{$transferLogs->comments}}</td>
                            </tr>
                            @endforeach                          
                          </tbody>
                        </thead>

                      </table>

                      @endif

                    </div>
                  
                </div>

              </div>
            </div><!-- End Recent Sales -->

            <div class="col-4">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Status</h5>
                  <form class="row g-3" method="POST" action="{{ route('submit_transfer_update') }}">
                    @csrf
                    <div class="col-12">
                     
                        <div class="col-sm-12">
                          <select class="form-select" name="status" aria-label="" tabindex="5" @if ($Transfers->status==4) disabled="disabled" @endif required>
                            <option selected>---</option>
                            @foreach ($catalogs->transfer_status as $key => $name)
                              <option value="{{$key}}" @if($Transfers->status == $key) selected @endif>{{$name}}</option>
                            @endforeach
                          </select>
                        </div>

                    </div>

                     <div class="col-12">
                            <input type="hidden" id="transfer_id" name="transfer_id" value="{{$Transfers->transfer_id}}" />
                            <input type="hidden" id="facility_arrive" name="facility_arrive" value="{{$Transfers->facility_arrive}}" />
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                  </form>
                    
                </div>
              </div>

              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Comentarios transferencia {{$Transfers->transfer_id}}</h5>
                  <form class="row g-3" method="POST" action="{{ route('submit_transfer_logs') }}">
                    @csrf
                    <div class="col-12">
                      

                      <label for="inputEmail" class="col-sm-5 col-form-label">Comentarios</label>
                      @error('comments')
                          <br><span class="text-danger">{{ $message }}</span>
                        @enderror
                        <textarea id="comments" name="comments" rows="4" cols="50" class="form-control"></textarea>                      
                    </div>

                    <div class="col-12">
                      <br>
                      <input type="hidden" id="transfer_id" name="transfer_id" value="{{$Transfers->transfer_id}}" />
                  
                      <button type="submit" class="btn btn-primary">Guardar</button>                      
                    </div>
                  </form>
                    
                </div>
              </div>
            </div>


    </section>

  </main><!-- End #main -->

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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">
  
  $(document).ready(function(){

  });

</script>