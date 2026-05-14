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
    <h1>Editar Corte #{{ $corte->id }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cortes.index') }}">Cortes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cortes.show', $corte->fecha_corte->format('Y-m-d')) }}">{{ $corte->fecha_corte->format('d/m/Y') }}</a></li>
        <li class="breadcrumb-item active">Editar</li>
      </ol>
    </nav>
  </div>

  <section class="section">

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('cortes.update', $corte->id) }}">
      @csrf
      @method('PUT')

      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row align-items-center g-3">
            <div class="col-auto">
              <span class="fw-semibold">Sucursal:</span>
              <span class="badge bg-primary ms-2 fs-6">{{ $facility->name }}</span>
            </div>
            <div class="col-auto">
              <span class="fw-semibold">Fecha:</span>
              <span class="ms-2">{{ $corte->fecha_corte->format('d/m/Y') }}</span>
            </div>
            <div class="col-auto">
              <span class="fw-semibold">Caja:</span>
              <span class="badge bg-dark ms-2 fs-6">{{ $corte->caja->nombre }} ({{ $corte->caja->codigo }})</span>
            </div>
            <div class="col-auto ms-auto">
              <span class="text-muted small">TC vigente: <strong>${{ number_format($tipoCambio,2) }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-6">

          {{-- VENTAS --}}
          <div class="card mb-2">
            <div class="card-header py-2 bg-primary text-white fw-semibold">
              <i class="bi bi-cash-stack me-1"></i> Ventas
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label small mb-1">Total Ventas</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="total_ventas"
                           value="{{ $corte->total_ventas }}" class="form-control">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1"># Pagos Tarjeta</label>
                  <input type="number" min="0" name="num_pagos_tarjeta"
                         value="{{ $corte->num_pagos_tarjeta }}" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Importe Tarjeta</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="importe_tarjeta"
                           value="{{ $corte->importe_tarjeta }}" class="form-control" id="e_tarjeta">
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label small mb-1">Efectivo MXN</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="efectivo_mxn"
                           value="{{ $corte->efectivo_mxn }}" class="form-control" id="e_mxn">
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label small mb-1">Efectivo DLLS</label>
                  <input type="number" step="0.01" min="0" name="efectivo_dlls_cantidad"
                         value="{{ $corte->efectivo_dlls_cantidad }}" class="form-control form-control-sm" id="e_dlls">
                </div>
                <div class="col-4">
                  <label class="form-label small mb-1">TC DLLS</label>
                  <input type="number" step="0.0001" min="0" name="efectivo_dlls_tc"
                         value="{{ $corte->efectivo_dlls_tc }}" class="form-control form-control-sm" id="e_tc">
                </div>
              </div>
            </div>
          </div>

          {{-- EGRESOS --}}
          <div class="card mb-2">
            <div class="card-header py-2 bg-warning fw-semibold">
              <i class="bi bi-arrow-down-circle me-1"></i> Egresos
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Dotación</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="dotacion"
                           value="{{ $corte->dotacion }}" class="form-control">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Pagos Cancelados</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="pagos_cancelados"
                           value="{{ $corte->pagos_cancelados }}" class="form-control">
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- DISPENSADORES --}}
          <div class="card mb-2">
            <div class="card-header py-2 bg-info text-white fw-semibold">
              <i class="bi bi-safe me-1"></i> Dispensadores
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Saldo Final</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="saldo_inicial_dispensador"
                           value="{{ $corte->saldo_inicial_dispensador }}" class="form-control">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Dotación Final</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="dotacion_final"
                           value="{{ $corte->dotacion_final }}" class="form-control">
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- CAMBIOS --}}
          <div class="card mb-2">
            <div class="card-header py-2 bg-secondary text-white fw-semibold">
              <i class="bi bi-arrow-left-right me-1"></i> Cambios
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Cambio Entregado</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="cambio_entregado"
                           value="{{ $corte->cambio_entregado }}" class="form-control">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Cambio No Entregado</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="cambio_no_entregado"
                           value="{{ $corte->cambio_no_entregado }}" class="form-control">
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label small mb-1">Referencia</label>
                  <input type="text" name="referencia_cambio"
                         value="{{ $corte->referencia_cambio }}" class="form-control form-control-sm">
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="col-lg-6">

          {{-- DENOMINACIONES MXN --}}
          <div class="card mb-2">
            <div class="card-header py-2 fw-semibold" style="background:#e8f5e9">
              <i class="bi bi-currency-exchange me-1"></i> Denominaciones MXN
            </div>
            <div class="card-body p-3">
              <div class="row g-1" style="font-size:.82rem">
                @php
                  $bMxn = ['b500'=>'B.$500','b200'=>'B.$200','b100'=>'B.$100','b50'=>'B.$50','b20'=>'B.$20','b10'=>'B.$10','b5'=>'B.$5','b2'=>'B.$2','b1'=>'B.$1'];
                  $mMxn = ['m10'=>'M.$10','m5'=>'M.$5','m2'=>'M.$2','m1'=>'M.$1'];
                @endphp
                <div class="col-12"><small class="text-muted fw-semibold">BILLETES</small></div>
                @foreach($bMxn as $k => $lbl)
                  <div class="col-4">
                    <label class="form-label mb-0">{{ $lbl }}</label>
                    <input type="number" min="0" name="den_mxn[{{ $k }}]"
                           value="{{ $denMxn[$k]->cantidad ?? '' }}"
                           class="form-control form-control-sm" placeholder="0">
                  </div>
                @endforeach
                <div class="col-12 mt-1"><small class="text-muted fw-semibold">MONEDAS</small></div>
                @foreach($mMxn as $k => $lbl)
                  <div class="col-3">
                    <label class="form-label mb-0">{{ $lbl }}</label>
                    <input type="number" min="0" name="den_mxn[{{ $k }}]"
                           value="{{ $denMxn[$k]->cantidad ?? '' }}"
                           class="form-control form-control-sm" placeholder="0">
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          {{-- DENOMINACIONES USD --}}
          <div class="card mb-2">
            <div class="card-header py-2 fw-semibold" style="background:#e3f2fd">
              <i class="bi bi-currency-dollar me-1"></i> Denominaciones USD
            </div>
            <div class="card-body p-3">
              <div class="row g-1" style="font-size:.82rem">
                @foreach(['usd_b50'=>'B.$50','usd_b20'=>'B.$20','usd_b10'=>'B.$10','usd_b5'=>'B.$5','usd_b2'=>'B.$2','usd_b1'=>'B.$1'] as $k => $lbl)
                  <div class="col-4">
                    <label class="form-label mb-0">{{ $lbl }}</label>
                    <input type="number" min="0" name="den_usd[{{ $k }}]"
                           value="{{ $denUsd[$k]->cantidad ?? '' }}"
                           class="form-control form-control-sm" placeholder="0">
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          {{-- CIERRE --}}
          <div class="card border-dark">
            <div class="card-header py-2 bg-dark text-white fw-semibold">
              <i class="bi bi-check2-circle me-1"></i> Cierre
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Corte Total Efectivo</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="corte_total_efectivo"
                           value="{{ $corte->corte_total_efectivo }}" class="form-control" id="e_corte">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Efectivo Entregado</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="efectivo_entregado"
                           value="{{ $corte->efectivo_entregado }}" class="form-control" id="e_entregado">
                  </div>
                </div>
                <div class="col-12">
                  <div class="p-2 rounded text-center fw-bold" id="diff-badge" style="background:#f8f9fa; border:2px solid #dee2e6">
                    Diferencia: <span id="txt-diff">$0.00</span>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label small mb-1">Observaciones</label>
                  <textarea name="observaciones" class="form-control form-control-sm" rows="2">{{ $corte->observaciones }}</textarea>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
        <a href="{{ route('cortes.show', $corte->fecha_corte->format('Y-m-d')) }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary px-5">
          <i class="bi bi-save me-1"></i> Guardar Cambios
        </button>
      </div>

    </form>
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
  function calcDiff() {
    var corte     = parseFloat(document.getElementById('e_corte')?.value)     || 0;
    var entregado = parseFloat(document.getElementById('e_entregado')?.value) || 0;
    var diff      = entregado - corte;
    var badge     = document.getElementById('diff-badge');
    var txt       = document.getElementById('txt-diff');
    txt.textContent = '$' + diff.toFixed(2);
    if (diff === 0)      { badge.style.background='#d4edda'; badge.style.borderColor='#28a745'; badge.style.color='#155724'; }
    else if (diff > 0)   { badge.style.background='#fff3cd'; badge.style.borderColor='#ffc107'; badge.style.color='#856404'; }
    else                 { badge.style.background='#f8d7da'; badge.style.borderColor='#dc3545'; badge.style.color='#721c24'; }
  }
  ['e_corte','e_entregado'].forEach(id => document.getElementById(id)?.addEventListener('input', calcDiff));
  calcDiff();
})();
</script>
