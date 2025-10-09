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

  
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Membresías</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">Membresías</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->


    <section class="section dashboard">

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

                <form class="row g-3" method="POST" action="{{ route('indicadores-membresias') }}">                
                  @csrf
                  <div class="col-md-5">
                    <label class="col-sm-6 col-form-label">Fecha Inicio</label>
                    <input type="datetime-local" class="form-control" name="startDate" value="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d\TH:i') }}">
                  </div>

                  <div class="col-md-5">
                    <label class="col-sm-4 col-form-label">Fecha Final</label>
                    <input type="datetime-local" class="form-control" name="endDate" value="{{ \Carbon\Carbon::parse($endDate)->format('Y-m-d\TH:i') }}">
                  </div>  

                  <div class="col-md-2"><label class="col-sm-4 col-form-label">&nbsp;</label>
                    <button class="btn btn-warning w-100 submitBtn" tabindex="6" type="submit">Consultar</button>
                  </div>                
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">
<!--
<p><a href="/exportar-indicadores/">Descargar Reporte</a></p>
-->

<p>
<a href="{{ route('exportar-membresias', ['startDate' => request('startDate'), 'endDate' => request('endDate')]) }}" class="btn btn-success" target="_blank">Descargar XLS</a>
- 
<a href="{{ route('exportar_membresias_pdf', ['startDate' => request('startDate'), 'endDate' => request('endDate')]) }}" class="btn btn-info text-white" target="_blank">Descargar PDF</a>
</p>

<p><strong>Total de uso de membresías:</strong> {{ $resultado->Total_Clientes_Con_Membresia }} <br>
 <strong> Uso promedio: </strong>{{ number_format($resultado->Uso_Promedio, 2) }}
<table class="table table-striped">
  <thead>
    <tr>
        <th colspan="20" class=" mb-2 text-white text-center" style="background-color:#31bec5;">Aqua Car Club Misiones - Uso de Membresias<br>
       del 
    <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D [de] MMMM [de] YYYY') }}</strong> 
    al 
    <strong>{{ \Carbon\Carbon::parse($endDate)->isoFormat('D [de] MMMM [de] YYYY') }}</strong>
</th>
    </tr>
   
    <tr class=" mb-2 bg-dark p-3">
      <th>Referencia o ID</th>
      <th>Nombre Ligado al ID</th>
      <th>Usos</th>
      <th>Paquete asignado originalmente</th>
      <th>Inscrito desde</th>
      <th>Costo de membresía</th>
      <th>Ticket promedio</th>
    </tr>
  </thead>
    @foreach ($resultados as $item)
      <tr>
          <td>{{ $item->Referencia }}</td>
          <td>{{ $item->Nombre }} {{ $item->Apellido }}</td>
          <td>{{ $item->usos }}</td>
          <td>{{ $item->Nombre_Paquete }}</td>
          <td>{{ $item->Fecha_PrimerCobro ? \Carbon\Carbon::parse($item->Fecha_PrimerCobro)->format('Y-m-d') : '' }} </td>
          <td>${{ number_format($item->Precio, 2) }}</td>
          <td>${{ number_format($item->ticket_promedio,2) }}</td>
      </tr>
  @endforeach

</table>

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
</section>
</main>
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


</body>