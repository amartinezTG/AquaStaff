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
    <h1>Detalle de Corte — {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h1>
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

    {{-- Botones de acción --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
      <a href="{{ route('cortes.create') }}?fecha={{ $fecha }}" class="btn btn-sm btn-outline-warning">
        <i class="bi bi-pencil me-1"></i> Editar cortes del día
      </a>
      <a href="{{ route('cortes.index') }}" class="btn btn-sm btn-outline-secondary ms-auto">
        <i class="bi bi-arrow-left me-1"></i> Volver al resumen
      </a>
    </div>

    {{-- Resumen del día --}}
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body py-3">
            <div class="text-muted small">Total Prosepago (día)</div>
            <div class="fs-4 fw-bold text-primary">${{ number_format($totalDia['prosepago'],2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body py-3">
            <div class="text-muted small">Total Efectivo (día)</div>
            <div class="fs-4 fw-bold text-success">${{ number_format($totalDia['efectivo'],2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body py-3">
            <div class="text-muted small">Venta Total (día)</div>
            <div class="fs-4 fw-bold text-dark">${{ number_format($totalDia['venta'],2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body py-3">
            <div class="text-muted small">Diferencia Total</div>
            <div class="fs-4 fw-bold {{ $totalDia['diferencia'] == 0 ? 'text-success' : ($totalDia['diferencia'] > 0 ? 'text-warning' : 'text-danger') }}">
              ${{ number_format($totalDia['diferencia'],2) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Detalle por cajero --}}
    <div class="row g-3">
      @foreach($cortes as $corte)
        @php
          $denMxn = $corte->denominaciones->where('moneda','MXN')->keyBy('denominacion');
          $denUsd = $corte->denominaciones->where('moneda','USD')->keyBy('denominacion');
          $diff   = $corte->diferencia;
        @endphp
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
              <span class="fw-bold">{{ strtoupper($corte->caja->nombre) }} — {{ $corte->caja->codigo }}</span>
              @if(auth()->user()->role == 1 || auth()->user()->role == 2)
                <a href="{{ route('cortes.edit', $corte->id) }}" class="btn btn-sm btn-outline-light py-0">
                  <i class="bi bi-pencil"></i>
                </a>
              @endif
            </div>
            <div class="card-body p-3" style="font-size:.85rem">

              {{-- VENTAS --}}
              <div class="fw-semibold text-uppercase text-muted mb-1" style="font-size:.75rem; letter-spacing:.05em">
                Ventas
              </div>
              <table class="table table-sm table-borderless mb-2">
                <tr><td>Total Ventas</td>
                    <td class="text-end fw-semibold">${{ number_format($corte->total_ventas,2) }}</td></tr>
                <tr><td>Tarjetas ({{ $corte->num_pagos_tarjeta }} pagos)</td>
                    <td class="text-end">${{ number_format($corte->importe_tarjeta,2) }}</td></tr>
                <tr><td>Efectivo MXN</td>
                    <td class="text-end">${{ number_format($corte->efectivo_mxn,2) }}</td></tr>
                @if($corte->efectivo_dlls_cantidad > 0)
                <tr><td>Efectivo DLLS ({{ $corte->efectivo_dlls_cantidad }} × ${{ $corte->efectivo_dlls_tc }})</td>
                    <td class="text-end">${{ number_format($corte->efectivo_dlls_importe,2) }}</td></tr>
                @endif
                <tr class="table-light"><td class="fw-semibold">Total Efectivo</td>
                    <td class="text-end fw-semibold">${{ number_format($corte->total_efectivo,2) }}</td></tr>
                <tr class="fw-bold"><td>TOTAL DE VENTA</td>
                    <td class="text-end">${{ number_format($corte->total_de_venta,2) }}</td></tr>
              </table>

              {{-- EGRESOS --}}
              <div class="fw-semibold text-uppercase text-muted mb-1 mt-2" style="font-size:.75rem; letter-spacing:.05em">
                Egresos
              </div>
              <table class="table table-sm table-borderless mb-2">
                <tr><td>Dotación</td>
                    <td class="text-end">${{ number_format($corte->dotacion,2) }}</td></tr>
                <tr><td>Pagos Cancelados</td>
                    <td class="text-end">${{ number_format($corte->pagos_cancelados,2) }}</td></tr>
                <tr class="fw-semibold table-light"><td>Total Egresos</td>
                    <td class="text-end">${{ number_format($corte->total_egresos,2) }}</td></tr>
              </table>

              {{-- DISPENSADORES --}}
              <div class="fw-semibold text-uppercase text-muted mb-1 mt-2" style="font-size:.75rem; letter-spacing:.05em">
                Dispensadores
              </div>
              <table class="table table-sm table-borderless mb-2">
                <tr><td>Saldo Final</td>
                    <td class="text-end">${{ number_format($corte->saldo_inicial_dispensador,2) }}</td></tr>
                <tr><td>Dotación</td>
                    <td class="text-end">${{ number_format($corte->dotacion,2) }}</td></tr>
                <tr class="fw-semibold table-light"><td>Dotación Final (Dispensador)</td>
                    <td class="text-end">${{ number_format($corte->dotacion_final,2) }}</td></tr>
                <tr><td>Saldo en Dispensadores</td>
                    <td class="text-end">${{ number_format($corte->saldo_dispensador,2) }}</td></tr>
              </table>

              {{-- CAMBIOS --}}
              <div class="fw-semibold text-uppercase text-muted mb-1 mt-2" style="font-size:.75rem; letter-spacing:.05em">
                Cambios Entregados
              </div>
              <table class="table table-sm table-borderless mb-2">
                <tr><td>Cambio Entregado</td>
                    <td class="text-end">${{ number_format($corte->cambio_entregado,2) }}</td></tr>
                <tr><td>Cambio No Entregado</td>
                    <td class="text-end">${{ number_format($corte->cambio_no_entregado,2) }}</td></tr>
                <tr class="fw-semibold table-light"><td>Saldo en Cambio</td>
                    <td class="text-end">${{ number_format($corte->saldo_cambio_entregado,2) }}</td></tr>
                @if($corte->referencia_cambio)
                <tr><td colspan="2" class="text-muted fst-italic">{{ $corte->referencia_cambio }}</td></tr>
                @endif
              </table>

              {{-- DENOMINACIONES --}}
              @if($denMxn->isNotEmpty() || $denUsd->isNotEmpty())
              <div class="fw-semibold text-uppercase text-muted mb-1 mt-2" style="font-size:.75rem; letter-spacing:.05em">
                Denominaciones
              </div>
              <div class="row g-2">
                @if($denMxn->isNotEmpty())
                <div class="col-6">
                  <div class="small fw-semibold mb-1">Pesos MXN</div>
                  @foreach(['b500'=>'B.$500','b200'=>'B.$200','b100'=>'B.$100','b50'=>'B.$50','b20'=>'B.$20','b10'=>'B.$10','b5'=>'B.$5','b2'=>'B.$2','b1'=>'B.$1','m10'=>'M.$10','m5'=>'M.$5','m2'=>'M.$2','m1'=>'M.$1'] as $k => $lbl)
                    @if(($denMxn[$k]->cantidad ?? 0) > 0)
                    <div class="d-flex justify-content-between" style="font-size:.78rem">
                      <span>{{ $lbl }}</span>
                      <span>{{ $denMxn[$k]->cantidad }} = ${{ number_format($denMxn[$k]->monto,2) }}</span>
                    </div>
                    @endif
                  @endforeach
                </div>
                @endif
                @if($denUsd->isNotEmpty())
                <div class="col-6">
                  <div class="small fw-semibold mb-1">USD (TC: ${{ number_format($tipoCambio,2) }})</div>
                  @foreach(['usd_b50'=>'B.$50','usd_b20'=>'B.$20','usd_b10'=>'B.$10','usd_b5'=>'B.$5','usd_b2'=>'B.$2','usd_b1'=>'B.$1'] as $k => $lbl)
                    @if(($denUsd[$k]->cantidad ?? 0) > 0)
                    <div class="d-flex justify-content-between" style="font-size:.78rem">
                      <span>{{ $lbl }}</span>
                      <span>{{ $denUsd[$k]->cantidad }} = ${{ number_format($denUsd[$k]->monto,2) }}</span>
                    </div>
                    @endif
                  @endforeach
                </div>
                @endif
              </div>
              @endif

              {{-- CIERRE --}}
              <div class="mt-3 p-3 rounded fw-bold text-center fs-6
                {{ $diff == 0 ? 'bg-success text-white' : ($diff > 0 ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                <div>Corte Total Efectivo: ${{ number_format($corte->corte_total_efectivo,2) }}</div>
                <div>Efectivo Entregado: ${{ number_format($corte->efectivo_entregado,2) }}</div>
                <div class="mt-1 fs-5">Diferencia: ${{ number_format($diff,2) }}</div>
              </div>

              @if($corte->observaciones)
              <div class="mt-2 text-muted fst-italic small">
                <i class="bi bi-chat-left-text me-1"></i>{{ $corte->observaciones }}
              </div>
              @endif

              <div class="mt-2 text-muted" style="font-size:.72rem">
                Capturado por: {{ $corte->capturadoPor->name ?? 'N/D' }}
                · {{ $corte->updated_at->format('d/m/Y H:i') }}
              </div>

            </div>
          </div>
        </div>
      @endforeach
    </div>

  </section>
</main>

<footer id="footer" class="footer">
  <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
</footer>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>
@include('layout.footer')
