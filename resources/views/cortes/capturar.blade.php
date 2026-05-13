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

    <form method="POST" action="{{ route('cortes.store') }}" id="formCorte">
      @csrf
      <input type="hidden" name="facility_id" value="{{ $facility->facility_id }}">

      {{-- ── CABECERA ── --}}
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row align-items-end g-3">

            <div class="col-auto">
              <span class="fw-semibold text-muted small d-block mb-1">Sucursal</span>
              <span class="badge bg-primary fs-6">{{ $facility->name }}</span>
            </div>

            <div class="col-auto">
              <label class="fw-semibold text-muted small d-block mb-1" for="inp_fecha">Fecha</label>
              <input type="date" name="fecha_corte" id="inp_fecha"
                     value="{{ $fechaCorte }}"
                     class="form-control form-control-sm" style="width:160px" required>
            </div>

            <div class="col-auto">
              <label class="fw-semibold text-muted small d-block mb-1" for="sel_caja">Cajero</label>
              <select name="caja_id" id="sel_caja" class="form-select form-select-sm" style="min-width:180px">
                @foreach($cajas as $caja)
                  <option value="{{ $caja->id }}"
                    {{ $caja->id == $cajaSeleccionada->id ? 'selected' : '' }}>
                    {{ $caja->nombre }} ({{ $caja->codigo }})
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Indicador de corte existente --}}
            <div class="col-auto">
              <div id="badge_existente" class="d-none">
                <span class="badge bg-warning text-dark">
                  <i class="bi bi-pencil-square me-1"></i>
                  Editando corte guardado el <span id="txt_fecha_guardado"></span>
                </span>
              </div>
            </div>

            <div class="col-auto ms-auto text-end">
              <span class="text-muted small d-block">TC vigente</span>
              <strong>${{ number_format($tipoCambio,2) }}</strong>
            </div>

          </div>
        </div>
      </div>

      {{-- ── CUERPO ── --}}
      <div class="row g-3">

        {{-- COLUMNA IZQUIERDA --}}
        <div class="col-lg-6">

          {{-- VENTAS --}}
          <div class="card mb-3">
            <div class="card-header py-2 bg-primary text-white fw-semibold">
              <i class="bi bi-cash-stack me-1"></i> Ventas
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label small mb-1">Total Ventas (del sistema)</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="total_ventas" id="f_total_ventas"
                           value="{{ old('total_ventas', $corteExistente->total_ventas ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1"># Pagos Tarjeta</label>
                  <input type="number" min="0" name="num_pagos_tarjeta" id="f_num_tarjeta"
                         value="{{ old('num_pagos_tarjeta', $corteExistente->num_pagos_tarjeta ?? '') }}"
                         class="form-control form-control-sm" placeholder="0">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Importe Tarjeta (Prosepago)</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="importe_tarjeta" id="f_tarjeta"
                           value="{{ old('importe_tarjeta', $corteExistente->importe_tarjeta ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label small mb-1">Efectivo MXN</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="efectivo_mxn" id="f_mxn"
                           value="{{ old('efectivo_mxn', $corteExistente->efectivo_mxn ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label small mb-1">Efectivo DLLS</label>
                  <input type="number" step="0.01" min="0" name="efectivo_dlls_cantidad" id="f_dlls"
                         value="{{ old('efectivo_dlls_cantidad', $corteExistente->efectivo_dlls_cantidad ?? '') }}"
                         class="form-control form-control-sm" placeholder="0">
                </div>
                <div class="col-4">
                  <label class="form-label small mb-1">TC DLLS</label>
                  <input type="number" step="0.0001" min="0" name="efectivo_dlls_tc" id="f_tc"
                         value="{{ old('efectivo_dlls_tc', $corteExistente->efectivo_dlls_tc ?? $tipoCambio) }}"
                         class="form-control form-control-sm">
                </div>
              </div>
              <div class="mt-3 p-2 bg-light rounded" style="font-size:.85rem">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Total Efectivo:</span>
                  <strong id="txt_efectivo">$0.00</strong>
                </div>
                <div class="d-flex justify-content-between mt-1 fw-bold">
                  <span>Total de Venta:</span>
                  <span id="txt_venta">$0.00</span>
                </div>
              </div>
            </div>
          </div>

          {{-- EGRESOS --}}
          <div class="card mb-3">
            <div class="card-header py-2 bg-warning fw-semibold">
              <i class="bi bi-arrow-down-circle me-1"></i> Egresos
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Dotación</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="dotacion" id="f_dotacion"
                           value="{{ old('dotacion', $corteExistente->dotacion ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Pagos Cancelados</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="pagos_cancelados" id="f_cancelados"
                           value="{{ old('pagos_cancelados', $corteExistente->pagos_cancelados ?? 0) }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- DISPENSADORES --}}
          <div class="card mb-3">
            <div class="card-header py-2 bg-info text-white fw-semibold">
              <i class="bi bi-safe me-1"></i> Saldo en Dispensadores
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Saldo Final</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="saldo_inicial_dispensador" id="f_saldo_disp"
                           value="{{ old('saldo_inicial_dispensador', $corteExistente->saldo_inicial_dispensador ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Dotación Final</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="dotacion_final" id="f_dot_final"
                           value="{{ old('dotacion_final', $corteExistente->dotacion_final ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- CAMBIOS --}}
          <div class="card mb-3">
            <div class="card-header py-2 bg-secondary text-white fw-semibold">
              <i class="bi bi-arrow-left-right me-1"></i> Cambios Entregados
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small mb-1">Cambio Entregado</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="cambio_entregado" id="f_cambio_e"
                           value="{{ old('cambio_entregado', $corteExistente->cambio_entregado ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Cambio No Entregado</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="cambio_no_entregado" id="f_cambio_ne"
                           value="{{ old('cambio_no_entregado', $corteExistente->cambio_no_entregado ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label small mb-1">Referencia / Justificación</label>
                  <input type="text" name="referencia_cambio" id="f_referencia"
                         value="{{ old('referencia_cambio', $corteExistente->referencia_cambio ?? '') }}"
                         class="form-control form-control-sm"
                         placeholder="Ej: cambio entregado a cliente, devolución...">
                </div>
              </div>
            </div>
          </div>

        </div>{{-- col izq --}}

        {{-- COLUMNA DERECHA --}}
        <div class="col-lg-6">

          {{-- DENOMINACIONES MXN --}}
          <div class="card mb-3">
            <div class="card-header py-2 fw-semibold" style="background:#e8f5e9">
              <i class="bi bi-currency-exchange me-1"></i> Denominaciones MXN
            </div>
            <div class="card-body p-3">
              <div class="row g-2" style="font-size:.83rem">
                <div class="col-12"><small class="text-muted fw-semibold">BILLETES</small></div>
                @foreach(['b500'=>'B. $500','b200'=>'B. $200','b100'=>'B. $100','b50'=>'B. $50','b20'=>'B. $20','b10'=>'B. $10','b5'=>'B. $5','b2'=>'B. $2','b1'=>'B. $1'] as $k => $lbl)
                  <div class="col-4">
                    <label class="form-label mb-0">{{ $lbl }}</label>
                    <input type="number" min="0" name="den_mxn[{{ $k }}]" id="f_mxn_{{ $k }}"
                           value="{{ old('den_mxn.'.$k, $denMxn[$k]->cantidad ?? '') }}"
                           class="form-control form-control-sm" placeholder="0">
                  </div>
                @endforeach
                <div class="col-12 mt-1"><small class="text-muted fw-semibold">MONEDAS</small></div>
                @foreach(['m10'=>'M. $10','m5'=>'M. $5','m2'=>'M. $2','m1'=>'M. $1'] as $k => $lbl)
                  <div class="col-3">
                    <label class="form-label mb-0">{{ $lbl }}</label>
                    <input type="number" min="0" name="den_mxn[{{ $k }}]" id="f_mxn_{{ $k }}"
                           value="{{ old('den_mxn.'.$k, $denMxn[$k]->cantidad ?? '') }}"
                           class="form-control form-control-sm" placeholder="0">
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          {{-- DENOMINACIONES USD --}}
          <div class="card mb-3">
            <div class="card-header py-2 fw-semibold" style="background:#e3f2fd">
              <i class="bi bi-currency-dollar me-1"></i> Denominaciones USD
            </div>
            <div class="card-body p-3">
              <div class="row g-2" style="font-size:.83rem">
                @foreach(['usd_b50'=>'B. $50','usd_b20'=>'B. $20','usd_b10'=>'B. $10','usd_b5'=>'B. $5','usd_b2'=>'B. $2','usd_b1'=>'B. $1'] as $k => $lbl)
                  <div class="col-4">
                    <label class="form-label mb-0">{{ $lbl }}</label>
                    <input type="number" min="0" name="den_usd[{{ $k }}]" id="f_usd_{{ $k }}"
                           value="{{ old('den_usd.'.$k, $denUsd[$k]->cantidad ?? '') }}"
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
                    <input type="number" step="0.01" min="0" name="corte_total_efectivo" id="f_corte"
                           value="{{ old('corte_total_efectivo', $corteExistente->corte_total_efectivo ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Efectivo Entregado</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" name="efectivo_entregado" id="f_entregado"
                           value="{{ old('efectivo_entregado', $corteExistente->efectivo_entregado ?? '') }}"
                           class="form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-12">
                  <div class="p-2 rounded text-center fw-bold fs-6" id="diff_badge"
                       style="background:#f8f9fa; border:2px solid #dee2e6">
                    Diferencia: <span id="txt_diff">$0.00</span>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label small mb-1">Observaciones</label>
                  <textarea name="observaciones" id="f_observaciones"
                            class="form-control form-control-sm" rows="2"
                            placeholder="Notas opcionales...">{{ old('observaciones', $corteExistente->observaciones ?? '') }}</textarea>
                </div>
              </div>
            </div>
          </div>

        </div>{{-- col der --}}
      </div>{{-- row --}}

      <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
        <a href="{{ route('cortes.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary px-5">
          <i class="bi bi-save me-1"></i> Guardar Corte
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
  var TC_DEFAULT  = {{ $tipoCambio }};
  var URL_DATOS   = "{{ route('cortes.datos_cajero') }}";
  var CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  // ── Helpers ──────────────────────────────────────────────────────────────
  function fmt(n) { return '$' + parseFloat(n || 0).toFixed(2); }

  function set(id, val) {
    var el = document.getElementById(id);
    if (el) el.value = (val !== null && val !== undefined) ? val : '';
  }

  function setDen(prefix, obj) {
    // obj puede ser null (sin corte) o un objeto { b500: 2, b200: 1, ... }
    var keys = prefix === 'mxn'
      ? ['b500','b200','b100','b50','b20','b10','b5','b2','b1','m10','m5','m2','m1']
      : ['usd_b50','usd_b20','usd_b10','usd_b5','usd_b2','usd_b1'];

    keys.forEach(function(k) {
      var el = document.getElementById('f_' + prefix + '_' + k);
      if (el) el.value = (obj && obj[k]) ? obj[k] : '';
    });
  }

  // ── Cálculos en vivo ─────────────────────────────────────────────────────
  function calcular() {
    var mxn      = parseFloat(document.getElementById('f_mxn')?.value)     || 0;
    var dlls     = parseFloat(document.getElementById('f_dlls')?.value)    || 0;
    var tc       = parseFloat(document.getElementById('f_tc')?.value)      || 0;
    var tarjeta  = parseFloat(document.getElementById('f_tarjeta')?.value) || 0;
    var efectivo = mxn + dlls * tc;
    document.getElementById('txt_efectivo').textContent = fmt(efectivo);
    document.getElementById('txt_venta').textContent    = fmt(efectivo + tarjeta);
  }

  function calcularDiff() {
    var corte     = parseFloat(document.getElementById('f_corte')?.value)     || 0;
    var entregado = parseFloat(document.getElementById('f_entregado')?.value) || 0;
    var diff      = entregado - corte;
    var badge     = document.getElementById('diff_badge');
    document.getElementById('txt_diff').textContent = fmt(diff);
    if (diff === 0) {
      badge.style.cssText = 'background:#d4edda;border:2px solid #28a745;color:#155724';
    } else if (diff > 0) {
      badge.style.cssText = 'background:#fff3cd;border:2px solid #ffc107;color:#856404';
    } else {
      badge.style.cssText = 'background:#f8d7da;border:2px solid #dc3545;color:#721c24';
    }
    badge.style.padding = '.5rem'; badge.style.borderRadius = '.375rem';
    badge.style.textAlign = 'center'; badge.style.fontWeight = 'bold';
  }

  ['f_mxn','f_dlls','f_tc','f_tarjeta'].forEach(function(id) {
    document.getElementById(id)?.addEventListener('input', calcular);
  });
  ['f_corte','f_entregado'].forEach(function(id) {
    document.getElementById(id)?.addEventListener('input', calcularDiff);
  });

  // ── Cargar datos del cajero seleccionado ─────────────────────────────────
  function cargarCajero() {
    var cajaId = document.getElementById('sel_caja').value;
    var fecha  = document.getElementById('inp_fecha').value;

    if (!cajaId || !fecha) return;

    fetch(URL_DATOS + '?caja_id=' + cajaId + '&fecha_corte=' + fecha, {
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      var badge = document.getElementById('badge_existente');
      var txt   = document.getElementById('txt_fecha_guardado');

      if (d.existe) {
        // Llenar todos los campos con datos guardados
        set('f_total_ventas',       d.total_ventas);
        set('f_num_tarjeta',        d.num_pagos_tarjeta);
        set('f_tarjeta',            d.importe_tarjeta);
        set('f_mxn',                d.efectivo_mxn);
        set('f_dlls',               d.efectivo_dlls_cantidad);
        set('f_tc',                 d.efectivo_dlls_tc);
        set('f_dotacion',           d.dotacion);
        set('f_cancelados',         d.pagos_cancelados);
        set('f_saldo_disp',         d.saldo_inicial_dispensador);
        set('f_dot_final',          d.dotacion_final);
        set('f_cambio_e',           d.cambio_entregado);
        set('f_cambio_ne',          d.cambio_no_entregado);
        set('f_referencia',         d.referencia_cambio);
        set('f_corte',              d.corte_total_efectivo);
        set('f_entregado',          d.efectivo_entregado);
        set('f_observaciones',      d.observaciones);
        setDen('mxn', d.den_mxn);
        setDen('usd', d.den_usd);
        txt.textContent = d.updated_at;
        badge.classList.remove('d-none');
      } else {
        // Limpiar el formulario
        var ids = ['f_total_ventas','f_num_tarjeta','f_tarjeta','f_mxn','f_dlls',
                   'f_dotacion','f_cancelados','f_saldo_disp','f_dot_final',
                   'f_cambio_e','f_cambio_ne','f_referencia','f_corte','f_entregado','f_observaciones'];
        ids.forEach(function(id) { set(id, ''); });
        set('f_tc', TC_DEFAULT);
        setDen('mxn', null);
        setDen('usd', null);
        badge.classList.add('d-none');
      }

      calcular();
      calcularDiff();
    });
  }

  // Disparar al cambiar cajero o fecha
  document.getElementById('sel_caja').addEventListener('change', cargarCajero);
  document.getElementById('inp_fecha').addEventListener('change', cargarCajero);

  // Calcular al cargar con valores existentes
  calcular();
  calcularDiff();

})();
</script>
