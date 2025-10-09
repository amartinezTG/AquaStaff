  @include('layout.shared')

<body class="toggle-sidebar">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

  @include('layout.nav-header')

  </header><!-- End Header -->

  
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Indicadores</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">Indicadores</li>
          <li class="breadcrumb-item active">Operativos</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->


    <section class="section dashboard">

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

                <form class="row g-3" method="POST" action="{{ route('indicadores') }}">                
                  @csrf
                  <div class="col-md-5">
                    <label class="col-sm-6 col-form-label">Fecha Inicio</label>
                    <input type="datetime-local" class="form-control" name="startDate" value="{{ $startDate }}">
                  </div>

                  <div class="col-md-5">
                    <label class="col-sm-4 col-form-label">Fecha Final</label>
                    <input type="datetime-local" class="form-control" name="endDate" value="{{ $endDate }}">
                  </div>  

                  <div class="col-md-2"><label class="col-sm-4 col-form-label">&nbsp;</label>
                    <button class="btn btn-warning w-100 submitBtn" tabindex="6" type="submit">Consultar</button>
                  </div>                
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">

        <div class="col-12">
          <div class="card">
            <div class="col-12">

              <div class="card-body">

<p>
<a href="{{ route('exportar-indicadores', ['startDate' => request('startDate'), 'endDate' => request('endDate')]) }}" class="btn btn-success" target="_blank">Descargar XLS</a>
- 
<a href="{{ route('indicadores_operativos_pdf', ['startDate' => request('startDate'), 'endDate' => request('endDate')]) }}" class="btn btn-info text-white" target="_blank">Descargar PDF</a>
</p>
<div class="table-responsive">
<table class="table table-striped">
  <thead>
    <tr>
        <th colspan="20" class=" mb-2 bg-info text-white text-center">INDICADORES OPERATIVOS</th>
    </tr>
    <tr>
        <th colspan="2"></th>
        <th colspan="8" class="text-dark" style="background-color: #e0ebf6;">Lavados de vehículo por paquete</th>
        <th colspan="6" class=" text-dark" style="background-color: #e4efdc;" >Lavados por memebresía</th>
        <th colspan="5" class=" text-dark" style="background-color: #f5ebca;" >Ingresos</th>

    </tr>
    </thead>
    <tr class="text-white" style="background-color: #9c9898;" >
      <th>Fecha</th>
      <th>Total Vehículos Lavados</th>

      <!--Lavados de vehículo por paquete -->
      <th>Express</th>
      <th>Básico</th>
      <th>Ultra</th>
      <th>Deluxe</th>
      
     
      <th>Promo $50</th>
      <th>Promo $150</th>
      <th>Promo $200</th>
      <th>Total x Paquete</th>


      <!--Lavados por memebresía -->
      <th>Express</th>
      <th>Básico</th>
      <th>Ultra</th>
      <th>Deluxe</th>
      <th>Total Membresía</th>

      <th>Lavados No Contabilizados</th>


      <!--Ingresos -->
      <th>Ingresos Membresías</th>
      <th>Ingresos Domiciliaciones</th>
      <th>Total Ingresos</th>

      <th>Total Sin IVA</th>
      <th>Ticket Promedio</th>
    </tr>
  
  <tbody>
    @php
      $start = \Carbon\Carbon::parse($startDate);
      $end   = \Carbon\Carbon::parse($endDate);

      // Totales
        $totalVehiculos     = 0;
        $pkgExpress         = $pkgBasico = $pkgUltra = $pkgDeluxe = 0;
        $promo50            = $promo150 = $promo200 = 0;
        $membExpress        = $membBasico = $membUltra = $membDeluxe = 0;
        $ingMembresias      = $ingDomiciliaciones = $ingTotal = $ingSinIVA = $ticketSum = 0;
        $ticketCount        = 0;
        $lavadosNoContadosTot=0;

        //$start = \Carbon\Carbon::parse($startDate);
        //$end   = \Carbon\Carbon::parse($endDate);

    @endphp

    @for ($date = $start->copy(); $date->lte($end); $date->addDay())

    @php

        $formattedDate = $date->locale('es')->isoFormat('ddd/DD/MM/YYYY');
        $fechaStr      = $date->toDateString();
        
        // Lavados por día
        $totTransreg   = $totTrans->get($fechaStr);
        $lavadosXdiaLT = $totTransreg->ids_distintos ?? 0;   // Transactions

        //echo $totLavsXMembOrders[$fechaStr] ?? null .' ---  <br>';
        $totLavsXMembOrdersT = $totLavsXMembOrders[$fechaStr] ?? null; // Orders

        // Datos Transactions
        $registro = $datos[$fechaStr] ?? null;

        // Datos Orders
        $usoMembresia = $usoMembresiasPorDia[$fechaStr] ?? null;

        

        // Vehículos
        $totalVehiculos = $totalVehiculos+$lavadosXdiaLT;

        // Paquetes
        $pkgExpress += $registro->Paquete_Express ?? 0;
        $pkgBasico  += $registro->Paquete_Basico ?? 0;
        $pkgUltra   += $registro->Paquete_Ultra ?? 0;
        $pkgDeluxe  += $registro->Paquete_Deluxe ?? 0;

        // Promos
        $promo50  += $registro->Paquetes_50 ?? 0;
        $promo150 += $registro->Paquetes_150 ?? 0;
        $promo200 += $registro->Paquetes_200 ?? 0;

        // Membresías usadas
        $membExpress += $usoMembresia->Uso_Membresia_Express ?? 0;
        $membBasico  += $usoMembresia->Uso_Membresia_Basico ?? 0;
        $membUltra   += $usoMembresia->Uso_Membresia_Ultra ?? 0;
        $membDeluxe  += $usoMembresia->Uso_Membresia_Delux ?? 0;

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

        // Ticket promedio acumulado
        if (!empty($registro->Ticket_Promedio)) {
          $ticketSum += $registro->Ticket_Promedio;
          $ticketCount++;
        }

        $lavadosNoContados = max(0, $lavadosXdiaLT - (
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

        $lavadosNoContadosTot=$lavadosNoContadosTot+$lavadosNoContados;
        @endphp
        <tr>
            <td>{{ $formattedDate }}</td>
            
            <td><!--Orders:--> {{ $totLavsXMembOrdersT }}<br>
           
            <!--Trans: {{$lavadosXdiaLT}}--> 
            </td>



            <!-- Lavado de vehículos por paquete -->
            <td>{{ $registro->Paquete_Express ?? 0 }}</td> {{-- Express --}}
            <td>{{ $registro->Paquete_Basico ?? 0 }}</td> {{-- Basico --}}
            <td>{{ $registro->Paquete_Ultra ?? 0 }}</td> {{-- Ultra --}}
            <td>{{ $registro->Paquete_Deluxe ?? 0 }}</td> {{-- Delux --}}
            

            <td>{{ $registro->Paquetes_50 ?? 0 }}</td> {{-- Promo $50 --}}
            <td>{{ $registro->Paquetes_150 ?? 0 }}</td> {{-- Promo $150 --}}
            <td>{{ $registro->Paquetes_200 ?? 0 }}</td> {{-- Promo $200 --}}
            <td>{{ ($registro->Paquete_Express ?? 0)+($registro->Paquete_Basico ?? 0)+ ($registro->Paquete_Ultra ?? 0 )+ ($registro->Paquete_Deluxe ?? 0)+($registro->Paquetes_50 ?? 0)+($registro->Paquetes_150 ?? 0)+($registro->Paquetes_200 ?? 0) }}</td> {{-- Total x Paquete --}}

            <!-- Lavados por membresía-->
            <td>{{ $usoMembresia->Uso_Membresia_Express  ?? 0}}</td> {{-- Express --}}
            <td>{{ $usoMembresia->Uso_Membresia_Basico ?? 0 }}</td> {{-- Basico --}}
            <td>{{ $usoMembresia->Uso_Membresia_Ultra ?? 0 }}</td> {{-- Ultra --}}
            <td>{{$usoMembresia->Uso_Membresia_Delux ?? 0 }}</td> {{-- Deluxe --}}
            <td>{{ ($usoMembresia->Uso_Membresia_Express ?? 0)+($usoMembresia->Uso_Membresia_Basico ?? 0)+($usoMembresia->Uso_Membresia_Ultra ?? 0)+($usoMembresia->Uso_Membresia_Delux ?? 0)}}</td> 

            <td>{{$lavadosNoContados}}</td>
            <!--Ingresos -->
            <td>${{ number_format(($registro->Renovacion_Membresia_Express ?? 0)+($registro->Renovacion_Membresia_Basico ?? 0)+($registro->Renovacion_Membresia_Ultra ?? 0)+($registro->Ingresos_Membresia_Deluxe ?? 0),2) }}</td> {{-- Ingresos Membresías --}}
            <td>${{ number_format(($registro->Ingresos_Membresia_Express ?? 0)+($registro->Ingresos_Membresia_Basico ?? 0)+($registro->Ingresos_Membresia_Ultra ?? 0)+($registro->Renovacion_Membresia_Deluxe ?? 0),2) }}</td> {{-- Ingresos Domiciliaciones --}}
            <td>${{ number_format($registro->Total_Ingresos ?? 0,2) }}</td> {{-- Total Ingresos --}}
            <td>${{ number_format($registro->Ingresos_Sin_IVA ?? 0,2) }}</td> {{-- Total Sin IVA --}}
            <td>${{ number_format($registro->Ticket_Promedio ?? 0,2) }}</td> {{-- Ticket Promedio --}}
        </tr>
    @endfor

        <tr class="bg-light fw-bold">
          <td>Totales</td>
          <td>{{ number_format($totalVehiculos) }}</td>

          <td>{{ number_format($pkgExpress) }}</td>
          <td>{{ number_format($pkgBasico) }}</td>
          <td>{{ number_format($pkgUltra) }}</td>
          <td>{{ number_format($pkgDeluxe) }}</td>

          <td>{{ number_format($promo50) }}</td>
          <td>{{ number_format($promo150) }}</td>
          <td>{{ number_format($promo200) }}</td>
          <td>{{ number_format($promo50 + $promo150 + $promo200) }}</td>

          <td>{{ number_format($membExpress) }}</td>
          <td>{{ number_format($membBasico) }}</td>
          <td>{{ number_format($membUltra) }}</td>
          <td>{{ number_format($membDeluxe) }}</td>
          <td>{{ number_format($membExpress + $membBasico + $membUltra + $membDeluxe) }}</td>
          <td>{{$lavadosNoContadosTot}}</td>

          <td>${{ number_format($ingMembresias, 2) }}</td>
          <td>${{ number_format($ingDomiciliaciones, 2) }}</td>
          <td>${{ number_format($ingTotal, 2) }}</td>
          <td>${{ number_format($ingSinIVA, 2) }}</td>
          <td>
            ${{ number_format($ticketCount ? $ticketSum / $ticketCount : 0, 2) }}
          </td>
        </tr>
  </tbody>
</table>
</div>



                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
</section>
</main>
<!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span></span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ 
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>-->
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

@include('layout.footer')


</body>