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

    {{-- Leyenda global de colores --}}
    <div class="d-flex gap-3 mb-3 flex-wrap" style="font-size:.75rem;font-weight:600">
      <span>
        <span style="display:inline-block;width:12px;height:12px;background:#fef9c3;border:1px solid #fbbf24;border-radius:2px;margin-right:4px"></span>
        Sistema (local_transaction) — solo lectura
      </span>
      <span>
        <span style="display:inline-block;width:12px;height:12px;background:#f1f3f5;border:1px solid #adb5bd;border-radius:2px;margin-right:4px"></span>
        Cajero Interlogic — prellenado, editable
      </span>
      <span>
        <span style="display:inline-block;width:12px;height:12px;background:#fff;border:1px solid #ced4da;border-radius:2px;margin-right:4px"></span>
        Captura del operador
      </span>
    </div>

    <form method="POST" action="{{ route('cortes.store') }}" id="formCorte">
      @csrf
      <input type="hidden" name="facility_id" value="{{ $facility->facility_id }}">

      <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8 col-xl-7">

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
                <div id="badge_interlogic" class="{{ $sistemaData['interlogic'] ? '' : 'd-none' }}">
                  <div class="alert alert-info py-1 px-2 mb-0 small mt-1">
                    <i class="bi bi-plug me-1"></i>
                    Corte Interlogic cargado — sesión <strong id="txt_sesion_badge">{{ $sistemaData['interlogic']['id_sesion'] ?? '' }}</strong>
                  </div>
                </div>
                <div id="badge_sin_interlogic" class="{{ $sistemaData['interlogic'] ? 'd-none' : '' }}">
                  <div class="alert alert-secondary py-1 px-2 mb-0 small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Sin datos del cajero Interlogic — capture manualmente los campos grises
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ══ DATOS GENERALES DEL CAJERO (interlogic) ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#e9ecef">
            <span class="fw-bold small text-uppercase text-secondary">
              <i class="bi bi-info-circle me-1"></i>Datos Generales del Cajero
            </span>
          </div>
          <div class="card-body px-4 py-3">
            @php $il = $sistemaData['interlogic']; @endphp
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small mb-1 text-muted">ID de Sesión Cerrada</label>
                <input type="text" id="dg_sesion" readonly tabindex="-1"
                       value="{{ $il['id_sesion'] ?? '' }}"
                       class="form-control form-control-sm" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
              <div class="col-6">
                <label class="form-label small mb-1 text-muted">ID de Cajero</label>
                <input type="text" id="dg_cajero" readonly tabindex="-1"
                       value="{{ $il['id_cajero'] ?? '' }}"
                       class="form-control form-control-sm" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
              <div class="col-12">
                <label class="form-label small mb-1 text-muted">Operador que Ejecutó</label>
                <input type="text" id="dg_operador" readonly tabindex="-1"
                       value="{{ $il['operador'] ?? '' }}"
                       class="form-control form-control-sm" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
              <div class="col-6">
                <label class="form-label small mb-1 text-muted">Inicio de Sesión</label>
                <input type="text" id="dg_inicio" readonly tabindex="-1"
                       value="{{ $il ? \Carbon\Carbon::parse($il['inicio_sesion'])->format('d/m/Y H:i') : '' }}"
                       class="form-control form-control-sm" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
              <div class="col-6">
                <label class="form-label small mb-1 text-muted">Fecha de Corte</label>
                <input type="text" id="dg_fin" readonly tabindex="-1"
                       value="{{ $il ? \Carbon\Carbon::parse($il['fin_sesion'])->format('d/m/Y H:i') : '' }}"
                       class="form-control form-control-sm" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
            </div>
          </div>
        </div>

        {{-- ══ PAQUETES COMPRADOS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom d-flex justify-content-between align-items-center" style="background:#faf5ff">
            <span class="fw-bold small text-uppercase" style="color:#6d28d9">
              <i class="bi bi-car-front me-1"></i>Paquetes Comprados
            </span>
            <span style="font-size:.68rem;color:#92400e;font-weight:600">
              <span style="display:inline-block;width:9px;height:9px;background:#fef9c3;border:1px solid #fbbf24;border-radius:2px;margin-right:3px"></span>Sistema
              &nbsp;
              <span style="display:inline-block;width:9px;height:9px;background:#f1f3f5;border:1px solid #adb5bd;border-radius:2px;margin-right:3px"></span>Cajero
            </span>
          </div>
          <div class="card-body px-4 py-3">
            <div class="row g-0 mb-2" style="font-size:.70rem;color:#888;font-weight:600;text-transform:uppercase">
              <div class="col-3">Paquete</div>
              <div class="col-2 text-center">Autos<br><span style="color:#92400e">Sistema</span></div>
              <div class="col-2 text-center">Autos<br>Cajero</div>
              <div class="col-2 text-center">Precio</div>
              <div class="col-3 text-end">Total</div>
            </div>
            @php
              $ordenPaq = ['Express','Ultra','Deluxe','Basico','Otro'];
              $todosPaq = collect($ordenPaq)->filter(fn($n) =>
                isset($sistemaData['paquetes'][$n]) || isset($sistemaData['interlogic']['paquetes'])
              );
              // también incluir paquetes del sistema que no estén en el orden fijo
              foreach(($sistemaData['paquetes'] ?? []) as $nombre => $info) {
                if (!in_array($nombre, $ordenPaq)) $todosPaq->push($nombre);
              }
              $todosPaq = $todosPaq->unique()->values();
              // construir mapa interlogic por nombre
              $ilPaq = collect($sistemaData['interlogic']['paquetes'] ?? [])->keyBy('paquete');
            @endphp
            @forelse($todosPaq as $nombre)
              @php
                $sis   = $sistemaData['paquetes'][$nombre] ?? null;
                $cajIl = $ilPaq[$nombre] ?? null;
                $autosCajero = $corteExistente
                  ? ($corteExistente->paquetes->firstWhere('paquete', $nombre)?->autos_cajero ?? ($cajIl['autos'] ?? ''))
                  : ($cajIl['autos'] ?? '');
                $precio = $corteExistente
                  ? ($corteExistente->paquetes->firstWhere('paquete', $nombre)?->precio ?? ($cajIl['precio'] ?? ''))
                  : ($cajIl['precio'] ?? '');
              @endphp
              <div class="row g-0 align-items-center border-bottom py-2" data-paquete="{{ $nombre }}">
                <div class="col-3 fw-semibold small">{{ $nombre }}</div>
                {{-- Autos sistema (amarillo) --}}
                <div class="col-2 px-1">
                  <input type="text" readonly tabindex="-1"
                         value="{{ $sis['autos'] ?? 0 }}"
                         class="form-control form-control-sm text-center paq-sis-autos"
                         style="background:#fef9c3;border-color:#fbbf24;color:#92400e;font-weight:600">
                </div>
                {{-- Autos cajero (gris, editable) --}}
                <div class="col-2 px-1">
                  <input type="number" min="0"
                         name="paq_cajero[{{ $nombre }}]"
                         id="paq_cajero_{{ \Illuminate\Support\Str::slug($nombre) }}"
                         value="{{ old('paq_cajero.'.$nombre, $autosCajero) }}"
                         class="form-control form-control-sm text-center paq-cajero-input"
                         style="background:#f1f3f5;border-color:#adb5bd"
                         placeholder="0">
                </div>
                {{-- Precio (gris, editable) --}}
                <div class="col-2 px-1">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd;font-size:.7rem">$</span>
                    <input type="number" step="0.01" min="0"
                           name="paq_precio[{{ $nombre }}]"
                           id="paq_precio_{{ \Illuminate\Support\Str::slug($nombre) }}"
                           value="{{ old('paq_precio.'.$nombre, $precio) }}"
                           class="form-control form-control-sm text-end paq-precio-input"
                           style="background:#f1f3f5;border-color:#adb5bd"
                           placeholder="0">
                  </div>
                </div>
                {{-- Total calculado --}}
                <div class="col-3 text-end">
                  <span class="small fw-semibold paq-total" id="paq_total_{{ \Illuminate\Support\Str::slug($nombre) }}">
                    ${{ number_format(($cajIl['total'] ?? (($sis['autos'] ?? 0) * ($sis['precio'] ?? 0))), 2) }}
                  </span>
                </div>
              </div>
            @empty
              <div class="text-muted small py-2">Sin paquetes registrados para esta fecha/cajero.</div>
            @endforelse
            {{-- Total paquetes --}}
            <div class="row g-0 align-items-center py-2 mt-1 rounded" style="background:#faf5ff">
              <div class="col-5 fw-bold small ps-1">TOTAL PAQUETES</div>
              <div class="col-4 text-center">
                <span class="badge bg-warning text-dark" id="paq_sis_total_autos">
                  {{ array_sum(array_column($sistemaData['paquetes'] ?? [], 'autos')) }}
                </span>
                <span class="text-muted mx-1" style="font-size:.7rem">vs</span>
                <span class="badge bg-secondary" id="paq_caj_total_autos">—</span>
              </div>
              <div class="col-3 text-end fw-bold small pe-1" id="paq_gran_total">$0.00</div>
            </div>
          </div>
        </div>

        {{-- ══ MEMBRESÍAS UTILIZADAS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom d-flex justify-content-between align-items-center" style="background:#f0fdf4">
            <span class="fw-bold small text-uppercase" style="color:#166534">
              <i class="bi bi-credit-card me-1"></i>Membresías Utilizadas
            </span>
            <span style="font-size:.68rem;color:#92400e;font-weight:600">
              <span style="display:inline-block;width:9px;height:9px;background:#fef9c3;border:1px solid #fbbf24;border-radius:2px;margin-right:3px"></span>Sistema
              &nbsp;
              <span style="display:inline-block;width:9px;height:9px;background:#f1f3f5;border:1px solid #adb5bd;border-radius:2px;margin-right:3px"></span>Cajero
            </span>
          </div>
          <div class="card-body px-4 py-3">
            <div class="row g-0 mb-2" style="font-size:.70rem;color:#888;font-weight:600;text-transform:uppercase">
              <div class="col-4">Paquete</div>
              <div class="col-4 text-center">Autos<br><span style="color:#92400e">Sistema</span></div>
              <div class="col-4 text-center">Autos<br>Cajero</div>
            </div>
            @php
              $ordenMem = ['Express','Ultra','Deluxe','Basico','Otro'];
              $todosMem = collect($ordenMem)->filter(fn($n) =>
                isset($sistemaData['membresias'][$n]) ||
                collect($sistemaData['interlogic']['membresias'] ?? [])->contains('paquete', $n)
              );
              foreach(($sistemaData['membresias'] ?? []) as $nombre => $info) {
                if (!in_array($nombre, $ordenMem)) $todosMem->push($nombre);
              }
              $todosMem = $todosMem->unique()->values();
              $ilMem = collect($sistemaData['interlogic']['membresias'] ?? [])->keyBy('paquete');
            @endphp
            @forelse($todosMem as $nombre)
              @php
                $sisMem  = $sistemaData['membresias'][$nombre] ?? null;
                $cajIlMem = $ilMem[$nombre] ?? null;
                $autosMem = $corteExistente
                  ? ($corteExistente->membresias->firstWhere('paquete', $nombre)?->autos_cajero ?? ($cajIlMem['autos'] ?? ''))
                  : ($cajIlMem['autos'] ?? '');
              @endphp
              <div class="row g-0 align-items-center border-bottom py-2">
                <div class="col-4 fw-semibold small">{{ $nombre }}</div>
                {{-- Sistema (amarillo) --}}
                <div class="col-4 px-2">
                  <input type="text" readonly tabindex="-1"
                         value="{{ $sisMem['autos'] ?? 0 }}"
                         class="form-control form-control-sm text-center mem-sis-autos"
                         style="background:#fef9c3;border-color:#fbbf24;color:#92400e;font-weight:600">
                </div>
                {{-- Cajero (gris, editable) --}}
                <div class="col-4 px-2">
                  <input type="number" min="0"
                         name="mem_cajero[{{ $nombre }}]"
                         id="mem_cajero_{{ \Illuminate\Support\Str::slug($nombre) }}"
                         value="{{ old('mem_cajero.'.$nombre, $autosMem) }}"
                         class="form-control form-control-sm text-center mem-cajero-input"
                         style="background:#f1f3f5;border-color:#adb5bd"
                         placeholder="0">
                </div>
              </div>
            @empty
              <div class="text-muted small py-2">Sin membresías registradas para esta fecha/cajero.</div>
            @endforelse
            {{-- Totales --}}
            <div class="row g-0 align-items-center py-2 mt-1 rounded" style="background:#f0fdf4">
              <div class="col-4 fw-bold small ps-1">TOTAL MEMBRESÍAS</div>
              <div class="col-4 text-center">
                <span class="badge bg-warning text-dark" id="mem_sis_total">
                  {{ array_sum(array_column(array_map(fn($m) => ['autos' => $m['autos']], $sistemaData['membresias'] ?? []), 'autos')) }}
                </span>
              </div>
              <div class="col-4 text-center">
                <span class="badge bg-secondary" id="mem_caj_total">—</span>
              </div>
            </div>
          </div>
        </div>

        {{-- ══ PAGOS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 bg-primary bg-opacity-10 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-primary small text-uppercase">
              <i class="bi bi-cash-stack me-1"></i>Pagos Recibidos
            </span>
          </div>
          <div class="card-body px-4 py-3">

            {{-- Leyenda --}}
            <div class="row g-0 py-1 mb-1" style="font-size:.70rem;color:#888;font-weight:600;text-transform:uppercase">
              <div class="col-4"></div>
              <div class="col-3 text-center" style="color:#92400e">Sistema</div>
              <div class="col-5 text-center">Cajero / Captura</div>
            </div>

            {{-- Tarjeta --}}
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-4 text-muted small">Tarjetas</div>
              <div class="col-3 pe-1">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#fef9c3;border-color:#fbbf24;color:#92400e;font-size:.70rem"
                        id="sis_num_tarjeta_badge">{{ $sistemaData['num_pagos_tarjeta'] }}x</span>
                  <input type="text" id="sis_importe_tarjeta" readonly tabindex="-1"
                         value="{{ '$'.number_format($sistemaData['importe_tarjeta'],2) }}"
                         class="form-control form-control-sm text-end" style="background:#fef9c3;border-color:#fbbf24;color:#92400e;font-weight:600">
                </div>
              </div>
              <div class="col-2 pe-1">
                <input type="number" min="0" name="num_pagos_tarjeta" id="f_num_tarjeta"
                       value="{{ old('num_pagos_tarjeta', $corteExistente->num_pagos_tarjeta ?? ($sistemaData['interlogic']['tarjeta_numero_pagos'] ?? '')) }}"
                       class="form-control form-control-sm text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="# pagos">
              </div>
              <div class="col-3">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd">$</span>
                  <input type="number" step="0.01" min="0" name="importe_tarjeta" id="f_tarjeta"
                         value="{{ old('importe_tarjeta', $corteExistente->importe_tarjeta ?? ($sistemaData['interlogic']['tarjeta_importe_pagado'] ?? '')) }}"
                         class="form-control text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1">
              <div class="col-7 text-muted small ps-2">Total Tarjeta</div>
              <div class="col-5 text-end small fw-semibold pe-1" id="txt_prosepago">$0.00</div>
            </div>

            {{-- Efectivo MXN --}}
            @php
              $ilPesos = collect($sistemaData['interlogic']['totales_efectivo'] ?? [])->firstWhere('moneda', 'Pesos');
              $ilDlls  = collect($sistemaData['interlogic']['totales_efectivo'] ?? [])->firstWhere('moneda', 'Dólares');
            @endphp
            <div class="row g-0 align-items-center border-top border-bottom py-2 mt-1">
              <div class="col-4 text-muted small">Efectivo MXN</div>
              <div class="col-3 pe-1">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#fef9c3;border-color:#fbbf24"><i class="bi bi-database" style="color:#92400e;font-size:.70rem"></i></span>
                  <input type="text" id="sis_efectivo_mxn" readonly tabindex="-1"
                         value="{{ '$'.number_format($sistemaData['efectivo_mxn'],2) }}"
                         class="form-control form-control-sm text-end" style="background:#fef9c3;border-color:#fbbf24;color:#92400e;font-weight:600">
                </div>
              </div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd">$</span>
                  <input type="number" step="0.01" min="0" name="efectivo_mxn" id="f_mxn"
                         value="{{ old('efectivo_mxn', $corteExistente->efectivo_mxn ?? ($ilPesos['cantidad'] ?? '')) }}"
                         class="form-control text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="0.00">
                </div>
              </div>
            </div>

            {{-- Efectivo DLLS --}}
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-4 text-muted small">Efectivo USD</div>
              <div class="col-3 pe-1"></div>
              <div class="col-2 pe-1">
                <input type="number" step="0.01" min="0" name="efectivo_dlls_cantidad" id="f_dlls"
                       value="{{ old('efectivo_dlls_cantidad', $corteExistente->efectivo_dlls_cantidad ?? ($ilDlls['cantidad'] ?? '')) }}"
                       class="form-control form-control-sm text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="cant.">
              </div>
              <div class="col-1 pe-1">
                <input type="number" step="0.0001" min="0" name="efectivo_dlls_tc" id="f_tc"
                       value="{{ old('efectivo_dlls_tc', $corteExistente->efectivo_dlls_tc ?? ($ilDlls['tc'] ?? $tipoCambio)) }}"
                       class="form-control form-control-sm text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="TC">
              </div>
              <div class="col-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control text-end bg-light" id="txt_dlls_importe"
                         readonly tabindex="-1" placeholder="—">
                </div>
              </div>
            </div>

            <div class="row g-0 align-items-center py-1 border-bottom">
              <div class="col-7 text-muted small ps-2">Total Efectivo</div>
              <div class="col-5 text-end small fw-semibold pe-1" id="txt_efectivo">$0.00</div>
            </div>
            <div class="row g-0 align-items-center py-2 rounded mt-1" style="background:#e0f2fe">
              <div class="col-7 fw-bold small ps-2">TOTAL DE VENTA</div>
              <div class="col-5 text-end fw-bold small pe-1" id="txt_venta">$0.00</div>
            </div>
            <input type="number" step="0.01" name="total_ventas" id="f_total_ventas" class="d-none"
                   value="{{ old('total_ventas', $corteExistente->total_ventas ?? '') }}">

          </div>
        </div>

        {{-- ══ PAGOS CON ERROR (interlogic) ══ --}}
        <div class="card mb-2" id="card_pagos_error">
          <div class="card-header py-2 px-4 border-bottom" style="background:#fff3cd">
            <span class="fw-bold small text-uppercase" style="color:#856404">
              <i class="bi bi-exclamation-triangle me-1"></i>Pagos con Error
              <span class="badge bg-warning text-dark ms-2" id="badge_num_errores">
                {{ count($sistemaData['interlogic']['pagos_error'] ?? []) }}
              </span>
            </span>
          </div>
          <div class="card-body px-2 py-2">
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0" style="font-size:.75rem">
                <thead class="table-light">
                  <tr>
                    <th>Fecha/Hora</th>
                    <th class="text-end">A Pagar</th>
                    <th class="text-end">Ingresado</th>
                    <th class="text-end">Cambio</th>
                    <th class="text-end">Faltante</th>
                  </tr>
                </thead>
                <tbody id="tbody_errores">
                  @forelse($sistemaData['interlogic']['pagos_error'] ?? [] as $pe)
                    <tr>
                      <td class="text-nowrap">{{ $pe['fecha_hora'] ? \Carbon\Carbon::parse($pe['fecha_hora'])->format('d/m H:i') : '—' }}</td>
                      <td class="text-end">${{ number_format($pe['a_pagar'],2) }}</td>
                      <td class="text-end">${{ number_format($pe['ingresado'],2) }}</td>
                      <td class="text-end">${{ number_format($pe['cambio_entregado'],2) }}</td>
                      <td class="text-end text-danger">${{ number_format($pe['cambio_faltante'],2) }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-muted py-2">Sin pagos con error</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if(count($sistemaData['interlogic']['pagos_error'] ?? []) > 0)
              <div class="text-end pe-2 pt-1" style="font-size:.72rem;color:#856404;font-weight:600">
                Total faltante: ${{ number_format(array_sum(array_column($sistemaData['interlogic']['pagos_error'], 'cambio_faltante')), 2) }}
              </div>
            @endif
          </div>
        </div>

        {{-- ══ RESUMEN DE CORTE (interlogic) ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#f1f5f9">
            <span class="fw-bold small text-uppercase text-secondary">
              <i class="bi bi-list-check me-1"></i>Resumen de Corte de Caja
            </span>
          </div>
          <div class="card-body px-4 py-3">
            @php
              $il2 = $sistemaData['interlogic'];
            @endphp
            @foreach([
              ['TOTAL INGRESADO EN PAGOS CORRECTOS',  'corte_total_ingresado_correctos'],
              ['TOTAL INGRESADO EN PAGOS CON ERROR',  'corte_total_ingresado_error'],
              ['TOTAL INGRESADO EN PAGOS CANCELADOS', 'corte_total_ingresado_cancelados'],
              ['TOTAL INGRESADO',                     'corte_total_ingresado'],
              ['CAMBIO ENTREGADO EN PAGOS CORRECTOS', 'corte_total_cambio_entregado_correctos'],
              ['CAMBIO ENTREGADO EN PAGOS CON ERROR', 'corte_total_cambio_entregado_error'],
              ['DEVUELTO EN PAGOS CANCELADOS',        'corte_total_devuelto_cancelados'],
              ['TOTAL DE CAMBIO ENTREGADO',           'corte_total_cambio_entregado'],
            ] as [$label, $key])
            <div class="row g-0 align-items-center border-bottom py-1">
              <div class="col-8 text-muted small">{{ $label }}</div>
              <div class="col-4 text-end">
                <span class="fw-semibold small" style="color:{{ $il2 ? '#495057' : '#adb5bd' }}">
                  ${{ number_format($il2[$key] ?? 0, 2) }}
                </span>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- ══ BALANCE GENERAL (interlogic) ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#f0f9ff">
            <span class="fw-bold small text-uppercase" style="color:#0c4a6e">
              <i class="bi bi-calculator me-1"></i>Balance General
            </span>
          </div>
          <div class="card-body px-4 py-3">
            @foreach([
              ['(+) Total Ingresado',    'balance_total_ingresado',   false],
              ['(-) Total de Venta',     'balance_total_venta',       false],
              ['(-) Cambio Entregado',   'balance_cambio_entregado',  false],
              ['(-) Cambio No Entregado','balance_cambio_no_entregado',false],
              ['(-) Dotación',           'corte_total_dotaciones',    false],
              ['(=) RESULTADO',          'balance_resultado',         true],
            ] as [$label, $key, $bold])
            <div class="row g-0 align-items-center border-bottom py-1 {{ $bold ? 'fw-bold' : '' }}">
              <div class="col-8 small {{ $bold ? '' : 'text-muted' }}">{{ $label }}</div>
              <div class="col-4 text-end">
                @php $val = $il2[$key] ?? 0; @endphp
                <span class="small {{ $bold ? 'fs-6' : '' }}" style="color:{{ $bold ? ($val == 0 ? '#166534' : ($val > 0 ? '#0c4a6e' : '#991b1b')) : '#495057' }}">
                  ${{ number_format($val, 2) }}
                </span>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- ══ EGRESOS ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 bg-warning bg-opacity-10 border-bottom">
            <span class="fw-bold text-warning small text-uppercase">
              <i class="bi bi-arrow-down-circle me-1"></i>Egresos
            </span>
          </div>
          <div class="card-body px-4 py-3">
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Dotación</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd">$</span>
                  <input type="number" step="0.01" min="0" name="dotacion" id="f_dotacion"
                         value="{{ old('dotacion', $corteExistente->dotacion ?? ($il2['corte_total_dotaciones'] ?? '')) }}"
                         class="form-control text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 text-muted small">Pagos Cancelados / Devueltos</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd">$</span>
                  <input type="number" step="0.01" min="0" name="pagos_cancelados" id="f_cancelados"
                         value="{{ old('pagos_cancelados', $corteExistente->pagos_cancelados ?? ($il2['corte_total_devuelto_cancelados'] ?? 0)) }}"
                         class="form-control text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1">
              <div class="col-7 fw-bold small ps-2">Total Egresos</div>
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
            {{-- Saldo dispensadores de interlogic --}}
            @if(count($sistemaData['interlogic']['saldo_dispensadores'] ?? []) > 0)
              <div class="mb-3">
                <div class="text-muted small fw-semibold mb-1" style="font-size:.72rem;text-transform:uppercase">Saldo Final según Cajero</div>
                <table class="table table-sm table-bordered mb-0" style="font-size:.75rem">
                  <thead class="table-light">
                    <tr><th>Denominación</th><th>Divisa</th><th class="text-end">Total</th></tr>
                  </thead>
                  <tbody>
                    @foreach($sistemaData['interlogic']['saldo_dispensadores'] as $sd)
                      <tr style="background:#f1f3f5">
                        <td>${{ number_format($sd['denominacion'],0) }}</td>
                        <td>{{ $sd['divisa'] }}</td>
                        <td class="text-end">${{ number_format($sd['total'],2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif

            <div class="row g-0 py-1 mb-1" style="font-size:.75rem;color:#666">
              <div class="col-4"></div>
              <div class="col-4 text-center">Saldo Inicial</div>
              <div class="col-4 text-center">Dotación Final</div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-4 text-muted small">Dispensador</div>
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
                         class="form-control text-end bg-light" placeholder="0.00" readonly tabindex="-1">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center py-1 bg-light rounded mt-1">
              <div class="col-7 fw-bold small ps-2">Saldo en Dispensadores</div>
              <div class="col-5 text-end fw-bold small pe-1" id="txt_dispensador">$0.00</div>
            </div>
          </div>
        </div>

        {{-- ══ ACEPTADO POR EL CAJERO MXN (interlogic) ══ --}}
        @php
          $denAceptadoMxn = collect($sistemaData['interlogic']['denominaciones'] ?? [])->where('tipo','aceptado_mxp');
          $denAceptadoUsd = collect($sistemaData['interlogic']['denominaciones'] ?? [])->where('tipo','aceptado_usd');
          $denConteoMxn   = collect($sistemaData['interlogic']['denominaciones'] ?? [])->where('tipo','conteo_operador')->filter(fn($d) => $d['valor'] >= 1);
        @endphp
        @if($denAceptadoMxn->isNotEmpty() || $denAceptadoUsd->isNotEmpty())
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#f1f5f9">
            <span class="fw-bold small text-uppercase text-secondary">
              <i class="bi bi-cash-coin me-1"></i>Aceptado por el Cajero
            </span>
          </div>
          <div class="card-body px-4 py-3">
            @if($denAceptadoMxn->isNotEmpty())
              <div class="text-muted small fw-semibold mb-1" style="font-size:.72rem;text-transform:uppercase">MXN</div>
              <table class="table table-sm table-bordered mb-2" style="font-size:.75rem">
                <thead class="table-light"><tr><th>Valor</th><th class="text-center">Cantidad</th><th class="text-end">Importe</th></tr></thead>
                <tbody>
                  @foreach($denAceptadoMxn as $den)
                    <tr style="background:#f1f3f5">
                      <td>${{ number_format($den['valor'],0) }}</td>
                      <td class="text-center">{{ $den['cantidad'] }}</td>
                      <td class="text-end">${{ number_format($den['importe'],2) }}</td>
                    </tr>
                  @endforeach
                  <tr class="fw-bold">
                    <td colspan="2">Total</td>
                    <td class="text-end">${{ number_format($denAceptadoMxn->sum('importe'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            @endif
            @if($denAceptadoUsd->isNotEmpty())
              <div class="text-muted small fw-semibold mb-1 mt-2" style="font-size:.72rem;text-transform:uppercase">USD</div>
              <table class="table table-sm table-bordered mb-0" style="font-size:.75rem">
                <thead class="table-light"><tr><th>Valor</th><th class="text-center">Cantidad</th><th class="text-end">Importe</th></tr></thead>
                <tbody>
                  @foreach($denAceptadoUsd as $den)
                    <tr style="background:#f1f3f5">
                      <td>${{ number_format($den['valor'],0) }}</td>
                      <td class="text-center">{{ $den['cantidad'] }}</td>
                      <td class="text-end">${{ number_format($den['importe'],2) }}</td>
                    </tr>
                  @endforeach
                  <tr class="fw-bold">
                    <td colspan="2">Total</td>
                    <td class="text-end">${{ number_format($denAceptadoUsd->sum('importe'),2) }}</td>
                  </tr>
                </tbody>
              </table>
            @endif
          </div>
        </div>
        @endif

        {{-- ══ DENOMINACIONES DEL OPERADOR MXN ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#f0fdf4">
            <span class="fw-bold small text-uppercase" style="color:#166534">
              <i class="bi bi-currency-exchange me-1"></i>Conteo del Operador — Pesos MXN
            </span>
          </div>
          <div class="card-body px-4 py-3">
            <div class="row g-0 py-1 mb-1" style="font-size:.72rem;color:#888;font-weight:600;text-transform:uppercase">
              <div class="col-5">Denominación</div>
              <div class="col-3 text-center">Cantidad</div>
              <div class="col-4 text-end">Monto</div>
            </div>

            <div class="fw-semibold text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em">Billetes</div>
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

            <div class="fw-semibold text-muted mb-1 mt-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em">Monedas</div>
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
              <i class="bi bi-currency-dollar me-1"></i>Conteo del Operador — USD
              <span class="fw-normal text-muted ms-2">(TC ${{ number_format($tipoCambio,2) }})</span>
            </span>
          </div>
          <div class="card-body px-4 py-3">
            <div class="row g-0 py-1 mb-1" style="font-size:.72rem;color:#888;font-weight:600;text-transform:uppercase">
              <div class="col-3">Denom.</div>
              <div class="col-3 text-center">TC</div>
              <div class="col-3 text-center">Cantidad</div>
              <div class="col-3 text-end">Monto MXN</div>
            </div>
            @foreach(['usd_b50'=>['B. $50',50],'usd_b20'=>['B. $20',20],'usd_b10'=>['B. $10',10],'usd_b5'=>['B. $5',5],'usd_b2'=>['B. $2',2],'usd_b1'=>['B. $1',1]] as $k => [$lbl,$val])
            <div class="row g-0 align-items-center border-bottom py-1" data-val="{{ $val }}">
              <div class="col-3 small">{{ $lbl }}</div>
              <div class="col-3 text-center small text-muted">${{ number_format($tipoCambio,0) }}</div>
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
              <div class="col-8 fw-semibold small ps-1">Total USD (en MXN)</div>
              <div class="col-4 text-end fw-semibold small pe-1" id="txt_total_usd">$0.00</div>
            </div>
          </div>
        </div>

        {{-- ══ DIFERENCIA CAJERO (interlogic) ══ --}}
        @if($il2)
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#fef2f2">
            <span class="fw-bold small text-uppercase" style="color:#991b1b">
              <i class="bi bi-arrow-left-right me-1"></i>Diferencia (Cajero Interlogic)
            </span>
          </div>
          <div class="card-body px-4 py-3">
            @foreach([
              ['Registrado en Cajero',    number_format($il2['aceptado_cajero_total'],2)],
              ['Reportado por Operador',  number_format($il2['conteo_operador_total'],2)],
            ] as [$label, $valor])
            <div class="row g-0 align-items-center border-bottom py-1">
              <div class="col-7 text-muted small">{{ $label }}</div>
              <div class="col-5 text-end fw-semibold small">${{ $valor }}</div>
            </div>
            @endforeach
            <div class="row g-0 align-items-center py-2 mt-1 rounded px-2 fw-bold"
                 style="background:{{ $il2['diferencia_total'] == 0 ? '#d1fae5' : '#fee2e2' }};
                        color:{{ $il2['diferencia_total'] == 0 ? '#065f46' : '#991b1b' }}">
              <div class="col-7 small">DIFERENCIA</div>
              <div class="col-5 text-end">${{ number_format($il2['diferencia_total'],2) }}</div>
            </div>
          </div>
        </div>
        @endif

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
                  <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd">$</span>
                  <input type="number" step="0.01" min="0" name="cambio_entregado" id="f_cambio_e"
                         value="{{ old('cambio_entregado', $corteExistente->cambio_entregado ?? ($il2['corte_total_cambio_entregado'] ?? '')) }}"
                         class="form-control text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="0.00">
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
                       class="form-control form-control-sm" placeholder="Justificación del cambio...">
              </div>
            </div>
          </div>
        </div>

        {{-- ══ PROMOCIONES QR (interlogic) ══ --}}
        <div class="card mb-2">
          <div class="card-header py-2 px-4 border-bottom" style="background:#fdf4ff">
            <span class="fw-bold small text-uppercase" style="color:#6b21a8">
              <i class="bi bi-qr-code me-1"></i>Promociones QR
            </span>
          </div>
          <div class="card-body px-4 py-3">
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small mb-1 text-muted">Promociones Aplicadas</label>
                <input type="text" readonly tabindex="-1"
                       value="{{ $il2['promociones_qr_aplicadas'] ?? 0 }}"
                       class="form-control form-control-sm text-center" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
              <div class="col-6">
                <label class="form-label small mb-1 text-muted">Cortesías Aplicadas</label>
                <input type="text" readonly tabindex="-1"
                       value="{{ $il2['cortesias_aplicadas'] ?? 0 }}"
                       class="form-control form-control-sm text-center" style="background:#f1f3f5;border-color:#adb5bd">
              </div>
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
              <div class="col-7 fw-semibold small">Corte Total Efectivo (cajero)</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="background:#f1f3f5;border-color:#adb5bd">$</span>
                  <input type="number" step="0.01" min="0" name="corte_total_efectivo" id="f_corte"
                         value="{{ old('corte_total_efectivo', $corteExistente->corte_total_efectivo ?? ($il2['corte_total_ingresado'] ?? '')) }}"
                         class="form-control text-end" style="background:#f1f3f5;border-color:#adb5bd" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row g-0 align-items-center border-bottom py-2">
              <div class="col-7 fw-semibold small">Efectivo Entregado por Operador</div>
              <div class="col-5">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="efectivo_entregado" id="f_entregado"
                         value="{{ old('efectivo_entregado', $corteExistente->efectivo_entregado ?? '') }}"
                         class="form-control text-end" placeholder="0.00">
                </div>
              </div>
            </div>

            <div class="row g-0 align-items-center py-2 px-2 mt-2 rounded fw-bold fs-6" id="diff_badge"
                 style="background:#f8f9fa;border:2px solid #dee2e6">
              <div class="col-6">Diferencia</div>
              <div class="col-6 text-end" id="txt_diff">$0.00</div>
            </div>

            <div class="mt-3">
              <label class="form-label small fw-semibold mb-1">Observaciones</label>
              <textarea name="observaciones" id="f_observaciones"
                        class="form-control form-control-sm" rows="2"
                        placeholder="Notas opcionales...">{{ old('observaciones', $corteExistente->observaciones ?? '') }}</textarea>
            </div>
          </div>
        </div>

        <div style="height:72px"></div>

      </div>{{-- col --}}
      </div>{{-- row --}}
    </form>

    {{-- Barra fija inferior --}}
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
  var SISTEMA_INIT = @json($sistemaData);

  function fmt(n) { return '$' + parseFloat(n || 0).toFixed(2); }
  function num(id) { return parseFloat(document.getElementById(id)?.value) || 0; }
  function set(id, val) {
    var el = document.getElementById(id);
    if (el && val !== null && val !== undefined) el.value = val;
  }
  function setGray(id, val) {
    var el = document.getElementById(id);
    if (el && (val !== null && val !== undefined)) el.value = val;
  }

  // ── Sistema (amarillo) ────────────────────────────────────────────────────
  function setSistema(s) {
    if (!s) return;
    set('sis_total_ventas',    fmt(s.total_ventas));
    set('sis_importe_tarjeta', fmt(s.importe_tarjeta));
    set('sis_efectivo_mxn',    fmt(s.efectivo_mxn));
    var nb = document.getElementById('sis_num_tarjeta_badge');
    if (nb) nb.textContent = (s.num_pagos_tarjeta || 0) + 'x';

    // Paquetes sistema (amarillo)
    (Object.values(s.paquetes || {})).forEach(function(p) {
      var slug = p.paquete.toLowerCase().replace(/[^a-z0-9]/g,'-');
      var el = document.querySelector('[id="paq_sis_' + slug + '"], .paq-sis-autos[data-paq="' + p.paquete + '"]');
      // buscar por fila
      document.querySelectorAll('[data-paquete="' + p.paquete + '"] .paq-sis-autos').forEach(function(inp){ inp.value = p.autos; });
    });

    // Total sistema paquetes
    var totalSisAutos = Object.values(s.paquetes || {}).reduce(function(a,p){ return a + p.autos; }, 0);
    var el = document.getElementById('paq_sis_total_autos');
    if (el) el.textContent = totalSisAutos;

    // Membresías sistema (amarillo)
    (Object.values(s.membresias || {})).forEach(function(m) {
      document.querySelectorAll('[data-mem="' + m.paquete + '"] .mem-sis-autos, .mem-sis-autos[data-paq="' + m.paquete + '"]').forEach(function(inp){ inp.value = m.autos; });
    });
    var totalSisMem = Object.values(s.membresias || {}).reduce(function(a,m){ return a + m.autos; }, 0);
    var elMem = document.getElementById('mem_sis_total');
    if (elMem) elMem.textContent = totalSisMem;
  }

  // ── Interlogic (gris, pre-llena inputs editables) ─────────────────────────
  function setInterlogic(il) {
    if (!il) return;

    // Datos generales (display)
    set('dg_sesion',   il.id_sesion);
    set('dg_cajero',   il.id_cajero);
    set('dg_operador', il.operador);
    set('dg_inicio',   il.inicio_sesion ? formatFecha(il.inicio_sesion) : '');
    set('dg_fin',      il.fin_sesion    ? formatFecha(il.fin_sesion)    : '');

    // Badge interlogic
    var bi = document.getElementById('badge_interlogic');
    var bs = document.getElementById('badge_sin_interlogic');
    var sb = document.getElementById('txt_sesion_badge');
    if (bi) bi.classList.remove('d-none');
    if (bs) bs.classList.add('d-none');
    if (sb) sb.textContent = il.id_sesion;

    // Tarjeta
    setGray('f_num_tarjeta', il.tarjeta_numero_pagos);
    setGray('f_tarjeta',     il.tarjeta_importe_pagado);

    // Efectivo
    var pesos = (il.totales_efectivo || []).find(function(t){ return t.moneda === 'Pesos'; });
    var dlls  = (il.totales_efectivo || []).find(function(t){ return t.moneda === 'Dólares'; });
    if (pesos) setGray('f_mxn',  pesos.cantidad);
    if (dlls)  { setGray('f_dlls', dlls.cantidad); setGray('f_tc', dlls.tc); }

    // Egresos
    setGray('f_dotacion',   il.corte_total_dotaciones);
    setGray('f_cancelados', il.corte_total_devuelto_cancelados);

    // Cambio
    setGray('f_cambio_e', il.corte_total_cambio_entregado);

    // Corte total
    setGray('f_corte', il.corte_total_ingresado);

    // Paquetes cajero (gris)
    (il.paquetes || []).forEach(function(p) {
      var slug = slugify(p.paquete);
      setGray('paq_cajero_' + slug, p.autos);
      setGray('paq_precio_' + slug, p.precio);
    });

    // Membresías cajero (gris)
    (il.membresias || []).forEach(function(m) {
      setGray('mem_cajero_' + slugify(m.paquete), m.autos);
    });

    // Pagos con error - actualizar tabla
    renderPagosError(il.pagos_error || []);
  }

  function formatFecha(str) {
    if (!str) return '';
    var d = new Date(str.replace(' ', 'T'));
    if (isNaN(d)) return str;
    return d.toLocaleDateString('es-MX') + ' ' + d.toLocaleTimeString('es-MX', {hour:'2-digit',minute:'2-digit'});
  }

  function slugify(s) {
    return (s || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g,'');
  }

  function renderPagosError(rows) {
    var tbody = document.getElementById('tbody_errores');
    var badge = document.getElementById('badge_num_errores');
    if (!tbody) return;
    if (badge) badge.textContent = rows.length;
    if (rows.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-2">Sin pagos con error</td></tr>';
      return;
    }
    tbody.innerHTML = rows.map(function(r) {
      var fh = r.fecha_hora ? formatFecha(r.fecha_hora) : '—';
      return '<tr><td class="text-nowrap">' + fh + '</td>' +
             '<td class="text-end">$' + parseFloat(r.a_pagar||0).toFixed(2) + '</td>' +
             '<td class="text-end">$' + parseFloat(r.ingresado||0).toFixed(2) + '</td>' +
             '<td class="text-end">$' + parseFloat(r.cambio_entregado||0).toFixed(2) + '</td>' +
             '<td class="text-end text-danger">$' + parseFloat(r.cambio_faltante||0).toFixed(2) + '</td></tr>';
    }).join('');
  }

  // ── Totales guardados en un corte existente ───────────────────────────────
  function setCorte(d) {
    set('f_total_ventas',     d.total_ventas);
    set('f_num_tarjeta',      d.num_pagos_tarjeta);
    set('f_tarjeta',          d.importe_tarjeta);
    set('f_mxn',              d.efectivo_mxn);
    set('f_dlls',             d.efectivo_dlls_cantidad);
    set('f_tc',               d.efectivo_dlls_tc);
    set('f_dotacion',         d.dotacion);
    set('f_cancelados',       d.pagos_cancelados);
    set('f_saldo_disp',       d.saldo_inicial_dispensador);
    set('f_dot_final',        d.dotacion_final);
    set('f_cambio_e',         d.cambio_entregado);
    set('f_cambio_ne',        d.cambio_no_entregado);
    set('f_referencia',       d.referencia_cambio);
    set('f_corte',            d.corte_total_efectivo);
    set('f_entregado',        d.efectivo_entregado);
    set('f_observaciones',    d.observaciones);
    setDen('mxn', d.den_mxn);
    setDen('usd', d.den_usd);

    // Paquetes guardados
    if (d.paq_cajero) {
      Object.entries(d.paq_cajero).forEach(function(kv) {
        set('paq_cajero_' + slugify(kv[0]), kv[1].autos_cajero);
        set('paq_precio_' + slugify(kv[0]), kv[1].precio);
      });
    }
    // Membresías guardadas
    if (d.mem_cajero) {
      Object.entries(d.mem_cajero).forEach(function(kv) {
        set('mem_cajero_' + slugify(kv[0]), kv[1]);
      });
    }

    var badge = document.getElementById('badge_existente');
    var txt   = document.getElementById('txt_fecha_guardado');
    if (badge) badge.classList.remove('d-none');
    if (txt && d.updated_at) txt.textContent = d.updated_at;
  }

  // ── Recalcular totales ────────────────────────────────────────────────────
  function recalc() {
    var tarjeta    = num('f_tarjeta');
    var mxn        = num('f_mxn');
    var dlls       = num('f_dlls');
    var tc         = num('f_tc') || TC;
    var dotacion   = num('f_dotacion');
    var cancelados = num('f_cancelados');
    var saldo_disp = num('f_saldo_disp');

    var dlls_mxn = dlls * tc;
    var efectivo = mxn + dlls_mxn;
    var venta    = tarjeta + efectivo;
    var egresos  = dotacion + cancelados;
    var disp     = saldo_disp + dotacion;

    var elTV = document.getElementById('f_total_ventas');
    if (elTV) elTV.value = venta.toFixed(2);

    document.getElementById('txt_prosepago').textContent   = fmt(tarjeta);
    document.getElementById('txt_dlls_importe').value      = fmt(dlls_mxn);
    document.getElementById('txt_efectivo').textContent    = fmt(efectivo);
    document.getElementById('txt_venta').textContent       = fmt(venta);
    document.getElementById('txt_egresos').textContent     = fmt(egresos);
    document.getElementById('txt_dispensador').textContent = fmt(disp);
    var elDotFinal = document.getElementById('f_dot_final');
    if (elDotFinal) elDotFinal.value = disp.toFixed(2);

    // Denominaciones MXN
    var totalBilletes = 0, totalMonedas = 0;
    var billetesKeys = ['b500','b200','b100','b50','b20','b10','b5','b2','b1'];
    document.querySelectorAll('.den-mxn-input').forEach(function(inp) {
      var row  = inp.closest('[data-den-mxn]');
      var val  = parseFloat(row?.dataset.val || 0);
      var cant = parseFloat(inp.value) || 0;
      var monto = cant * val;
      var key  = row?.dataset.denMxn || inp.name.match(/\[(\w+)\]/)?.[1];
      var mtoEl = document.getElementById('mto_mxn_' + key);
      if (mtoEl) mtoEl.textContent = fmt(monto);
      if (billetesKeys.includes(key)) totalBilletes += monto;
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

    // Paquetes: total cajero y gran total
    var totalCajAutos = 0, granTotal = 0;
    document.querySelectorAll('.paq-cajero-input').forEach(function(inp) {
      var row    = inp.closest('[data-paquete]');
      var nombre = row?.dataset.paquete;
      var autos  = parseFloat(inp.value) || 0;
      var slug   = slugify(nombre || '');
      var precioEl = document.getElementById('paq_precio_' + slug);
      var precio   = precioEl ? parseFloat(precioEl.value) || 0 : 0;
      var total    = autos * precio;
      var totalEl  = document.getElementById('paq_total_' + slug);
      if (totalEl) totalEl.textContent = fmt(total);
      totalCajAutos += autos;
      granTotal     += total;
    });
    var elCajAutos = document.getElementById('paq_caj_total_autos');
    var elGranTotal = document.getElementById('paq_gran_total');
    if (elCajAutos)  elCajAutos.textContent  = totalCajAutos;
    if (elGranTotal) elGranTotal.textContent  = fmt(granTotal);

    // Membresías: total cajero
    var totalCajMem = 0;
    document.querySelectorAll('.mem-cajero-input').forEach(function(inp) {
      totalCajMem += parseFloat(inp.value) || 0;
    });
    var elMemTotal = document.getElementById('mem_caj_total');
    if (elMemTotal) elMemTotal.textContent = totalCajMem;

    calcDiff();
  }

  function calcDiff() {
    var corte     = num('f_corte');
    var entregado = num('f_entregado');
    var diff      = entregado - corte;
    var badge     = document.getElementById('diff_badge');
    document.getElementById('txt_diff').textContent = fmt(diff);
    if (diff === 0) {
      badge.style.cssText = 'background:#d1fae5;border:2px solid #10b981;color:#065f46';
    } else if (diff > 0) {
      badge.style.cssText = 'background:#fff3cd;border:2px solid #fbbf24;color:#856404';
    } else {
      badge.style.cssText = 'background:#fee2e2;border:2px solid #f87171;color:#991b1b';
    }
  }

  function setDen(prefix, obj) {
    var keys = prefix === 'mxn'
      ? ['b500','b200','b100','b50','b20','b10','b5','b2','b1','m10','m5','m2','m1']
      : ['usd_b50','usd_b20','usd_b10','usd_b5','usd_b2','usd_b1'];
    keys.forEach(function(k) {
      var el = document.getElementById('f_' + prefix + '_' + k);
      if (el) el.value = (obj && obj[k]) ? obj[k] : '';
    });
  }

  // ── AJAX: cambio de caja o fecha ──────────────────────────────────────────
  function cargarCajero() {
    var cajaId = document.getElementById('sel_caja').value;
    var fecha  = document.getElementById('inp_fecha').value;
    if (!cajaId || !fecha) return;

    fetch(URL_JSON + '?caja_id=' + cajaId + '&fecha_corte=' + fecha, {
      headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      setSistema(d.sistema);

      var badge = document.getElementById('badge_existente');
      var bi    = document.getElementById('badge_interlogic');
      var bs    = document.getElementById('badge_sin_interlogic');

      // Si hay interlogic, prellenar grises
      if (d.sistema && d.sistema.interlogic) {
        setInterlogic(d.sistema.interlogic);
      } else {
        if (bi) bi.classList.add('d-none');
        if (bs) bs.classList.remove('d-none');
      }

      if (d.existe) {
        // Corte guardado: sobrescribe prellenado de interlogic
        setCorte(d);
        if (badge) badge.classList.remove('d-none');
      } else {
        // Limpiar campos de captura del operador (no los grises de interlogic)
        set('f_cambio_ne', '');
        set('f_referencia', '');
        set('f_entregado', '');
        set('f_observaciones', '');
        setDen('mxn', null);
        setDen('usd', null);
        if (badge) badge.classList.add('d-none');
      }
      recalc();
    });
  }

  // ── Listeners ─────────────────────────────────────────────────────────────
  ['f_tarjeta','f_mxn','f_dlls','f_tc','f_dotacion','f_cancelados',
   'f_saldo_disp','f_corte','f_entregado'].forEach(function(id) {
    document.getElementById(id)?.addEventListener('input', recalc);
  });
  document.querySelectorAll('.den-mxn-input, .den-usd-input, .paq-cajero-input, .paq-precio-input, .mem-cajero-input').forEach(function(el) {
    el.addEventListener('input', recalc);
  });

  document.getElementById('sel_caja').addEventListener('change', cargarCajero);
  document.getElementById('inp_fecha').addEventListener('change', cargarCajero);

  // ── Inicializar con datos actuales ────────────────────────────────────────
  setSistema(SISTEMA_INIT);
  recalc();

})();
</script>
</body>
</html>
