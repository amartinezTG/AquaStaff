@include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <!--<img src="{{ public_path('img/AQUA-CAR-CLUB-LOGO-N.png') }}" style="width: 120px;">-->
    </div><!-- End Logo -->
  </header><!-- End Header -->

  <main id="main" class="main">

    <div class="container">
    <h2>Corte de Caja #{{ $corteCaja->id }}</h2>

    <div class="grid">
        <div class="column section">
            <h4>Datos Generales</h4>
            <table>
                <tr><td><strong>Fecha de Corte</strong></td><td>{{ $corteCaja->fecha_corte }}</td></tr>
                <tr><td><strong>Fecha que se hizo</strong></td><td>{{ $corteCaja->created_at }}</td></tr>
                <tr><td><strong>Sucursal</strong></td><td>{{ $corteCaja->sucursal }}</td></tr>
                <tr><td><strong>Quien lo hizo</strong></td><td>{{ $corteCaja->usuario->name ?? 'N/A' }}</td></tr>
                <tr><td><strong>Total Ventas</strong></td><td>${{ number_format($corteCaja->total_ventas, 2) }}</td></tr>
                <tr><td><strong>Total Vehículos</strong></td><td>{{ $corteCaja->total_tickets }}</td></tr>
                <tr><td><strong>Efectivo Acumulado</strong></td><td>${{ number_format($corteCaja->dinero_acumulado_efectivo, 2) }}</td></tr>
                <tr><td><strong>Tarjeta Acumulado</strong></td><td>${{ number_format($corteCaja->dinero_acumulado_tarjeta, 2) }}</td></tr>
                <tr><td><strong>Dinero Recibido</strong></td><td>${{ number_format($corteCaja->dinero_recibido, 2) }}</td></tr>
                <tr><td><strong>Tipo de cambio</strong></td><td>{{ $corteCaja->tipo_cambio ?? '-' }}</td></tr>
                <tr><td><strong>Total efectivo MXN</strong></td><td>${{ number_format($total_efectivo_en_mxn, 2) }}</td></tr>
                <tr><td><strong>Total efectivo USD</strong></td><td>${{ number_format($total_usd, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <h3>Denominaciones MXN</h3>
<table>
    <tr><th>Denominación</th><th>Cantidad</th></tr>
@php use Illuminate\Support\Str; @endphp

@foreach($corteCaja->detallesArqueo->filter(fn($d) => Str::startsWith($d->denominacion, 'mxn_')) as $item)
    <tr><td>${{ str_replace('mxn_', '', $item->denominacion) }}</td><td>{{ $item->cantidad }}</td></tr>
@endforeach
</table>
    </div>

    <div class="section">
        <h3>Denominaciones USD</h3>
        <table>
            <tr><th>Denominación</th><th>Cantidad</th></tr>
            @foreach($corteCaja->detallesArqueo->filter(fn($d) => Str::startsWith($d->denominacion, 'usd_')) as $item)
    <tr><td>${{ str_replace(['usd_', 'c'], ['', '¢'], $item->denominacion) }}</td><td>{{ $item->cantidad }}</td></tr>
@endforeach
        </table>
    </div>

    @if(!empty($Comentarios))
    <div class="section">
        <h4>Comentarios</h4>
        <p>{{ $Comentarios }}</p>
    </div>
    @endif
</div>

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

