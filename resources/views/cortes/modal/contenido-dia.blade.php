{{-- Tarjetas resumen del día --}}
<div class="row g-0 border-bottom">
  @php
    $tarjetas = [
      ['lbl'=>'Prosepago',   'val'=>$totalDia['prosepago'],  'color'=>'text-primary'],
      ['lbl'=>'Efectivo',    'val'=>$totalDia['efectivo'],   'color'=>'text-success'],
      ['lbl'=>'Venta total', 'val'=>$totalDia['venta'],      'color'=>'text-dark'],
      ['lbl'=>'Diferencia',  'val'=>$totalDia['diferencia'],
       'color'=> $totalDia['diferencia']==0 ? 'text-success' : ($totalDia['diferencia']>0 ? 'text-warning' : 'text-danger')],
    ];
  @endphp
  @foreach($tarjetas as $t)
    <div class="col-6 col-md-3 border-end py-3 text-center">
      <div class="text-muted small">{{ $t['lbl'] }}</div>
      <div class="fs-5 fw-bold {{ $t['color'] }}">${{ number_format($t['val'],2) }}</div>
    </div>
  @endforeach
</div>

{{-- Selector de cajero --}}
<div class="px-3 py-2 border-bottom bg-light d-flex align-items-center gap-2 flex-wrap">
  <span class="fw-semibold text-muted small me-1">Ver cajero:</span>
  @foreach($cortes as $i => $c)
    @php $diff = (float)$c->diferencia_real; @endphp
    <button type="button"
            class="btn btn-sm btn-cajero-modal {{ $i===0 ? 'btn-dark' : 'btn-outline-dark' }}"
            data-idx="{{ $i }}">
      {{ $c->caja->nombre }}
      <span class="ms-1 badge {{ $diff==0 ? 'bg-success' : ($diff>0 ? 'bg-warning text-dark' : 'bg-danger') }}">
        {{ $diff==0 ? '✓' : ($diff>0 ? '+' : '−') }}
      </span>
    </button>
  @endforeach
</div>

