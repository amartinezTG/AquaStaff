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
    <h1>Capturar Corte de Caja</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cortes.index') }}">Cortes</a></li>
        <li class="breadcrumb-item active">Capturar</li>
      </ol>
    </nav>
  </div>

  <section class="section">

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('cortes.store') }}" id="formCortes">
      @csrf

      <input type="hidden" name="facility_id" value="{{ $facility->facility_id }}">

      {{-- Cabecera: fecha y sucursal --}}
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row align-items-center g-3">
            <div class="col-auto">
              <label class="fw-semibold mb-0">Sucursal:</label>
              <span class="badge bg-primary ms-2 fs-6">{{ $facility->name }}</span>
            </div>
            <div class="col-auto">
              <label class="fw-semibold mb-0 me-2">Fecha:</label>
              <input type="date" name="fecha_corte" value="{{ $fechaCorte }}"
                     class="form-control form-control-sm d-inline-block" style="width:160px" required>
            </div>
            <div class="col-auto ms-auto">
              <span class="text-muted small">TC vigente: <strong>${{ number_format($tipoCambio,2) }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      {{-- Columnas por cajero --}}
      <div class="row g-3">
        @foreach($cajas as $caja)
          @php $corteExistente = $cortesExistentes->get($caja->id); @endphp
          <div class="col-lg-6">

            {{-- Badge de cajero --}}
            <div class="d-flex align-items-center mb-2 gap-2">
              <span class="badge bg-dark fs-6">{{ strtoupper($caja->nombre) }}</span>
              <span class="text-muted small">({{ $caja->codigo }})</span>
              @if($corteExistente)
                <span class="badge bg-warning text-dark ms-auto">Editando corte existente</span>
              @endif
            </div>

            {{-- VENTAS --}}
            <div class="card mb-2">
              <div class="card-header py-2 bg-primary text-white fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> Ventas
              </div>
              <div class="card-body p-3">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label small mb-1">Total Ventas (sistema)</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][total_ventas]"
                             value="{{ $corteExistente->total_ventas ?? '' }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small mb-1"># Pagos Tarjeta</label>
                    <input type="number" min="0"
                           name="cajas[{{ $caja->id }}][num_pagos_tarjeta]"
                           value="{{ $corteExistente->num_pagos_tarjeta ?? '' }}"
                           class="form-control form-control-sm" placeholder="0">
                  </div>
                  <div class="col-6">
                    <label class="form-label small mb-1">Importe Tarjeta (Prosepago)</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][importe_tarjeta]"
                             value="{{ $corteExistente->importe_tarjeta ?? '' }}"
                             class="form-control total-calc" data-caja="{{ $caja->id }}" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-4">
                    <label class="form-label small mb-1">Efectivo MXN</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][efectivo_mxn]"
                             value="{{ $corteExistente->efectivo_mxn ?? '' }}"
                             class="form-control total-calc" data-caja="{{ $caja->id }}" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-4">
                    <label class="form-label small mb-1">Efectivo DLLS (cant.)</label>
                    <input type="number" step="0.01" min="0"
                           name="cajas[{{ $caja->id }}][efectivo_dlls_cantidad]"
                           value="{{ $corteExistente->efectivo_dlls_cantidad ?? '' }}"
                           class="form-control form-control-sm dlls-cant" data-caja="{{ $caja->id }}" placeholder="0">
                  </div>
                  <div class="col-4">
                    <label class="form-label small mb-1">TC DLLS</label>
                    <input type="number" step="0.0001" min="0"
                           name="cajas[{{ $caja->id }}][efectivo_dlls_tc]"
                           value="{{ $corteExistente->efectivo_dlls_tc ?? $tipoCambio }}"
                           class="form-control form-control-sm dlls-tc" data-caja="{{ $caja->id }}">
                  </div>
                </div>
                {{-- Totales calculados --}}
                <div class="mt-2 p-2 bg-light rounded">
                  <div class="d-flex justify-content-between small">
                    <span>Total Efectivo:</span>
                    <strong class="txt-total-efectivo-{{ $caja->id }}">$0.00</strong>
                  </div>
                  <div class="d-flex justify-content-between small">
                    <span>Total de Venta:</span>
                    <strong class="txt-total-venta-{{ $caja->id }}">$0.00</strong>
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
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][dotacion]"
                             value="{{ $corteExistente->dotacion ?? '' }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small mb-1">Pagos Cancelados</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][pagos_cancelados]"
                             value="{{ $corteExistente->pagos_cancelados ?? 0 }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- DISPENSADORES --}}
            <div class="card mb-2">
              <div class="card-header py-2 bg-info text-white fw-semibold">
                <i class="bi bi-safe me-1"></i> Saldo en Dispensadores
              </div>
              <div class="card-body p-3">
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small mb-1">Saldo Final</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][saldo_inicial_dispensador]"
                             value="{{ $corteExistente->saldo_inicial_dispensador ?? '' }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small mb-1">Dotación Final</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][dotacion_final]"
                             value="{{ $corteExistente->dotacion_final ?? '' }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- CAMBIOS --}}
            <div class="card mb-2">
              <div class="card-header py-2 bg-secondary text-white fw-semibold">
                <i class="bi bi-arrow-left-right me-1"></i> Cambios Entregados
              </div>
              <div class="card-body p-3">
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small mb-1">Cambio Entregado</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][cambio_entregado]"
                             value="{{ $corteExistente->cambio_entregado ?? '' }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small mb-1">Cambio No Entregado</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][cambio_no_entregado]"
                             value="{{ $corteExistente->cambio_no_entregado ?? '' }}"
                             class="form-control" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label small mb-1">Referencia / Justificación</label>
                    <input type="text"
                           name="cajas[{{ $caja->id }}][referencia_cambio]"
                           value="{{ $corteExistente->referencia_cambio ?? '' }}"
                           class="form-control form-control-sm" placeholder="Ej: cambio entregado a cliente, devolucion...">
                  </div>
                </div>
              </div>
            </div>

            {{-- DENOMINACIONES MXN --}}
            <div class="card mb-2">
              <div class="card-header py-2 fw-semibold" style="background:#e8f5e9">
                <i class="bi bi-currency-exchange me-1"></i> Denominaciones Pesos MXN
              </div>
              <div class="card-body p-3">
                <div class="row g-1" style="font-size:.82rem">
                  @php
                    $denMxn = [];
                    if($corteExistente) {
                      $denMxn = $corteExistente->denominaciones->where('moneda','MXN')->keyBy('denominacion');
                    }
                    $billetesMxn = ['b500'=>'B. $500','b200'=>'B. $200','b100'=>'B. $100','b50'=>'B. $50','b20'=>'B. $20'];
                    $monedasMxn  = ['b10'=>'B. $10','b5'=>'B. $5','b2'=>'B. $2','b1'=>'B. $1','m10'=>'M. $10','m5'=>'M. $5','m2'=>'M. $2','m1'=>'M. $1'];
                  @endphp
                  <div class="col-12"><small class="text-muted fw-semibold">BILLETES</small></div>
                  @foreach($billetesMxn as $key => $label)
                    <div class="col-4">
                      <label class="form-label mb-0">{{ $label }}</label>
                      <input type="number" min="0"
                             name="cajas[{{ $caja->id }}][den_mxn][{{ $key }}]"
                             value="{{ $denMxn[$key]->cantidad ?? '' }}"
                             class="form-control form-control-sm" placeholder="0">
                    </div>
                  @endforeach
                  <div class="col-12 mt-1"><small class="text-muted fw-semibold">MONEDAS</small></div>
                  @foreach($monedasMxn as $key => $label)
                    <div class="col-3">
                      <label class="form-label mb-0">{{ $label }}</label>
                      <input type="number" min="0"
                             name="cajas[{{ $caja->id }}][den_mxn][{{ $key }}]"
                             value="{{ $denMxn[$key]->cantidad ?? '' }}"
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
                  @php
                    $denUsd = [];
                    if($corteExistente) {
                      $denUsd = $corteExistente->denominaciones->where('moneda','USD')->keyBy('denominacion');
                    }
                    $billetesUsd = ['usd_b50'=>'B. $50','usd_b20'=>'B. $20','usd_b10'=>'B. $10','usd_b5'=>'B. $5','usd_b2'=>'B. $2','usd_b1'=>'B. $1'];
                  @endphp
                  @foreach($billetesUsd as $key => $label)
                    <div class="col-4">
                      <label class="form-label mb-0">{{ $label }}</label>
                      <input type="number" min="0"
                             name="cajas[{{ $caja->id }}][den_usd][{{ $key }}]"
                             value="{{ $denUsd[$key]->cantidad ?? '' }}"
                             class="form-control form-control-sm" placeholder="0">
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            {{-- CIERRE --}}
            <div class="card mb-2 border-dark">
              <div class="card-header py-2 bg-dark text-white fw-semibold">
                <i class="bi bi-check2-circle me-1"></i> Cierre
              </div>
              <div class="card-body p-3">
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small mb-1">Corte Total Efectivo</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][corte_total_efectivo]"
                             value="{{ $corteExistente->corte_total_efectivo ?? '' }}"
                             class="form-control corte-efectivo" data-caja="{{ $caja->id }}" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small mb-1">Efectivo Entregado</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" min="0"
                             name="cajas[{{ $caja->id }}][efectivo_entregado]"
                             value="{{ $corteExistente->efectivo_entregado ?? '' }}"
                             class="form-control efectivo-entregado" data-caja="{{ $caja->id }}" placeholder="0.00">
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="p-2 rounded text-center fw-bold fs-6"
                         id="diferencia-badge-{{ $caja->id }}"
                         style="background:#f8f9fa; border: 2px solid #dee2e6">
                      Diferencia: <span id="txt-diferencia-{{ $caja->id }}">$0.00</span>
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label small mb-1">Observaciones</label>
                    <textarea name="cajas[{{ $caja->id }}][observaciones]"
                              class="form-control form-control-sm" rows="2"
                              placeholder="Notas opcionales...">{{ $corteExistente->observaciones ?? '' }}</textarea>
                  </div>
                </div>
              </div>
            </div>

          </div>{{-- col --}}
        @endforeach
      </div>{{-- row --}}

      <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
        <a href="{{ route('cortes.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary px-5">
          <i class="bi bi-save me-1"></i> Guardar Cortes
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
  @foreach($cajas as $caja)
  (function () {
    var cId = {{ $caja->id }};

    function calcular() {
      var tarjeta  = parseFloat(document.querySelector('[name="cajas['+cId+'][importe_tarjeta]"]')?.value) || 0;
      var mxn      = parseFloat(document.querySelector('[name="cajas['+cId+'][efectivo_mxn]"]')?.value)    || 0;
      var dlls     = parseFloat(document.querySelector('[name="cajas['+cId+'][efectivo_dlls_cantidad]"]')?.value) || 0;
      var tc       = parseFloat(document.querySelector('[name="cajas['+cId+'][efectivo_dlls_tc]"]')?.value) || 0;
      var efectivo = mxn + (dlls * tc);
      var venta    = efectivo + tarjeta;

      document.querySelector('.txt-total-efectivo-'+cId).textContent = '$' + efectivo.toFixed(2);
      document.querySelector('.txt-total-venta-'+cId).textContent    = '$' + venta.toFixed(2);
    }

    function calcularDiferencia() {
      var corte     = parseFloat(document.querySelector('[name="cajas['+cId+'][corte_total_efectivo]"]')?.value) || 0;
      var entregado = parseFloat(document.querySelector('[name="cajas['+cId+'][efectivo_entregado]"]')?.value)   || 0;
      var diff      = entregado - corte;
      var badge     = document.getElementById('diferencia-badge-'+cId);
      var txt       = document.getElementById('txt-diferencia-'+cId);
      txt.textContent = '$' + diff.toFixed(2);
      if (diff === 0) {
        badge.style.background = '#d4edda'; badge.style.borderColor = '#28a745'; badge.style.color = '#155724';
      } else if (diff > 0) {
        badge.style.background = '#fff3cd'; badge.style.borderColor = '#ffc107'; badge.style.color = '#856404';
      } else {
        badge.style.background = '#f8d7da'; badge.style.borderColor = '#dc3545'; badge.style.color = '#721c24';
      }
    }

    document.querySelectorAll('[name="cajas['+cId+'][importe_tarjeta]"], [name="cajas['+cId+'][efectivo_mxn]"], [name="cajas['+cId+'][efectivo_dlls_cantidad]"], [name="cajas['+cId+'][efectivo_dlls_tc]"]')
      .forEach(el => el.addEventListener('input', calcular));

    document.querySelectorAll('[name="cajas['+cId+'][corte_total_efectivo]"], [name="cajas['+cId+'][efectivo_entregado]"]')
      .forEach(el => el.addEventListener('input', calcularDiferencia));

    calcular();
    calcularDiferencia();
  })();
  @endforeach
})();
</script>
