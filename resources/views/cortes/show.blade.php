@include('layout.shared')

<body class="toggle-sidebar">

<header id="header" class="header fixed-top d-flex align-items-center">
  <div class="d-flex align-items-center justify-content-between">
    <a href="/dashboard" class="logo d-flex align-items-center">
      <img src="https://facturacion.aquacarclub.com/public/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div>
  @include('layout.nav-header')
</header>

<main id="main" class="main">
  
  <div class="pagetitle">
    <h1>Corte del {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cortes.index') }}">Cortes</a></li>
        <li class="breadcrumb-item active">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</li>
      </ol>
    </nav>
  </div>
 
  <section class="section">

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Resumen del día (siempre visible) --}}
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3">
        <div class="card text-center border-0 shadow-sm h-100">
          <div class="card-body py-3">
            <div class="text-muted small">Prosepago del día</div>
            <div class="fs-5 fw-bold text-primary">${{ number_format($totalDia['prosepago'],2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card text-center border-0 shadow-sm h-100">
          <div class="card-body py-3">
            <div class="text-muted small">Efectivo del día</div>
            <div class="fs-5 fw-bold text-success">${{ number_format($totalDia['efectivo'],2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card text-center border-0 shadow-sm h-100">
          <div class="card-body py-3">
            <div class="text-muted small">Venta total del día</div>
            <div class="fs-5 fw-bold text-dark">${{ number_format($totalDia['venta'],2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card text-center border-0 shadow-sm h-100">
          <div class="card-body py-3">
            <div class="text-muted small">Diferencia total</div>
            <div class="fs-5 fw-bold {{ $totalDia['diferencia'] == 0 ? 'text-success' : ($totalDia['diferencia'] > 0 ? 'text-warning' : 'text-danger') }}">
              ${{ number_format($totalDia['diferencia'],2) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Selector de cajero + acciones --}}
    <div class="card mb-3">
      <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">

          <span class="fw-semibold text-muted small me-1">Ver cajero:</span>

          @foreach($cortes as $i => $c)
            @php $diffC = $c->diferencia_real; @endphp
            <button type="button"
                    class="btn btn-cajero {{ $i === 0 ? 'btn-dark' : 'btn-outline-dark' }} btn-sm px-3"
                    data-idx="{{ $i }}">
              {{ $c->caja->nombre }}
              <span class="ms-1 badge
                {{ $diffC == 0 ? 'bg-success' : ($diffC > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                {{ $diffC == 0 ? '✓' : ($diffC > 0 ? '+' : '−') }}
              </span>
            </button>
          @endforeach

          <div class="ms-auto d-flex gap-2">
            @if(auth()->user()->role == 1 || auth()->user()->role == 2)
              <a href="{{ route('cortes.create') }}?fecha={{ $fecha }}"
                 class="btn btn-sm btn-outline-warning">
                <i class="bi bi-pencil me-1"></i> Editar
              </a>
            @endif
            <a href="{{ route('cortes.index') }}" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
          </div>

        </div>
      </div>
    </div>

    {{-- Panel de cada cajero (uno a la vez) --}}
    @foreach($cortes as $i => $corte)
      @php
        $denMxn = $corte->denominaciones->where('moneda','MXN')->keyBy('denominacion');
        $denUsd = $corte->denominaciones->where('moneda','USD')->keyBy('denominacion');
        $denDot = $corte->denominaciones->where('moneda','DOT')->keyBy('denominacion');
        $efectivoContado = (float) $denMxn->sum('monto') + (float) $denUsd->sum('monto') * ($corte->efectivo_dlls_tc ?: $tipoCambio);
        $registradoCajero = (float) $corte->total_efectivo - (float) $corte->dotacion + (float) $corte->cambio_entregado + (float) $corte->cambio_no_entregado;
        $diff   = $corte->diferencia_real;
        $colorDiff = $diff == 0 ? 'bg-success text-white' : ($diff > 0 ? 'bg-warning text-dark' : 'bg-danger text-white');
      @endphp

      <div class="panel-cajero" id="panel-cajero-{{ $i }}"
           style="{{ $i !== 0 ? 'display:none' : '' }}">

        <div class="card">
          <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
            <div>
              <span class="fw-bold fs-6">{{ strtoupper($corte->caja->nombre) }}</span>
              <span class="text-white-50 ms-2 small">({{ $corte->caja->codigo }})</span>
              <span class="ms-3 badge {{ $colorDiff }}">
                Diferencia: ${{ number_format($diff,2) }}
              </span>
            </div>
          </div>

          <div class="card-body p-3" style="font-size:.87rem">
            <div class="row g-4">

              {{-- Columna izquierda --}}
              <div class="col-md-6">

                {{-- VENTAS --}}
                <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem; letter-spacing:.06em">
                  <i class="bi bi-cash-stack me-1"></i>Ventas
                </div>
                <table class="table table-sm table-borderless mb-3">
                  <tr>
                    <td class="text-muted">Total Ventas</td>
                    <td class="text-end fw-semibold">${{ number_format($corte->total_ventas,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Tarjetas ({{ $corte->num_pagos_tarjeta }} pagos)</td>
                    <td class="text-end">${{ number_format($corte->importe_tarjeta,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Efectivo MXN</td>
                    <td class="text-end">${{ number_format($corte->efectivo_mxn,2) }}</td>
                  </tr>
                  @if($corte->efectivo_dlls_cantidad > 0)
                  <tr>
                    <td class="text-muted">Efectivo DLLS ({{ $corte->efectivo_dlls_cantidad }} × ${{ number_format($corte->efectivo_dlls_tc,2) }})</td>
                    <td class="text-end">${{ number_format($corte->efectivo_dlls_importe,2) }}</td>
                  </tr>
                  @endif
                  <tr class="table-light">
                    <td class="fw-semibold">Total Efectivo</td>
                    <td class="text-end fw-semibold">${{ number_format($corte->total_efectivo,2) }}</td>
                  </tr>
                  <tr class="fw-bold border-top">
                    <td>TOTAL DE VENTA</td>
                    <td class="text-end">${{ number_format($corte->total_de_venta,2) }}</td>
                  </tr>
                </table>

                {{-- EGRESOS --}}
                <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem; letter-spacing:.06em">
                  <i class="bi bi-arrow-down-circle me-1"></i>Egresos
                </div>
                <table class="table table-sm table-borderless mb-3">
                  <tr>
                    <td class="text-muted">Saldo Final</td>
                    <td class="text-end">${{ number_format($corte->saldo_dispensador,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Dotación</td>
                    <td class="text-end">${{ number_format($corte->dotacion,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Dotación Final</td>
                    <td class="text-end">${{ number_format($corte->dotacion_final,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Dotación Fija</td>
                    <td class="text-end">${{ number_format($corte->dotacion_determinada,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Entregada</td>
                    <td class="text-end">${{ number_format($corte->dotacion_diferencia,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Pagos Cancelados</td>
                    <td class="text-end">${{ number_format($corte->pagos_cancelados,2) }}</td>
                  </tr>
                  <tr class="table-light fw-semibold">
                    <td>Total Egresos</td>
                    <td class="text-end">${{ number_format($corte->total_egresos,2) }}</td>
                  </tr>
                </table>

                {{-- DESGLOSE DE DOTACIÓN --}}
                @if($denDot->sum('cantidad') > 0)
                <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem; letter-spacing:.06em">
                  <i class="bi bi-cash-coin me-1"></i>Desglose de Dotación
                </div>
                <div class="mb-3 p-2 rounded" style="background:#fff7ed">
                  @php
                    $densDot = ['db50'=>['lbl'=>'B. $50','val'=>50],'db20'=>['lbl'=>'B. $20','val'=>20],'dm5'=>['lbl'=>'M. $5','val'=>5],'dm1'=>['lbl'=>'M. $1','val'=>1]];
                    $totalDot = 0;
                  @endphp
                  <div class="row g-1">
                    @foreach($densDot as $k => $info)
                      @php $cant = $denDot[$k]->cantidad ?? 0; $totalDot += $cant * $info['val']; @endphp
                      @if($cant > 0)
                      <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                        <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                        <span class="fw-semibold">${{ number_format($cant * $info['val'],2) }}</span>
                      </div>
                      @endif
                    @endforeach
                  </div>
                  <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
                    <span>Total Dotación</span><span>${{ number_format($totalDot,2) }}</span>
                  </div>
                </div>
                @endif

                {{-- CAMBIOS --}}
                <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem; letter-spacing:.06em">
                  <i class="bi bi-arrow-left-right me-1"></i>Cambios Entregados
                </div>
                <table class="table table-sm table-borderless mb-3">
                  <tr>
                    <td class="text-muted">Cambio Entregado</td>
                    <td class="text-end">${{ number_format($corte->cambio_entregado,2) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Cambio No Entregado</td>
                    <td class="text-end">${{ number_format($corte->cambio_no_entregado,2) }}</td>
                  </tr>
                  <tr class="table-light fw-semibold">
                    <td>Saldo en Cambio</td>
                    <td class="text-end">${{ number_format($corte->saldo_cambio_entregado,2) }}</td>
                  </tr>
                  @if($corte->referencia_cambio)
                  <tr>
                    <td colspan="2" class="text-muted fst-italic small pt-0">
                      <i class="bi bi-chat-left-text me-1"></i>{{ $corte->referencia_cambio }}
                    </td>
                  </tr>
                  @endif
                </table>

              </div>{{-- col izq --}}

              {{-- Columna derecha --}}
              <div class="col-md-6">

                {{-- DENOMINACIONES MXN --}}
                @if($denMxn->isNotEmpty())
                <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem; letter-spacing:.06em">
                  <i class="bi bi-currency-exchange me-1"></i>Denominaciones MXN
                </div>
                <div class="mb-3 p-2 rounded" style="background:#f0fdf4">
                  @php
                    $billetesMxn = ['b500'=>['lbl'=>'B. $500','val'=>500],'b200'=>['lbl'=>'B. $200','val'=>200],'b100'=>['lbl'=>'B. $100','val'=>100],'b50'=>['lbl'=>'B. $50','val'=>50],'b20'=>['lbl'=>'B. $20','val'=>20],'b10'=>['lbl'=>'B. $10','val'=>10],'b5'=>['lbl'=>'B. $5','val'=>5],'b2'=>['lbl'=>'B. $2','val'=>2],'b1'=>['lbl'=>'B. $1','val'=>1]];
                    $monedasMxn  = ['m10'=>['lbl'=>'M. $10','val'=>10],'m5'=>['lbl'=>'M. $5','val'=>5],'m2'=>['lbl'=>'M. $2','val'=>2],'m1'=>['lbl'=>'M. $1','val'=>1]];
                    $totalBilletes = 0; $totalMonedas = 0;
                  @endphp
                  <div class="small fw-semibold text-muted mb-1">Billetes</div>
                  <div class="row g-1">
                    @foreach($billetesMxn as $k => $info)
                      @php $cant = $denMxn[$k]->cantidad ?? 0; $totalBilletes += $cant * $info['val']; @endphp
                      @if($cant > 0)
                      <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                        <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                        <span class="fw-semibold">${{ number_format($cant * $info['val'],2) }}</span>
                      </div>
                      @endif
                    @endforeach
                  </div>
                  <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
                    <span>Total Billetes</span><span>${{ number_format($totalBilletes,2) }}</span>
                  </div>
                  <div class="small fw-semibold text-muted mt-2 mb-1">Monedas</div>
                  <div class="row g-1">
                    @foreach($monedasMxn as $k => $info)
                      @php $cant = $denMxn[$k]->cantidad ?? 0; $totalMonedas += $cant * $info['val']; @endphp
                      @if($cant > 0)
                      <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                        <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                        <span class="fw-semibold">${{ number_format($cant * $info['val'],2) }}</span>
                      </div>
                      @endif
                    @endforeach
                  </div>
                  <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
                    <span>Total Monedas</span><span>${{ number_format($totalMonedas,2) }}</span>
                  </div>
                </div>
                @endif

                {{-- DENOMINACIONES USD --}}
                @if($denUsd->isNotEmpty())
                <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem; letter-spacing:.06em">
                  <i class="bi bi-currency-dollar me-1"></i>Denominaciones USD
                  <span class="text-muted fw-normal">(TC ${{ number_format($tipoCambio,2) }})</span>
                </div>
                <div class="mb-3 p-2 rounded" style="background:#eff6ff">
                  @php
                    $billetesUsd = ['usd_b50'=>['lbl'=>'B. $50','val'=>50],'usd_b20'=>['lbl'=>'B. $20','val'=>20],'usd_b10'=>['lbl'=>'B. $10','val'=>10],'usd_b5'=>['lbl'=>'B. $5','val'=>5],'usd_b2'=>['lbl'=>'B. $2','val'=>2],'usd_b1'=>['lbl'=>'B. $1','val'=>1]];
                    $totalUsd = 0;
                  @endphp
                  <div class="row g-1">
                    @foreach($billetesUsd as $k => $info)
                      @php $cant = $denUsd[$k]->cantidad ?? 0; $totalUsd += $cant * $info['val']; @endphp
                      @if($cant > 0)
                      <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                        <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                        <span class="fw-semibold">${{ number_format($cant * $info['val'],2) }} USD</span>
                      </div>
                      @endif
                    @endforeach
                  </div>
                  <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
                    <span>Total USD</span>
                    <span>${{ number_format($totalUsd,2) }} = ${{ number_format($totalUsd * $tipoCambio,2) }} MXN</span>
                  </div>
                </div>
                @endif

                {{-- CIERRE (Diferencia Cajero Interlogic) --}}
                <div class="p-3 rounded fw-semibold {{ $colorDiff }} mt-2">
                  <div class="d-flex justify-content-between mb-1">
                    <span>Registrado en Cajero</span>
                    <span>${{ number_format($registradoCajero,2) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span>Reportado por Operador</span>
                    <span>${{ number_format($efectivoContado,2) }}</span>
                  </div>
                  <div class="d-flex justify-content-between fs-6 fw-bold border-top pt-2">
                    <span>DIFERENCIA</span>
                    <span>${{ number_format($diff,2) }}</span>
                  </div>
                </div>

                @if($corte->observaciones)
                <div class="mt-2 text-muted fst-italic small">
                  <i class="bi bi-chat-left-text me-1"></i>{{ $corte->observaciones }}
                </div>
                @endif

                <div class="mt-3 text-muted" style="font-size:.72rem">
                  Capturado por: <strong>{{ $corte->capturadoPor->name ?? 'N/D' }}</strong>
                  · {{ $corte->updated_at->format('d/m/Y H:i') }}
                </div>

              </div>{{-- col der --}}
            </div>{{-- row --}}
          </div>{{-- card-body --}}
        </div>{{-- card --}}
      </div>{{-- panel-cajero --}}
    @endforeach

  </section>
</main>

<footer id="footer" class="footer">
  <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
</footer>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>
@include('layout.footer')

<script>
(function () {
  var btns   = document.querySelectorAll('.btn-cajero');
  var panels = document.querySelectorAll('.panel-cajero');

  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var idx = parseInt(this.dataset.idx);

      // Ocultar todos los paneles
      panels.forEach(function (p) { p.style.display = 'none'; });

      // Mostrar el seleccionado
      var panel = document.getElementById('panel-cajero-' + idx);
      if (panel) panel.style.display = '';

      // Resaltar botón activo
      btns.forEach(function (b) {
        b.classList.remove('btn-dark');
        b.classList.add('btn-outline-dark');
      });
      this.classList.remove('btn-outline-dark');
      this.classList.add('btn-dark');
    });
  });
})();
</script>
