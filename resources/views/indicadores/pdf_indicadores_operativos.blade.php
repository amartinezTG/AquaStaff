<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Indicadores Operativos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        .bg-header {
            background-color: #00c3e3;
            color: white;
        }
        .bg-paquete {
            background-color: #e0ebf6;
        }
        .bg-membresia {
            background-color: #e4efdc;
        }
        .bg-ingresos {
            background-color: #f5ebca;
        }
        .bg-subheader {
            background-color: #9c9898;
            color: white;
        }
        .bg-total {
            background-color: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
  <thead>
    <tr>
        <th colspan="20" class="bg-header">INDICADORES OPERATIVOS</th>
    </tr>
    <tr>
        <th colspan="2"></th>
        <th colspan="8" class="bg-paquete">Lavados de vehículo por paquete</th>
        <th colspan="5" class="bg-membresia">Lavados por membresía</th>
        <th colspan="5" class="bg-ingresos">Ingresos</th>
    </tr>
    <tr class="bg-subheader">
        <th>Fecha</th>
        <th>Total Vehículos Lavados</th>
        <th>Express</th>
        <th>Básico</th>
        <th>Ultra</th>
        <th>Deluxe</th>
        <th>Promo $50</th>
        <th>Promo $150</th>
        <th>Promo $200</th>
        <th>Total x Paquete</th>
        <th>Express</th>
        <th>Básico</th>
        <th>Ultra</th>
        <th>Deluxe</th>
        <th>Total Membresía</th>
        <th>Lavados No Contabilizados</th>
        <th>Ingresos Membresías</th>
        <th>Ingresos Domiciliaciones</th>
        <th>Total Ingresos</th>
        <th>Total Sin IVA</th>
        <th>Ticket Promedio</th>
    </tr>
  </thead>
  <tbody>
    @php
      $start = \Carbon\Carbon::parse($startDate);
      $end   = \Carbon\Carbon::parse($endDate);
      $ticketSum = $ticketCount = 0;
      $lavadosNoContadosTot = 0;
      $totalVehiculos=0;
      $pkgExpress=0;
        $pkgBasico=0;
        $pkgUltra=0;
        $pkgDeluxe =0;
        $promo50  =0;
        $promo150  =0;
        $promo200  =0;

        // Membresías usadas
        $membExpress  =0;
        $membBasico =0;
        $membUltra =0;
        $membDeluxe =0;
         $ingMembresias=0;
 $ingDomiciliaciones=0;
 $ingTotal  =0;
        $ingSinIVA =0;

    @endphp

    @for ($date = $start->copy(); $date->lte($end); $date->addDay())
      @php
        $fechaStr = $date->toDateString();
        $formattedDate = $date->format('d/m/Y');
        $registro = $datos[$fechaStr] ?? null;
        $uso = $usoMembresiasPorDia[$fechaStr] ?? null;
        $lavados = $totTrans[$fechaStr]->ids_distintos ?? 0;
        $ticket = $registro->Ticket_Promedio ?? 0;
        if ($ticket) { $ticketSum += $ticket; $ticketCount++; }

        $totalVehiculos += $lavados;

        // Paquetes
        $pkgExpress += $registro->Paquete_Express ?? 0;
        $pkgBasico  += $registro->Paquete_Basico ?? 0;
        $pkgUltra   += $registro->Paquete_Ultra ?? 0;
        $pkgDeluxe  += $registro->Paquete_Deluxe ?? 0;

        // Promociones
        $promo50  += $registro->Paquetes_50 ?? 0;
        $promo150 += $registro->Paquetes_150 ?? 0;
        $promo200 += $registro->Paquetes_200 ?? 0;

        // Membresías usadas
        $membExpress += $uso->Uso_Membresia_Express ?? 0;
        $membBasico  += $uso->Uso_Membresia_Basico ?? 0;
        $membUltra   += $uso->Uso_Membresia_Ultra ?? 0;
        $membDeluxe  += $uso->Uso_Membresia_Delux ?? 0;


        // Ingresos
        $ingMembresias += 
            ($registro->Renovacion_Membresia_Express ?? 0) +
            ($registro->Renovacion_Membresia_Basico ?? 0) +
            ($registro->Renovacion_Membresia_Ultra ?? 0) +
            ($registro->Ingresos_Membresia_Deluxe ?? 0);

        $ingDomiciliaciones += 
            ($registro->Ingresos_Membresia_Express ?? 0) +
            ($registro->Ingresos_Membresia_Basico ?? 0) +
            ($registro->Ingresos_Membresia_Ultra ?? 0) +
            ($registro->Renovacion_Membresia_Deluxe ?? 0);

        $ingTotal   += $registro->Total_Ingresos ?? 0;
        $ingSinIVA  += $registro->Ingresos_Sin_IVA ?? 0;


        //$totalVehiculos = $pkgExpress = $pkgBasico = $pkgUltra = $pkgDeluxe = 0;
        //$promo50        = $promo150 = $promo200 = 0;
        //$membExpress    = $membBasico = $membUltra = $membDeluxe = 0;
        //$ingMembresias  = $ingDomiciliaciones = $ingTotal = $ingSinIVA = 0;


        $lavadosNoContados = max(0, $lavados - (
          ($registro->Paquete_Express ?? 0) +
          ($registro->Paquete_Basico ?? 0) +
          ($registro->Paquete_Ultra ?? 0) +
          ($registro->Paquete_Deluxe ?? 0) +
          ($registro->Paquetes_50 ?? 0) +
          ($registro->Paquetes_150 ?? 0) +
          ($registro->Paquetes_200 ?? 0) +
          ($usoMembresia->Uso_Membresia_Express ?? 0) +
          ($usoMembresia->Uso_Membresia_Basico ?? 0) +
          ($usoMembresia->Uso_Membresia_Ultra ?? 0) +
          ($usoMembresia->Uso_Membresia_Delux ?? 0)
        ));


        $lavadosNoContadosTot += $lavadosNoContados;


      @endphp
      <tr>
        <td>{{ $formattedDate }}</td>
        <td>{{ $lavados }}</td>
        <td>{{ $registro->Paquete_Express ?? 0 }}</td>
        <td>{{ $registro->Paquete_Basico ?? 0 }}</td>
        <td>{{ $registro->Paquete_Ultra ?? 0 }}</td>
        <td>{{ $registro->Paquete_Deluxe ?? 0 }}</td>
        <td>{{ $registro->Paquetes_50 ?? 0 }}</td>
        <td>{{ $registro->Paquetes_150 ?? 0 }}</td>
        <td>{{ $registro->Paquetes_200 ?? 0 }}</td>
        <td>{{ ($registro->Paquete_Express ?? 0)+($registro->Paquete_Basico ?? 0)+ ($registro->Paquete_Ultra ?? 0 )+ ($registro->Paquete_Deluxe ?? 0)+($registro->Paquetes_50 ?? 0)+($registro->Paquetes_150 ?? 0)+($registro->Paquetes_200 ?? 0) }}</td>
        <td>{{ $uso->Uso_Membresia_Express ?? 0 }}</td>
        <td>{{ $uso->Uso_Membresia_Basico ?? 0 }}</td>
        <td>{{ $uso->Uso_Membresia_Ultra ?? 0 }}</td>
        <td>{{ $uso->Uso_Membresia_Delux ?? 0 }}</td>
        <td>{{ ($uso->Uso_Membresia_Express ?? 0)+($uso->Uso_Membresia_Basico ?? 0)+($uso->Uso_Membresia_Ultra ?? 0)+($uso->Uso_Membresia_Delux ?? 0) }}</td>
        <td>{{ $lavadosNoContados }}</td>
        <td>${{ number_format(($registro->Renovacion_Membresia_Express ?? 0)+($registro->Renovacion_Membresia_Basico ?? 0)+($registro->Renovacion_Membresia_Ultra ?? 0)+($registro->Ingresos_Membresia_Deluxe ?? 0),2) }}</td>
        <td>${{ number_format(($registro->Ingresos_Membresia_Express ?? 0)+($registro->Ingresos_Membresia_Basico ?? 0)+($registro->Ingresos_Membresia_Ultra ?? 0)+($registro->Renovacion_Membresia_Deluxe ?? 0),2) }}</td>
        <td>${{ number_format($registro->Total_Ingresos ?? 0, 2) }}</td>
        <td>${{ number_format($registro->Ingresos_Sin_IVA ?? 0, 2) }}</td>
        <td>${{ number_format($ticket, 2) }}</td>
      </tr>
    @endfor

    <tr class="bg-total">
      <td>Totales</td>
      <td>{{ number_format($totalVehiculos) }}</td>
<td>{{ number_format($pkgExpress) }}</td>
<td>{{ number_format($pkgBasico) }}</td>
<td>{{ number_format($pkgUltra) }}</td>
<td>{{ number_format($pkgDeluxe) }}</td>
<td>{{ number_format($promo50) }}</td>
<td>{{ number_format($promo150) }}</td>
<td>{{ number_format($promo200) }}</td>
<td>{{ number_format($promo50 + $promo150 + $promo200 + $pkgExpress + $pkgBasico + $pkgUltra + $pkgDeluxe) }}</td>
<td>{{ number_format($membExpress) }}</td>
<td>{{ number_format($membBasico) }}</td>
<td>{{ number_format($membUltra) }}</td>
<td>{{ number_format($membDeluxe) }}</td>
<td>{{ number_format($membExpress + $membBasico + $membUltra + $membDeluxe) }}</td>
<td>{{ number_format($lavadosNoContadosTot) }}</td>
<td>${{ number_format($ingMembresias, 2) }}</td>
<td>${{ number_format($ingDomiciliaciones, 2) }}</td>
<td>${{ number_format($ingTotal, 2) }}</td>
<td>${{ number_format($ingSinIVA, 2) }}</td>
<td>${{ number_format($ticketCount ? $ticketSum / $ticketCount : 0, 2) }}</td>
    </tr>
  </tbody>
</table>

</body>
</html>
