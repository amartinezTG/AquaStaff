@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            width: 700px;
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;

        }

        .logo img {
            max-width: 460px;
            display: block;
            margin: 0 auto;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .section-title {
            background-color: #253861;
            font-weight: bold;
            padding: 4px;
         
            color: #FFFFFF;
            text-align: center;
        }

        .section-subtitle {
            background-color: #244083;
            font-weight: bold;
            padding: 4px;
         
            color: #FFFFFF;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #244083;
            color: #FFFFFF;
        }
        .total-row {
            font-weight: bold;
            background-color: #244083;
            color: #FFFFFF;
        }
    </style>
</head>
<body>
    @php
    $logoPath = public_path('aqua-car-club-logo.png');
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoMime = mime_content_type($logoPath);
@endphp
    <div class="logo">
        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Logo">
    </div>
    <div class="section-title">CORTE DE CAJA</div>
    <div class="section-subtitle">{{ $corteCaja->sucursal }}</div>
    <table>
        <thead>
            <tr><th >FECHA:</th><th>{{ Carbon::parse($corteCaja->fecha_corte)->format('Y-m-d H:i:s') }} ({{ Carbon::parse($corteCaja->fecha_corte)->isoFormat('dddd') }})</th><th>RESPONSABLE:</th><th>{{ $responsable }}</th></tr>
        </thead>
    </table>

   
    {{-- Desglose de Paquetes --}}
    <div class="section-subtitle">DESGLOSE DE VENTAS POR PAQUETES</div>
    <table>
        <thead>
            <tr><th  style="text-align: left;">PAQUETES</th><th>CANTIDAD</th><th>IMPORTE</th></tr>
        </thead>
        <tbody>
            @foreach($ventasPorPaquete as $key => $val)
                <tr>
                    <td style="text-align: left;">{{ $catalogs->package_type[$key] ?? 'N/D' }}</td>
                    <td style="text-align: right;">{{ $val['total_transacciones'] }}</td>
                    <td style="text-align: right; width: 20%"> ${{ number_format($val['total_monto'], 2) }} </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td style="text-align: left;">Total</td>
                <td style="text-align: right;">{{ $ventasPorPaquete->sum('total_transacciones') }}</td>
                <td style="text-align: right;"> ${{ number_format($ventasPorPaquete->sum('total_monto'), 2) }} </td>
            </tr>
        </tbody>
    </table>
    <br>
    

    {{-- Lavado por Membresías y Nuevas Membresías --}}
    <div class="section-subtitle">LAVADO POR MEMBRESÍAS</div>
    <table>
        <thead>
            <tr><th style="text-align: left;">PAQUETES</th><th>CANTIDAD</th></tr>
        </thead>
        <tbody>
    @forelse($LavadosXMembresias as $item)
      <tr>
        <td style="text-align: left;">{{ $item['membership'] }}</td>
        <td style="text-align: right;">{{ $item['total'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="2">No se registraron órdenes en ese período.</td>
      </tr>
    @endforelse
    {{-- fila de totales --}}
    @if($LavadosXMembresias->isNotEmpty())
      <tr class="total-row">
        <td>Total</td>
        <td style="text-align:right">
          {{ $LavadosXMembresias->sum('total_transacciones') }}
        </td>
      </tr>
    @endif
        </tbody>
    </table>

<br>
    {{-- Desgloce de ventas por nuevas membresias --}}
    <div class="section-subtitle">DESGLOCE DE VENTAS POR NUEVAS MEMBRESIAS</div>
    <table>
        <thead>
            <tr><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr>
        </thead>
        <tbody>
            @forelse($MembresiasNuevas as $item)
              <tr>
                <td style="text-align: left;">{{ $item['membership'] }}</td>
                <td style="text-align: right;">{{ $item['qty'] }}</td>
                <td style="text-align: right;">${{ number_format($item['total'],2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="2" class="text-center">No hay nuevas membresías en este periodo.</td>
              </tr>
            @endforelse
            {{-- fila de totales --}}
    @if($MembresiasNuevas->isNotEmpty())
      <tr class="total-row">
        <td>Total</td>
        <td style="text-align:right">
          {{ $MembresiasNuevas->sum('qty') }}
        </td>
        <td style="text-align:right">
          ${{ number_format($MembresiasNuevas->sum('total'), 2) }}
        </td>
      </tr>
    @endif
        </tbody>
    </table>

@php
  $sumCantidad = 0;
  $sumImporte  = 0;
@endphp


    {{-- DESGLOSE DE TRANSACCIONES POR TIPO DE PAGO    ${{ number_format($corteCaja->tipo_cambio,2) }}                  --}}
    <div class="section-subtitle">DESGLOSE DE TRANSACCIONES POR TIPO DE PAGO  </div>
    <table>
        <thead>
            <tr><th>TIPO</th><th>CANTIDAD</th><th>TC</th><th>IMPORTE</th></tr>
        </thead>
        <tbody>
@foreach($ventasPorTipoPago as $data)
      <tr>
        <td style="text-align:left">{{ $data['label'] }}</td>
        <td style="text-align:right">{{ $data['total_transacciones'] }}</td>
        <td></td>
        <td style="text-align:right">${{ number_format($data['total_monto'],2) }}</td>
      </tr>
      @php
        $sumCantidad += $data['total_transacciones'];
        $sumImporte  += $data['total_monto'];
      @endphp
    @endforeach

    <tr>
        <td style="font-weight: bold; text-align:left; background-color: #a5b8e1;">Total Prosepago</td>
        <td style="background-color: #a5b8e1;"></td>
        <td style="background-color: #a5b8e1;"></td>
        <td style="text-align:right; background-color: #a5b8e1; font-weight: bold;">${{ number_format($corteCaja->dinero_acumulado_tarjeta,2) }} </td>
    </tr>

    <tr>
        <td style="text-align:left;">Efectivo MXN</td>
        <td style="text-align:left;"></td>
        <td></td>
        <td style="text-align:right;">  ${{ number_format($corteCaja->total_efectivo_en_mxn,2) }} </td>
    </tr>

    <tr>
        <td style="text-align:left;">Efectivo DLLS</td>
        <td></td>
        <td style="text-align:right;">${{ number_format($corteCaja->tipo_cambio,2) }}  </td>
        <td style="text-align:right;">${{ number_format( ($corteCaja->tipo_cambio*$corteCaja->dinero_acumulado_usd),2) }}  </td>
    </tr>

    <tr>
        <td style="font-weight: bold; text-align:left; background-color: #a5b8e1;">Total Efectivo</td>
        <td style="background-color: #a5b8e1;"></td>
        <td style="background-color: #a5b8e1;"></td>
        <td style="text-align:right; background-color: #a5b8e1; font-weight: bold;">${{ number_format( ($corteCaja->total_efectivo_en_mxn+($corteCaja->tipo_cambio*$corteCaja->dinero_acumulado_usd)),2) }}</td>
    </tr>

    <!--<tr class="total-row">
      <td>Total</td>
      <td></td>
      <td style="text-align:right">{{ $sumCantidad }}</td>
      <td style="text-align:right">${{ number_format($sumImporte,2) }}</td>
    </tr>-->
        </tbody>
    </table>

    {{-- DESGLOCE DE EGRESOS --}}
    <div class="section-title">DESGLOCE DE EGRESOS </div>
    <table>
        <thead><tr><th>EGRESO</th><th>REFERENCIA</th><th>IMPORTE</th></tr></thead>
        <tbody>

            <tr>
                <td style="text-align: left;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"> $</td>
            </tr>
             <tr>
                <td style="text-align: left;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"> $</td>
            </tr>
            <tr class="total-row">
                <td style="text-align: left;">Total</td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"> $</td>
            </tr>
        </tbody>
        </tbody>
    </table>


            {{-- Denominaciones --}}
    <div class="section-title">DENOMINACIONES </div>
    <div class="section-subtitle">DENOMINACIONES PESOS MEXICANOS</div>
    <table>
        <thead><tr><th>DENOMINACIONES</th><th></th><th>CANTIDAD</th><th>MONTO</th></tr></thead>
        <tbody>
           @php
            $sumCantidad = 0;
            $sumMonto    = 0;
        @endphp

        @foreach($denominacionesMXN as $den => $valor)
            @php
                // Elegimos "B." para billetes (>=10) y "M." para monedas (<10)
                $label = ($valor >= 10 ? 'B.' : 'M.') . ' ' . $valor;
                // buscamos la cantidad registrada, o 0 si no existe
                $cantidad = $detallesArqueo
                    ->firstWhere('denominacion', $den)
                    ->cantidad ?? 0;
                $monto = $cantidad * $valor;

                // vamos acumulando totales
                $sumCantidad += $cantidad;
                $sumMonto    += $monto;
            @endphp
            <tr>
                <td>{{ $label }}</td>
                <td></td>
                <td style="text-align: right;">{{ $cantidad }}</td>
                <td style="text-align: right;">${{ number_format($monto, 2) }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td>Total</td>
            <td></td>
            <td style="text-align: right;">{{ $sumCantidad }}</td>
            <td style="text-align: right;">${{ number_format($sumMonto, 2) }}</td>
        </tr>
        </tbody>
    </table>

    <div class="section-subtitle">DENOMINACIONES USD</div>
    @php
    $sumCantidadUsd = 0;
    $sumMontoUsd    = 0;
    $sumPesoEquiv   = 0;
@endphp

<table>
    <thead>
        <tr>
            <th>Denominación</th>
            <th>TC</th>
            <th>Cantidad</th>
            <th>USD</th>
            <th>MXN</th>
        </tr>
    </thead>
    <tbody>
        @foreach($denominacionesUSD as $den => $valor)
            @php
                // Etiqueta
                $label = ($valor >= 1 ? 'B.' : 'M.') 
                       . ' ' 
                       . ($valor >= 1 
                            ? number_format($valor, 0) 
                            : number_format($valor * 100, 0).'¢');
                // Cantidad registrada
                $cantidadUsd = $detallesArqueo
                    ->firstWhere('denominacion', $den)
                    ->cantidad ?? 0;
                // Monto en USD
                $montoUsd = $cantidadUsd * $valor;
                // Equivalente en MXN
                $pesoEquiv = $montoUsd * $corteCaja->tipo_cambio;

                // Acumular totales
                $sumCantidadUsd += $cantidadUsd;
                $sumMontoUsd    += $montoUsd;
                $sumPesoEquiv   += $pesoEquiv;
            @endphp
            <tr>
                <td>{{ $label }}</td>
                <td>{{ number_format($corteCaja->tipo_cambio, 2) }}</td>
                <td style="text-align: right;">{{ $cantidadUsd }}</td>
                <td style="text-align: right;">${{ number_format($montoUsd,    2) }}</td>
                <td style="text-align: right;">${{ number_format($pesoEquiv,   2) }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td>Total</td>
            <td></td>
            <td style="text-align: right;">{{ $sumCantidadUsd }}</td>
            <td style="text-align: right;">${{ number_format($sumMontoUsd, 2) }}</td>
            <td style="text-align: right;">${{ number_format($sumPesoEquiv, 2) }}</td>
        </tr>
    </tbody>
</table>





@php
$totalMxn  = (float) str_replace(',', '', $corteCaja->total_mxn);
$sumPeso   = (float) str_replace(',', '', $sumPesoEquiv);
$sumMonto  = (float) str_replace(',', '', $sumMonto);

$diferencia = ($corteCaja->total_efectivo_en_mxn-($sumPesoEquiv+$sumMonto));

@endphp


    <table>
        <tr><td style="text-align: left; background-color: #244083; color:white;">CORTE TOTAL EN EFECTIVO</td><td style="text-align: right; background-color: #a5b8e1;"> ${{ number_format(($sumPesoEquiv + $sumMonto), 2) }} </td></tr>
        <tr><td style="text-align: left; background-color: #244083; color:white;">DIFERENCIA DENOMINACIONES</td><td style="text-align: right; background-color: #a5b8e1;"> 
            @if($diferencia < 0)
                -${{ number_format(abs($diferencia), 2) }}
            @else
                ${{ number_format($diferencia, 2) }}
            @endif
        </td></tr>
        <tr><td style="text-align: left; background-color: #244083; color:white;">CORTE TOTAL </td><td style="text-align: right; background-color: #a5b8e1;"> ${{ number_format($corteCaja->total_ventas, 2) }} </td></tr>

        <tr><td style="text-align: left; background-color: #244083; color:white;">DIFERENCIAS </td><td style="text-align: right; background-color: #a5b8e1;"> ${{ number_format( ($corteCaja->total_ventas-$corteCaja->dinero_acumulado_tarjeta), 2) }} </td></tr>
        <tr><td style="text-align: left; background-color: #244083; color:white;">TICKET PROMEDIO</td><td style="text-align: right; background-color: #a5b8e1;"> ${{ number_format(($corteCaja->total_ventas/$corteCaja->total_tickets), 2) }} </td>

        <!--<tr><td>Total efectivo USD</td><td>- ${{ number_format($total_usd, 2) }} -</td></tr>
        <tr><td>Tipo de cambio</td><td>{{ $tipoCambio }}</td></tr>
        <tr><td>Total efectivo en MXN</td><td>- ${{ number_format($total_efectivo_en_mxn, 2) }} -</td></tr>
        <tr><td>Dinero recibido</td><td>- ${{ number_format($corteCaja->dinero_recibido, 2) }} -</td></tr>
        <tr><td>Diferencia</td><td>- ${{ number_format($corteCaja->dinero_recibido - $total_efectivo_en_mxn, 2) }} -</td></tr>-->
    </table>

    <table>
        <tr><td></td><td><br><br><br>________________________________________</td><td></td></tr>
        <tr><td></td><td>
            FIRMA DE RESPONSABLE
        </td><td></td></tr>
    </table>

    {{-- Comentarios --}}
    @if($Comentarios)
        <div class="section-subtitle">COMENTARIOS</div>
        <p>{{ $Comentarios }}</p>
    @endif

</body>
</html>