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
    <h1>Cortes por Cajero</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
        <li class="breadcrumb-item active">Cortes por Cajero</li>
      </ol>
    </nav>
  </div>

  <section class="section">

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
 
    {{-- Controles de mes y acciones --}}
    <div class="card mb-3">
      <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <form method="GET" action="{{ route('cortes.index') }}" class="d-flex align-items-center gap-2">
            <label class="mb-0 fw-semibold">Mes:</label>
            <input type="month" name="mes" value="{{ $mes }}" class="form-control form-control-sm" style="width:160px">
            <button type="submit" class="btn btn-sm btn-secondary">Ver</button>
          </form>
          <a href="{{ route('cortes.create') }}?fecha={{ now()->format('Y-m-d') }}"
             class="btn btn-sm btn-primary ms-auto">
            <i class="bi bi-plus-circle me-1"></i> Capturar Corte
          </a>
        </div>
      </div>
    </div>

    {{-- Tabla resumen mensual --}}
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Resumen {{ \Carbon\Carbon::parse($mes.'-01')->translatedFormat('F Y') }}</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle" style="font-size:.82rem; min-width:900px">
            <thead class="table-dark">
              <tr>
                <th style="min-width:90px">
                  <a href="{{ route('cortes.index') }}?mes={{ $mes }}&orden={{ $ordenOpuesto }}"
                     class="text-white text-decoration-none d-flex align-items-center gap-1">
                    Fecha
                    @if($orden === 'asc')
                      <i class="bi bi-sort-down-alt"></i>
                    @else
                      <i class="bi bi-sort-up-alt"></i>
                    @endif
                  </a>
                </th>
                @foreach($cajas as $caja)
                  <th class="text-center" colspan="3">{{ $caja->nombre }} ({{ $caja->codigo }})</th>
                @endforeach
                <th class="text-end">Total día</th>
                <th class="text-center">Acciones</th>
              </tr>
              <tr class="table-secondary" style="font-size:.75rem">
                <th></th>
                @foreach($cajas as $caja)
                  <th class="text-end">Prosepago</th>
                  <th class="text-end">Efectivo</th>
                  <th class="text-end">Total</th>
                @endforeach
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($fechas as $fecha)
                @php
                  $cortesDelDia = $cortes->get($fecha, collect());
                  $totalDia     = 0;
                  $tieneDatos   = $cortesDelDia->isNotEmpty();
                @endphp
                <tr class="{{ $tieneDatos ? '' : 'text-muted' }}">
                  <td class="fw-semibold">
                    {{ \Carbon\Carbon::parse($fecha)->format('d/m') }}
                    <small class="d-block text-muted" style="font-size:.7rem">
                      {{ \Carbon\Carbon::parse($fecha)->isoFormat('ddd') }}
                    </small>   
                  </td>

                  @foreach($cajas as $caja)
                    @php
                      $c = $cortesDelDia->firstWhere('caja_id', $caja->id);
                      $totalDia += $c ? $c->total_de_venta : 0;
                    @endphp
                    @if($c)
                      <td class="text-end">${{ number_format($c->importe_tarjeta,2) }}</td>
                      <td class="text-end">${{ number_format($c->total_efectivo,2) }}</td>
                      <td class="text-end fw-semibold">${{ number_format($c->total_de_venta,2) }}</td>
                    @else
                      <td class="text-center text-muted" colspan="3">—</td>
                    @endif
                  @endforeach

                  <td class="text-end fw-bold">
                    @if($tieneDatos) ${{ number_format($totalDia,2) }} @else — @endif
                  </td>
                  <td class="text-center">
                    @if($tieneDatos)
                      <button type="button"
                              class="btn btn-outline-primary btn-sm py-0 px-2 btn-ver-corte"
                              data-fecha="{{ $fecha }}">
                        <i class="bi bi-eye"></i>
                      </button>
                    @else
                      <a href="{{ route('cortes.create') }}?fecha={{ $fecha }}"
                         class="btn btn-outline-secondary btn-sm py-0 px-2">
                        <i class="bi bi-plus"></i>
                      </a>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-dark fw-bold">
              <tr>
                <td>TOTAL</td>
                @foreach($cajas as $caja)
                  <td class="text-end">${{ number_format($cortes->flatten()->where('caja_id',$caja->id)->sum('importe_tarjeta'),2) }}</td>
                  <td class="text-end">${{ number_format($cortes->flatten()->where('caja_id',$caja->id)->sum('total_efectivo'),2) }}</td>
                  <td class="text-end">${{ number_format($cortes->flatten()->where('caja_id',$caja->id)->sum('total_de_venta'),2) }}</td>
                @endforeach
                <td class="text-end">${{ number_format($cortes->flatten()->sum('total_de_venta'),2) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

  </section>
</main>

@include('cortes.modal.detalle-dia')