{{-- Paneles por cajero --}}
@foreach($cortes as $i => $corte)
  @php
    $denMxn    = $corte->denominaciones->where('moneda','MXN')->keyBy('denominacion');
    $denUsd    = $corte->denominaciones->where('moneda','USD')->keyBy('denominacion');
    $denDot    = $corte->denominaciones->where('moneda','DOT')->keyBy('denominacion');
    $efectivoContado = (float) $denMxn->sum('monto') + (float) $denUsd->sum('monto') * ($corte->efectivo_dlls_tc ?: $tipoCambio);
    $registradoCajero = (float) $corte->total_efectivo - (float) $corte->dotacion + (float) $corte->cambio_entregado + (float) $corte->cambio_no_entregado;
    $diff      = (float)$corte->diferencia_real;
    $colorDiff = $diff==0 ? 'bg-success text-white' : ($diff>0 ? 'bg-warning text-dark' : 'bg-danger text-white');
    $totalBil  = 0; $totalMon = 0; $totalUsd = 0;
  @endphp

  <div class="panel-modal p-3" id="panel-modal-{{ $i }}" style="{{ $i!==0 ? 'display:none' : '' }}">

    {{-- Encabezado cajero --}}
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
      <div class="fw-bold fs-6">
        {{ strtoupper($corte->caja->nombre) }}
        <span class="text-muted fs-6 fw-normal">({{ $corte->caja->codigo }})</span>
      </div>
      <a href="{{ route('cortes.create') }}?fecha={{ $fecha }}&caja_id={{ $corte->id }}"
         class="btn btn-sm btn-outline-warning py-0">
        <i class="bi bi-pencil me-1"></i>Editar
      </a>
    </div>

    <div class="row g-3" style="font-size:.87rem">

      {{-- Columna izquierda --}}
      <div class="col-md-6">

        <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem;letter-spacing:.06em">
          <i class="bi bi-cash-stack me-1"></i>Ventas
        </div>
        <table class="table table-sm table-borderless mb-3">
          <tr><td class="text-muted">Total Ventas</td><td class="text-end fw-semibold">${{ number_format($corte->total_ventas,2) }}</td></tr>
          <tr><td class="text-muted">Tarjetas ({{ $corte->num_pagos_tarjeta }} pagos)</td><td class="text-end">${{ number_format($corte->importe_tarjeta,2) }}</td></tr>
          <tr><td class="text-muted">Efectivo MXN</td><td class="text-end">${{ number_format($corte->efectivo_mxn,2) }}</td></tr>
          @if($corte->efectivo_dlls_cantidad > 0)
          <tr><td class="text-muted">Efectivo DLLS ({{ $corte->efectivo_dlls_cantidad }} × ${{ number_format($corte->efectivo_dlls_tc,2) }})</td><td class="text-end">${{ number_format($corte->efectivo_dlls_importe,2) }}</td></tr>
          @endif
          <tr class="table-light"><td class="fw-semibold">Total Efectivo</td><td class="text-end fw-semibold">${{ number_format($corte->total_efectivo,2) }}</td></tr>
          <tr class="fw-bold border-top"><td>TOTAL DE VENTA</td><td class="text-end">${{ number_format($corte->total_de_venta,2) }}</td></tr>
        </table>

        <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem;letter-spacing:.06em">
          <i class="bi bi-arrow-down-circle me-1"></i>Egresos
        </div>
        <table class="table table-sm table-borderless mb-3">
          <tr><td class="text-muted">Saldo Final</td><td class="text-end">${{ number_format($corte->saldo_dispensador,2) }}</td></tr>
          <tr><td class="text-muted">Dotación</td><td class="text-end">${{ number_format($corte->dotacion,2) }}</td></tr>
          <tr><td class="text-muted">Dotación Final</td><td class="text-end">${{ number_format($corte->dotacion_final,2) }}</td></tr>
          <tr><td class="text-muted">Dotación Fija</td><td class="text-end">${{ number_format($corte->dotacion_determinada,2) }}</td></tr>
          <tr><td class="text-muted">Entregada</td><td class="text-end">${{ number_format($corte->dotacion_diferencia,2) }}</td></tr>
          <tr><td class="text-muted">Pagos Cancelados</td><td class="text-end">${{ number_format($corte->pagos_cancelados,2) }}</td></tr>
          <tr class="table-light fw-semibold"><td>Total Egresos</td><td class="text-end">${{ number_format($corte->total_egresos,2) }}</td></tr>
        </table>

        {{-- Desglose de Dotación --}}
        @if($denDot->sum('cantidad') > 0)
        <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem;letter-spacing:.06em">
          <i class="bi bi-cash-coin me-1"></i>Desglose de Dotación
        </div>
        <div class="mb-3 p-2 rounded" style="background:#fff7ed">
          @php
            $densDot = ['db50'=>['lbl'=>'B. $50','val'=>50],'db20'=>['lbl'=>'B. $20','val'=>20],'dm5'=>['lbl'=>'M. $5','val'=>5],'dm1'=>['lbl'=>'M. $1','val'=>1]];
            $totalDot = 0;
          @endphp
          <div class="row g-1">
            @foreach($densDot as $k => $info)
              @php $cant = $denDot[$k]->cantidad ?? 0; $totalDot += $cant*$info['val']; @endphp
              @if($cant > 0)
              <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                <span class="fw-semibold">${{ number_format($cant*$info['val'],2) }}</span>
              </div>
              @endif
            @endforeach
          </div>
          <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
            <span>Total Dotación</span><span>${{ number_format($totalDot,2) }}</span>
          </div>
        </div>
        @endif

        <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem;letter-spacing:.06em">
          <i class="bi bi-arrow-left-right me-1"></i>Cambios Entregados
        </div>
        <table class="table table-sm table-borderless mb-3">
          <tr><td class="text-muted">Cambio Entregado</td><td class="text-end">${{ number_format($corte->cambio_entregado,2) }}</td></tr>
          <tr><td class="text-muted">Cambio No Entregado</td><td class="text-end">${{ number_format($corte->cambio_no_entregado,2) }}</td></tr>
          <tr class="table-light fw-semibold"><td>Saldo en Cambio</td><td class="text-end">${{ number_format($corte->saldo_cambio_entregado,2) }}</td></tr>
          @if($corte->referencia_cambio)
          <tr><td colspan="2" class="text-muted fst-italic small pt-0"><i class="bi bi-chat-left-text me-1"></i>{{ $corte->referencia_cambio }}</td></tr>
          @endif
        </table>

      </div>

      {{-- Columna derecha --}}
      <div class="col-md-6">

        {{-- Denominaciones MXN --}}
        @php
          $hayBil = false;
          foreach($billetesMxn as $k => $info) { if(($denMxn[$k]->cantidad ?? 0) > 0) { $hayBil = true; break; } }
        @endphp
        @if($hayBil)
        <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem;letter-spacing:.06em">
          <i class="bi bi-currency-exchange me-1"></i>Denominaciones MXN
        </div>
        <div class="mb-3 p-2 rounded" style="background:#f0fdf4">
          <div class="row g-1">
            @foreach($billetesMxn as $k => $info)
              @php $cant = $denMxn[$k]->cantidad ?? 0; if($info['val']>=10||in_array($k,['b1','b2','b5'])) $totalBil += $cant*$info['val']; else $totalMon += $cant*$info['val']; @endphp
              @if($cant > 0)
              <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                <span class="fw-semibold">${{ number_format($cant*$info['val'],2) }}</span>
              </div>
              @endif
            @endforeach
          </div>
          @php
            $totalBil = 0; $totalMon = 0;
            $bilKeys = ['b500','b200','b100','b50','b20','b10','b5','b2','b1'];
            $monKeys = ['m10','m5','m2','m1'];
            foreach($bilKeys as $k) { $totalBil += ($denMxn[$k]->cantidad ?? 0) * $billetesMxn[$k]['val']; }
            foreach($monKeys as $k) { $totalMon += ($denMxn[$k]->cantidad ?? 0) * $billetesMxn[$k]['val']; }
          @endphp
          <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
            <span>Total Billetes</span><span>${{ number_format($totalBil,2) }}</span>
          </div>
          <div class="d-flex justify-content-between small fw-semibold">
            <span>Total Monedas</span><span>${{ number_format($totalMon,2) }}</span>
          </div>
        </div>
        @endif

        {{-- Denominaciones USD --}}
        @php $hayUsd = false; foreach($billetesUsd as $k=>$info){ if(($denUsd[$k]->cantidad??0)>0){$hayUsd=true;break;} } @endphp
        @if($hayUsd)
        <div class="mb-1 text-uppercase text-muted fw-semibold" style="font-size:.72rem;letter-spacing:.06em">
          <i class="bi bi-currency-dollar me-1"></i>Denominaciones USD
          <span class="fw-normal text-muted">(TC ${{ number_format($tipoCambio,2) }})</span>
        </div>
        <div class="mb-3 p-2 rounded" style="background:#eff6ff">
          <div class="row g-1">
            @foreach($billetesUsd as $k => $info)
              @php $cant = $denUsd[$k]->cantidad ?? 0; $totalUsd += $cant*$info['val']; @endphp
              @if($cant > 0)
              <div class="col-6 d-flex justify-content-between" style="font-size:.8rem">
                <span class="text-muted">{{ $info['lbl'] }} × {{ $cant }}</span>
                <span class="fw-semibold">${{ number_format($cant*$info['val'],2) }} USD</span>
              </div>
              @endif
            @endforeach
          </div>
          <div class="d-flex justify-content-between mt-1 pt-1 border-top small fw-semibold">
            <span>Total USD</span>
            <span>${{ number_format($totalUsd,2) }} = ${{ number_format($totalUsd*$tipoCambio,2) }} MXN</span>
          </div>
        </div>
        @endif

        {{-- Cierre (Diferencia Cajero Interlogic) --}}
        <div class="p-3 rounded fw-semibold {{ $colorDiff }} mt-2">
          <div class="d-flex justify-content-between mb-1">
            <span>Registrado en Cajero</span><span>${{ number_format($registradoCajero,2) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Reportado por Operador</span><span>${{ number_format($efectivoContado,2) }}</span>
          </div>
          <div class="d-flex justify-content-between fs-6 fw-bold border-top pt-2">
            <span>DIFERENCIA</span><span>${{ number_format($diff,2) }}</span>
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

      </div>
    </div>{{-- row --}}
  </div>{{-- panel-modal --}}
@endforeach

{{-- Los tabs de cajero se manejan por delegación en cortes/modal/detalle-dia.blade.php
     (el <script> inyectado con innerHTML no se ejecutaría aquí). --}}
