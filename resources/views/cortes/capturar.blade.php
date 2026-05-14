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

      {{-- Contenedor centrado, max 640px como el Excel --}}
      <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-7 col-xl-6">

        {{-- ══ CABECERA ══ --}}
        <div class="card mb-2">
          <div class="card-body py-3 px-4">

            <div class="text-center mb-3">
              <div class="fw-bold fs-5 text-uppercase">Corte de Caja</div>
              <div class="text-muted small">{{ $facility->name }}</div>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-6">
                <label class="form-label small mb-1 fw-semibold">Fecha</label>
                <input type="date" name="fecha_corte" id="inp_fecha"
                       value="{{ $fechaCorte }}"
                       class="form-control form-control-sm" required>
              </div>
              <div class="col-6">
                <label class="form-label small mb-1 fw-semibold">Cajero</label>
                <select name="caja_id" id="sel_caja" class="form-select form-select-sm">
                  @foreach($cajas as $caja)
                    <option value="{{ $caja->id }}"
                      {{ $caja->id == $cajaSeleccionada->id ? 'selected' : '' }}>
                      {{ $caja->nombre }} — {{ $caja->codigo }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <div id="badge_existente" class="{{ $corteExistente ? '' : 'd-none' }}">
                  <div class="alert alert-warning py-1 px-2 mb-0 small">
                    <i class="bi bi-pencil-square me-1"></i>
                    Editando corte guardado el <strong id="txt_fecha_guardado">{{ $corteExistente?->updated_at->format('d/m/Y H:i') }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ══ VENTAS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 bg-primary bg-opacity-10 border-bottom">
            <span class="fw-bold text-primary small text-uppercase">
              <i class="bi bi-cash-stack me-1"></i>Ventas
            </span>
          </div>
          <div class="card-body px-4 py-3">

            {{-- Fila 5: VENTAS (total del sistema) --}}
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Total Ventas (sistema)</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="total_ventas" id="f_total_ventas"
                         value="{{ old('total_ventas', $corteExistente->total_ventas ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>

            {{-- Fila 7-9: Tarjetas --}}
            <div class="mt-2 mb-1 text-muted" style="font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; font-weight:600">
              Desglose por Tipo de Pago
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-4 text-muted small">Tarjetas</div>
              <div class="col-4 pe-1">
                <input type="number" min="0" name="num_pagos_tarjeta" id="f_num_tarjeta"
                       value="{{ old('num_pagos_tarjeta', $corteExistente->num_pagos_tarjeta ?? '') }}"
                       class="form-control form-control-sm text-end" placeholder="# pagos">
              </div>
              <div class="col-4">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="importe_tarjeta" id="f_tarjeta"
                         value="{{ old('importe_tarjeta', $corteExistente->importe_tarjeta ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1">
              <div class="col-7 text-muted small ps-2">Total Prosepago</div>
              <div class="col-5 text-end small fw-semibold pe-1" id="txt_prosepago">$0.00</div>
            </div>

            {{-- Fila 10-13: Efectivo --}}
            <div class="row g-0 align-items-center border-top border-bottom py-2 mt-1">
              <div class="col-4 text-muted small">Efectivo MXN</div>
              <div class="col-4 pe-1">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="efectivo_mxn" id="f_mxn"
                         value="{{ old('efectivo_mxn', $corteExistente->efectivo_mxn ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
              <div class="col-4">
                <div class="input-group input-group-sm" title="Importe MXN">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control text-end bg-light" id="txt_mxn_importe"
                         value="" readonly tabindex="-1" placeholder="—">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-4 text-muted small">Efectivo DLLS</div>
              <div class="col-2 pe-1">
                <input type="number" step="0.01" min="0" name="efectivo_dlls_cantidad" id="f_dlls"
                       value="{{ old('efectivo_dlls_cantidad', $corteExistente->efectivo_dlls_cantidad ?? '') }}"
                       class="form-control form-control-sm text-end" placeholder="cant.">
              </div>
              <div class="col-2 pe-1">
                <input type="number" step="0.0001" min="0" name="efectivo_dlls_tc" id="f_tc"
                       value="{{ old('efectivo_dlls_tc', $corteExistente->efectivo_dlls_tc ?? $tipoCambio) }}"
                       class="form-control form-control-sm text-end" placeholder="TC">
              </div>
              <div class="col-4">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control text-end bg-light" id="txt_dlls_importe"
                         value="" readonly tabindex="-1" placeholder="—">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1 border-bottom">
              <div class="col-7 text-muted small ps-2">Total Efectivo</div>
              <div class="col-5 text-end small fw-semibold pe-1" id="txt_efectivo">$0.00</div>
            </div>
            <div class="row g-0 align-items-center py-2 bg-light rounded mt-1">
              <div class="col-7 fw-bold small ps-2">TOTAL DE VENTA</div>
              <div class="col-5 text-end fw-bold small pe-1" id="txt_venta">$0.00</div>
            </div>

          </div>
        </div>

        {{-- ══ EGRESOS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 bg-warning bg-opacity-10 border-bottom">
            <span class="fw-bold text-warning small text-uppercase">
              <i class="bi bi-arrow-down-circle me-1"></i>Desglose de Egresos
            </span>
          </div>
          <div class="card-body px-4 py-3">

            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Dotación</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="dotacion" id="f_dotacion"
                         value="{{ old('dotacion', $corteExistente->dotacion ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Pagos Cancelados</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="pagos_cancelados" id="f_cancelados"
                         value="{{ old('pagos_cancelados', $corteExistente->pagos_cancelados ?? 0) }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1">
              <div class="col-7 fw-bold small ps-2">Total de Egresos</div>
              <div class="col-5 text-end fw-bold small pe-1" id="txt_egresos">$0.00</div>
            </div>

          </div>
        </div>

        {{-- ══ SALDO EN DISPENSADORES ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 bg-info bg-opacity-10 border-bottom">
            <span class="fw-bold text-info small text-uppercase">
              <i class="bi bi-safe me-1"></i>Saldo en Dispensadores
            </span> 
          </div>
          <div class="card-body px-4 py-3">

            <div class="row g-0 align-items-center py-1 mb-1" style="font-size:.75rem; color:#666">
              <div class="col-4"></div>
              <div class="col-4 text-center">Saldo Final</div>
              <div class="col-4 text-center">Dotación Final</div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-4 text-muted small">Saldo Dispensador</div>
              <div class="col-4 pe-1">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="saldo_inicial_dispensador" id="f_saldo_disp"
                         value="{{ old('saldo_inicial_dispensador', $corteExistente->saldo_inicial_dispensador ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
              <div class="col-4">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="dotacion_final" id="f_dot_final"
                         value="{{ old('dotacion_final', $corteExistente->dotacion_final ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1">
              <div class="col-7 fw-bold small ps-2">Saldo en Dispensadores</div>
              <div class="col-5 text-end fw-bold small pe-1" id="txt_dispensador">$0.00</div>
            </div>

          </div>
        </div>

        {{-- ══ CAMBIOS ENTREGADOS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 bg-secondary bg-opacity-10 border-bottom">
            <span class="fw-bold text-secondary small text-uppercase">
              <i class="bi bi-arrow-left-right me-1"></i>Cambios Entregados
            </span>
          </div>
          <div class="card-body px-4 py-3">

            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Cambio Entregado</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="cambio_entregado" id="f_cambio_e"
                         value="{{ old('cambio_entregado', $corteExistente->cambio_entregado ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Cambio No Entregado</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="cambio_no_entregado" id="f_cambio_ne"
                         value="{{ old('cambio_no_entregado', $corteExistente->cambio_no_entregado ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-2">
              <div class="col-4 text-muted small">Referencia</div>
              <div class="col-8">
                <input type="text" name="referencia_cambio" id="f_referencia"
                       value="{{ old('referencia_cambio', $corteExistente->referencia_cambio ?? '') }}"
                       class="form-control form-control-sm"
                       placeholder="Justificación del cambio...">
              </div>
            </div>

          </div>
        </div>

        {{-- ══ DENOMINACIONES MXN ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#f0fdf4">
            <span class="fw-bold small text-uppercase" style="color:#166534">
              <i class="bi bi-currency-exchange me-1"></i>Denominaciones Pesos MXN
            </span>
          </div>
          <div class="card-body px-4 py-3">

            {{-- Encabezado de columnas --}}
            <div class="row g-0 py-1 mb-1" style="font-size:.72rem; color:#888; font-weight:600; text-transform:uppercase">
              <div class="col-5">Denominación</div>
              <div class="col-3 text-center">Cantidad</div>
              <div class="col-4 text-end">Monto</div>
            </div>

            <div class="fw-semibold text-muted mb-1" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em">Billetes</div>
            @foreach(['b500'=>['B. $500',500],'b200'=>['B. $200',200],'b100'=>['B. $100',100],'b50'=>['B. $50',50],'b20'=>['B. $20',20],'b10'=>['B. $10',10],'b5'=>['B. $5',5],'b2'=>['B. $2',2],'b1'=>['B. $1',1]] as $k => [$lbl,$val])
            <div class="row g-0 align-items-center border-bottom py-1" data-den-mxn="{{ $k }}" data-val="{{ $val }}">
              <div class="col-5 small">{{ $lbl }}</div>
              <div class="col-3 px-1">
                <input type="number" min="0" name="den_mxn[{{ $k }}]" id="f_mxn_{{ $k }}"
                       value="{{ old('den_mxn.'.$k, $denMxn[$k]->cantidad ?? '') }}"
                       class="form-control form-control-sm text-end den-mxn-input" placeholder="0">
              </div>
              <div class="col-4 text-end small text-muted monto-den" id="mto_mxn_{{ $k }}">$0.00</div>
            </div>
            @endforeach

            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1 mb-2">
              <div class="col-8 fw-semibold small ps-1">Total Billetes M.N</div>
              <div class="col-4 text-end fw-semibold small pe-1" id="txt_total_billetes">$0.00</div>
            </div>

            <div class="fw-semibold text-muted mb-1 mt-2" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em">Monedas</div>
            @foreach(['m10'=>['M. $10',10],'m5'=>['M. $5',5],'m2'=>['M. $2',2],'m1'=>['M. $1',1]] as $k => [$lbl,$val])
            <div class="row g-0 align-items-center border-bottom py-1" data-den-mxn="{{ $k }}" data-val="{{ $val }}">
              <div class="col-5 small">{{ $lbl }}</div>
              <div class="col-3 px-1">
                <input type="number" min="0" name="den_mxn[{{ $k }}]" id="f_mxn_{{ $k }}"
                       value="{{ old('den_mxn.'.$k, $denMxn[$k]->cantidad ?? '') }}"
                       class="form-control form-control-sm text-end den-mxn-input" placeholder="0">
              </div>
              <div class="col-4 text-end small text-muted monto-den" id="mto_mxn_{{ $k }}">$0.00</div>
            </div>
            @endforeach

            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1">
              <div class="col-8 fw-semibold small ps-1">Total Monedas M.N</div>
              <div class="col-4 text-end fw-semibold small pe-1" id="txt_total_monedas">$0.00</div>
            </div>

          </div>
        </div>

        {{-- ══ DENOMINACIONES USD ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#eff6ff">
            <span class="fw-bold small text-uppercase" style="color:#1e40af">
              <i class="bi bi-currency-dollar me-1"></i>Denominaciones USD
              <span class="fw-normal text-muted ms-2">(TC ${{ number_format($tipoCambio,2) }})</span>
            </span>
          </div>
          <div class="card-body px-4 py-3">

            <div class="row g-0 py-1 mb-1" style="font-size:.72rem; color:#888; font-weight:600; text-transform:uppercase">
              <div class="col-3">Denominación</div>
              <div class="col-3 text-center">TC</div>
              <div class="col-3 text-center">Cantidad</div>
              <div class="col-3 text-end">Monto</div>
            </div>

            @foreach(['usd_b50'=>['B. $50',50],'usd_b20'=>['B. $20',20],'usd_b10'=>['B. $10',10],'usd_b5'=>['B. $5',5],'usd_b2'=>['B. $2',2],'usd_b1'=>['B. $1',1]] as $k => [$lbl,$val])
            <div class="row g-0 align-items-center border-bottom py-1" data-val="{{ $val }}">
              <div class="col-3 small">{{ $lbl }}</div>
              <div class="col-3 text-center small text-muted" style="font-size:.8rem">${{ number_format($tipoCambio,0) }}</div>
              <div class="col-3 px-1">
                <input type="number" min="0" name="den_usd[{{ $k }}]" id="f_usd_{{ $k }}"
                       value="{{ old('den_usd.'.$k, $denUsd[$k]->cantidad ?? '') }}"
                       class="form-control form-control-sm text-end den-usd-input" placeholder="0"
                       data-val="{{ $val }}">
              </div>
              <div class="col-3 text-end small text-muted" id="mto_usd_{{ $k }}">$0.00</div>
            </div>
            @endforeach

            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1">
              <div class="col-8 fw-semibold small ps-1">Total USD</div>
              <div class="col-4 text-end fw-semibold small pe-1" id="txt_total_usd">$0.00</div>
            </div>

          </div>
        </div>

        {{-- ══ CIERRE ══ --}}
        <div class="card mb-2 border-dark border-opacity-25">
          <div class="card-header py-2 px-4 bg-dark text-white">
            <span class="fw-bold small text-uppercase">
              <i class="bi bi-check2-circle me-1"></i>Corte Total en Efectivo
            </span>
          </div>
          <div class="card-body px-4 py-3">

            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 fw-semibold small">Corte Total Efectivo</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="corte_total_efectivo" id="f_corte"
                         value="{{ old('corte_total_efectivo', $corteExistente->corte_total_efectivo ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 fw-semibold small">Efectivo Entregado</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="efectivo_entregado" id="f_entregado"
                         value="{{ old('efectivo_entregado', $corteExistente->efectivo_entregado ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>

            {{-- Diferencia --}}
            <div class="row g-0 align-items-center py-2 px-2 mt-2 rounded fw-bold fs-6" id="diff_badge"
                 style="background:#f8f9fa; border:2px solid #dee2e6">
              <div class="col-6">Diferencia</div>
              <div class="col-6 text-end" id="txt_diff">$0.00</div>
            </div>

            {{-- Observaciones --}}
            <div class="mt-3">
              <label class="form-label small fw-semibold mb-1">Observaciones</label>
              <textarea name="observaciones" id="f_observaciones"
                        class="form-control form-control-sm" rows="2"
                        placeholder="Notas opcionales...">{{ old('observaciones', $corteExistente->observaciones ?? '') }}</textarea>
            </div>

          </div>
        </div>

        {{-- Espaciado para que la barra fija no tape el último campo --}}
        <div style="height:72px"></div>

      </div>{{-- col --}}
      </div>{{-- row --}}

    </form>

    {{-- Barra fija inferior con botón siempre visible --}}
    <div style="position:fixed;bottom:0;left:0;right:0;z-index:1040;
                background:#fff;border-top:1px solid #dee2e6;
                padding:10px 20px;box-shadow:0 -2px 8px rgba(0,0,0,.08)">
      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('cortes.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" form="formCorte" class="btn btn-primary px-5">
          <i class="bi bi-save me-1"></i> Guardar Corte
        </button>
      </div>
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

<script>
(function () {
  var TC  = {{ $tipoCambio }};
  var URL_JSON = "{{ route('cortes.datos_cajero') }}";

  function fmt(n) { return '$' + parseFloat(n || 0).toFixed(2); }
  function num(id) { return parseFloat(document.getElementById(id)?.value) || 0; }
  function set(id, val) { var el=document.getElementById(id); if(el) el.value = (val!=null && val!==undefined) ? val : ''; }

  // ── Recalcular todos los totales ──────────────────────────────────────────
  function recalc() {
    var tarjeta  = num('f_tarjeta');
    var mxn      = num('f_mxn');
    var dlls     = num('f_dlls');
    var tc       = num('f_tc') || TC;
    var dotacion = num('f_dotacion');
    var cancelados = num('f_cancelados');
    var saldo_disp = num('f_saldo_disp');
    var dot_final  = num('f_dot_final');

    var dlls_mxn = dlls * tc;
    var efectivo = mxn + dlls_mxn;
    var venta    = tarjeta + efectivo;
    var egresos  = dotacion + cancelados;
    var disp     = saldo_disp + dotacion;

    document.getElementById('txt_prosepago').textContent  = fmt(tarjeta);
    document.getElementById('txt_mxn_importe').value      = fmt(mxn);
    document.getElementById('txt_dlls_importe').value     = fmt(dlls_mxn);
    document.getElementById('txt_efectivo').textContent   = fmt(efectivo);
    document.getElementById('txt_venta').textContent      = fmt(venta);
    document.getElementById('txt_egresos').textContent    = fmt(egresos);
    document.getElementById('txt_dispensador').textContent = fmt(disp);

    // Denominaciones MXN
    var totalBilletes = 0, totalMonedas = 0;
    document.querySelectorAll('.den-mxn-input').forEach(function(inp) {
      var row  = inp.closest('[data-den-mxn]');
      var val  = parseFloat(row?.dataset.val || 0);
      var cant = parseFloat(inp.value) || 0;
      var monto = cant * val;
      var key  = row?.dataset.denMxn || inp.name.match(/\[(\w+)\]/)?.[1];
      var mtoEl = document.getElementById('mto_mxn_' + key);
      if (mtoEl) mtoEl.textContent = fmt(monto);
      if (['b500','b200','b100','b50','b20','b10','b5','b2','b1'].includes(key)) totalBilletes += monto;
      else totalMonedas += monto;
    });
    document.getElementById('txt_total_billetes').textContent = fmt(totalBilletes);
    document.getElementById('txt_total_monedas').textContent  = fmt(totalMonedas);

    // Denominaciones USD
    var totalUsd = 0;
    document.querySelectorAll('.den-usd-input').forEach(function(inp) {
      var val  = parseFloat(inp.dataset.val || 0);
      var cant = parseFloat(inp.value) || 0;
      var monto = cant * val * tc;
      var key  = inp.name.match(/\[(\w+)\]/)?.[1];
      var mtoEl = document.getElementById('mto_usd_' + key);
      if (mtoEl) mtoEl.textContent = fmt(monto);
      totalUsd += monto;
    });
    document.getElementById('txt_total_usd').textContent = fmt(totalUsd);

    calcDiff();
  }

  function calcDiff() {
    var corte     = num('f_corte');
    var entregado = num('f_entregado');
    var diff      = entregado - corte;
    var badge     = document.getElementById('diff_badge');
    document.getElementById('txt_diff').textContent = fmt(diff);
    if (diff === 0)    { badge.style.background='#d4edda'; badge.style.borderColor='#28a745'; badge.style.color='#155724'; }
    else if (diff > 0) { badge.style.background='#fff3cd'; badge.style.borderColor='#ffc107'; badge.style.color='#856404'; }
    else               { badge.style.background='#f8d7da'; badge.style.borderColor='#dc3545'; badge.style.color='#721c24'; }
  }

  // ── Listeners ─────────────────────────────────────────────────────────────
  ['f_tarjeta','f_mxn','f_dlls','f_tc','f_dotacion','f_cancelados',
   'f_saldo_disp','f_dot_final','f_corte','f_entregado'].forEach(function(id) {
    document.getElementById(id)?.addEventListener('input', recalc);
  });
  document.querySelectorAll('.den-mxn-input, .den-usd-input').forEach(function(el) {
    el.addEventListener('input', recalc);
  });

  // ── Cargar datos del cajero via AJAX ──────────────────────────────────────
  function setDen(prefix, obj) {
    var keys = prefix === 'mxn'
      ? ['b500','b200','b100','b50','b20','b10','b5','b2','b1','m10','m5','m2','m1']
      : ['usd_b50','usd_b20','usd_b10','usd_b5','usd_b2','usd_b1'];
    keys.forEach(function(k) {
      var el = document.getElementById('f_' + prefix + '_' + k);
      if (el) el.value = (obj && obj[k]) ? obj[k] : '';
    });
  }

  function cargarCajero() {
    var cajaId = document.getElementById('sel_caja').value;
    var fecha  = document.getElementById('inp_fecha').value;
    if (!cajaId || !fecha) return;

    fetch(URL_JSON + '?caja_id=' + cajaId + '&fecha_corte=' + fecha, {
      headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      var badge = document.getElementById('badge_existente');
      if (d.existe) {
        set('f_total_ventas', d.total_ventas);
        set('f_num_tarjeta',  d.num_pagos_tarjeta);
        set('f_tarjeta',      d.importe_tarjeta);
        set('f_mxn',          d.efectivo_mxn);
        set('f_dlls',         d.efectivo_dlls_cantidad);
        set('f_tc',           d.efectivo_dlls_tc);
        set('f_dotacion',     d.dotacion);
        set('f_cancelados',   d.pagos_cancelados);
        set('f_saldo_disp',   d.saldo_inicial_dispensador);
        set('f_dot_final',    d.dotacion_final);
        set('f_cambio_e',     d.cambio_entregado);
        set('f_cambio_ne',    d.cambio_no_entregado);
        set('f_referencia',   d.referencia_cambio);
        set('f_corte',        d.corte_total_efectivo);
        set('f_entregado',    d.efectivo_entregado);
        set('f_observaciones',d.observaciones);
        setDen('mxn', d.den_mxn);
        setDen('usd', d.den_usd);
        document.getElementById('txt_fecha_guardado').textContent = d.updated_at;
        badge.classList.remove('d-none');
      } else {
        ['f_total_ventas','f_num_tarjeta','f_tarjeta','f_mxn','f_dlls',
         'f_dotacion','f_cancelados','f_saldo_disp','f_dot_final',
         'f_cambio_e','f_cambio_ne','f_referencia','f_corte','f_entregado','f_observaciones'
        ].forEach(function(id){ set(id,''); });
        set('f_tc', TC);
        setDen('mxn', null);
        setDen('usd', null);
        badge.classList.add('d-none');
      }
      recalc();
    });
  }

  document.getElementById('sel_caja').addEventListener('change', cargarCajero);
  document.getElementById('inp_fecha').addEventListener('change', cargarCajero);

  // Calcular al cargar la página
  recalc();

})();
</script>
