<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'dashboard' ? '' : 'collapsed' }} " href="/dashboard">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        @if (auth()->user()->role == 1 or auth()->user()->role == 2)
            <li class="nav-item">
                <a class="nav-link {{ $activePage === 'vending' ? '' : 'collapsed' }}" href="/vending">
                    <i class="bi bi-safe2"></i>
                    <span>Vending Machine</span>
                </a>
            </li><!-- End Vending Machine Nav -->
        @endif

        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'membresias' ? '' : 'collapsed' }}" href="/membresias">
                <i class="bi bi-person-badge"></i>
                <span>Membresías</span>
            </a>
        </li><!-- End Membresías Nav -->


        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'cajero' ? '' : 'collapsed' }}" href="/cajero">
                <i class="bi bi-inboxes"></i>
                <span>Cajero </span>
            </a>
        </li><!-- End Cajero Nav -->


        @if (auth()->user()->role == 1 or auth()->user()->role == 2 or auth()->user()->role == 3)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#indicadores-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-bar-chart"></i><span>Indicadores</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="indicadores-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a class="nav-link {{ $activePage === 'indicadores' ? '' : 'collapsed' }}" href="/indicadores">
                            <i class="bi bi-receipt"></i><span>Indicadores</span></a>
                    </li>
                    <li>
                        <a class="nav-link {{ $activePage === 'indicadores_cajero' ? '' : 'collapsed' }}" href="/indicadores_cajero">
                            <i class="bi bi-receipt"></i><span>Indicadores Cajero</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ $activePage === 'membresias' ? '' : 'collapsed' }}"
                            {{-- href="/indicadores-membresias"><i class="bi bi-receipt"></i><span>Membresias</span> --}}
                            href="/indicadores_membresias"><i class="bi bi-receipt"></i><span>Membresias</span>
                        </a>
                    </li>
                </ul>
        @endif


        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'corte_caja' ? '' : 'collapsed' }}" href="/corte_caja">
                <i class="bi bi-receipt"></i>
                <span>Corte de Caja</span>
            </a>
        </li><!-- End Corte Caja -->

        @if (auth()->user()->role == 1 or auth()->user()->role == 2)
            <li class="nav-item">
                <a class="nav-link {{ $activePage === 'caja_chica' ? '' : 'collapsed' }}" href="/caja_chica">
                    <i class="bi bi-receipt"></i>
                    <span>Caja Chica</span>
                </a>
            </li><!-- End Caja Chica -->
        @endif




        <!--<li class="nav-item">
        <a class="nav-link {{ $activePage === 'inventarios' ? '' : 'collapsed' }}" href="/inventarios">
          <i class="bi bi-tags"></i>
          <span>Control de Inventarios</span>
        </a>
      </li> End Control de Inventarios Nav -->

        <!-- @if (auth()->user()->role == 1 or auth()->user()->role == 2)
<li class="nav-item">
        <a class="nav-link {{ $activePage === 'facturacion' ? '' : 'collapsed' }}" href="/facturacion">
          <i class="bi bi-receipt"></i>
          <span>Facturación</span>
        </a>
      </li>
@endif -->

        <!--<li class="nav-item">
        <a class="nav-link {{ $activePage === 'inventarios' ? '' : 'collapsed' }}" href="/inventarios">
          <i class="bi bi-receipt"></i>
          <span>Inventarios</span>
        </a>
      </li>-->

        @if (auth()->user()->role == 1 or auth()->user()->role == 2)
            <li class="nav-item">
                <a class="nav-link {{ $activePage === 'compaq' ? '' : 'collapsed' }}" href="/compaq">
                    <i class="bi bi-code-square"></i>
                    <span>Facturación</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role == 1 or auth()->user()->role == 2 or auth()->user()->role == 3)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-bar-chart"></i><span>Inventarios</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a class="nav-link {{ $activePage === 'transferencias' ? '' : 'collapsed' }}"
                            href="/transferencias">
                            <i class="bi bi-receipt"></i>
                            <span>Transferencias</span>
                        </a>
                    </li>
                </ul>
        @endif

        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
                <a class="nav-link {{ $activePage === 'productos' ? '' : 'collapsed' }}" href="/productos">
                    <i class="bi bi-receipt"></i>
                    <span>Productos</span>
                </a>
            </li>
        </ul>
        </li>

        <!-- End Membresías Nav -->



        @if (auth()->user()->role == 1)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#configuracion-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-bar-chart"></i><span>Configuración </span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="configuracion-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">



                    <li>
                        <a href="/usuarios">
                            <i class="bi bi-circle"></i><span>Usuarios </span>
                        </a>
                    </li>


                    <li>
                        <a href="/tipo_de_cambio">
                            <i class="bi bi-circle"></i><span>Tipo de Cambio</span>
                        </a>
                    </li>
                    <!--<li>
            <a href="#">
              <i class="bi bi-circle"></i><span>Niveles de inventario</span>
            </a>
          </li>-->
                </ul>
            </li><!-- End Charts Nav -->
        @endif

        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'logoput' ? '' : 'collapsed' }}" href="/logout">
                <i class="bi bi-code-square"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>





    </ul>

</aside><!-- End Sidebar-->
