<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">    
            <a class="nav-link {{ $activePage === 'dashboard' ? '' : 'collapsed' }} " href="/dashboard">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <!-- <li class="nav-item">
            <a class="nav-link {{ $activePage === 'vending' ? '' : 'collapsed' }}" href="/vending">
                <i class="bi bi-safe2"></i>
                <span>Vending Machine</span>
            </a>
        </li>End Vending Machine Nav -->



        <!-- <li class="nav-item">
            <a class="nav-link {{ $activePage === 'membresias' ? '' : 'collapsed' }}" href="/membresias">
                <i class="bi bi-person-badge"></i>
                <span>Membresías</span>
            </a>
        </li> -->
         <li class="nav-item">
            <a class="nav-link {{ $activePage === 'membresia_cajero' ? '' : 'collapsed' }}" href="/membresias/cajero"><i class="bi bi-person-badge"></i><span>Membresías Cajero</span>
            </a>
        </li>
        <!-- End Membresías Nav -->


        {{-- <li class="nav-item">
            <a class="nav-link {{ $activePage === 'cajero' ? '' : 'collapsed' }}" href="/cajero">
                <i class="bi bi-inboxes"></i>s
                <span>Cajero </span>
            </a>
        </li><!-- End Cajero Nav --> --}}

        <li class="nav-item">
            <a class="nav-link {{ in_array($activePage, ['cajero', 'cajero_transacciones', 'importacion', 'analisis_procepago']) ? '' : 'collapsed' }}" data-bs-target="#cajero-nav" data-bs-toggle="collapse"
                href="#">
                <i class="bi bi-inboxes"></i><span>Cajero</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="cajero-nav" class="nav-content collapse {{ in_array($activePage, ['cajero', 'cajero_transacciones', 'importacion', 'analisis_procepago']) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a class="nav-link {{ $activePage === 'cajero' ? '' : 'collapsed' }}" href="/cajero">
                        <i class="bi bi-receipt"></i><span>Cajero</span></a>
                </li>
                <li>
                    <a class="nav-link {{ $activePage === 'cajero_transacciones' ? '' : 'collapsed' }}" href="/cajero_transacciones">
                        <i class="bi bi-receipt"></i><span>Transacciones</span></a>
                </li>
                <li>
                    <a class="nav-link {{ $activePage === 'importacion' ? '' : 'collapsed' }}" href="/cajero/importacion">
                        <i class="bi bi-cloud-upload"></i><span>Importación</span></a>
                </li>
                <li>
                    <a class="nav-link {{ $activePage === 'analisis_procepago' ? '' : 'collapsed' }}" href="/cajero/analisis-procepago">
                        <i class="bi bi-bar-chart-steps"></i><span>Análisis Procepago</span></a>
                </li>

            </ul>
        </li>


        @if (auth()->user()->role == 1 or auth()->user()->role == 2 or auth()->user()->role == 3)
            <li class="nav-item">
                <a class="nav-link {{ in_array($activePage, ['indicadores', 'indicadores_cajero', 'indicadores_membresias', 'indicadores_clientes']) ? '' : 'collapsed' }}" data-bs-target="#indicadores-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-bar-chart"></i><span>Indicadores</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="indicadores-nav" class="nav-content collapse {{ in_array($activePage, ['indicadores', 'indicadores_cajero', 'indicadores_membresias', 'indicadores_clientes']) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                    <li>
                        <a class="nav-link {{ $activePage === 'indicadores' ? '' : 'collapsed' }}" href="/indicadores">
                            <i class="bi bi-receipt"></i><span>Indicadores</span></a>
                    </li>
                    <li>
                        <a class="nav-link {{ $activePage === 'indicadores_cajero' ? '' : 'collapsed' }}" href="/indicadores_cajero">
                            <i class="bi bi-receipt"></i><span>Indicadores Cajero</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ $activePage === 'indicadores_membresias' ? '' : 'collapsed' }}"
                            href="/indicadores_membresias"><i class="bi bi-receipt"></i><span>Indicadores Membresías</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link {{ $activePage === 'indicadores_clientes' ? '' : 'collapsed' }}" href="{{ route('indicadores.clientes') }}">
                            <i class="bi bi-people"></i><span>Clientes</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

   
        @if (auth()->user()->role == 1 or auth()->user()->role == 2)
        <li class="nav-item">
            <a class="nav-link {{ in_array($activePage, ['procepago_importacion', 'procepago_domiciliaciones']) ? '' : 'collapsed' }}"
                data-bs-target="#procepago-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bank"></i><span>Procepago</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="procepago-nav" class="nav-content collapse {{ in_array($activePage, ['procepago_importacion', 'procepago_domiciliaciones']) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a class="nav-link {{ $activePage === 'procepago_importacion' ? '' : 'collapsed' }}" href="/procepago/importacion">
                        <i class="bi bi-cloud-upload"></i><span>Importación Depósitos</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ $activePage === 'procepago_domiciliaciones' ? '' : 'collapsed' }}" href="/procepago/domiciliaciones">
                        <i class="bi bi-arrow-repeat"></i><span>Importación Domiciliaciones</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        {{-- <li class="nav-item">
            <a class="nav-link {{ $activePage === 'corte_caja' ? '' : 'collapsed' }}" href="/corte_caja">
                <i class="bi bi-receipt"></i>
                <span>Corte de Caja</span>
            </a>
        </li> --}}{{-- End Corte Caja --}}

        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'cortes' ? '' : 'collapsed' }}" href="{{ route('cortes.index') }}">
                <i class="bi bi-journal-check"></i>
                <span>Cortes por Cajero</span>
            </a>
        </li><!-- End Cortes por Cajero -->

        {{-- @if (auth()->user()->role == 1 or auth()->user()->role == 2)
            <li class="nav-item">
                <a class="nav-link {{ $activePage === 'caja_chica' ? '' : 'collapsed' }}" href="/caja_chica">
                    <i class="bi bi-receipt"></i>
                    <span>Caja Chica</span>
                </a>
            </li>
        @endif --}}{{-- End Caja Chica --}}




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

        @if (auth()->user()->role == 1 or auth()->user()->role == 2 or auth()->user()->role == 3)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#facturacion-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-receipt"></i><span>Facturación</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="facturacion-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    @if (auth()->user()->role == 1 or auth()->user()->role == 2)
                    <li>
                        <a class="nav-link {{ $activePage === 'facturacion' ? '' : 'collapsed' }}" href="/facturacion">
                            <i class="bi bi-file-earmark-text"></i><span>Factura Global</span>
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="nav-link {{ $activePage === 'facturacion_individual' ? '' : 'collapsed' }}" href="/facturacion-individual">
                            <i class="bi bi-person-lines-fill"></i><span>Factura Individual</span>
                        </a>
                    </li>
                    @if (auth()->user()->role == 1 or auth()->user()->role == 2)
                    <li>
                        <a class="nav-link {{ $activePage === 'compaq' ? '' : 'collapsed' }}" href="/compaq">
                            <i class="bi bi-code-square"></i><span>Compaq (anterior)</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
        @endif

        @if (auth()->user()->role == 1 or auth()->user()->role == 2 or auth()->user()->role == 3)
            <li class="nav-item">
                <a class="nav-link {{ in_array($activePage, ['transferencias', 'productos']) ? '' : 'collapsed' }}" data-bs-target="#inventarios-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-bar-chart"></i><span>Inventarios</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="inventarios-nav" class="nav-content collapse {{ in_array($activePage, ['transferencias', 'productos']) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                    <li>
                        <a class="nav-link {{ $activePage === 'transferencias' ? '' : 'collapsed' }}"
                            href="/transferencias">
                            <i class="bi bi-receipt"></i>
                            <span>Transferencias</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link {{ $activePage === 'productos' ? '' : 'collapsed' }}" href="/productos">
                            <i class="bi bi-receipt"></i>
                            <span>Productos</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        <!-- End Membresías Nav -->



        @if (auth()->user()->role == 1)
            <li class="nav-item">
                <a class="nav-link {{ in_array($activePage, ['usuarios', 'administracion', 'sync_monitor', 'mongo_monitor', 'tipo_de_cambio', 'api_explorer']) ? '' : 'collapsed' }}" data-bs-target="#configuracion-nav" data-bs-toggle="collapse"
                    href="#">
                    <i class="bi bi-gear"></i><span>Configuración </span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="configuracion-nav" class="nav-content collapse {{ in_array($activePage, ['usuarios', 'administracion', 'sync_monitor', 'mongo_monitor', 'tipo_de_cambio', 'api_explorer']) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                    <li><a class="nav-link {{ $activePage === 'usuarios' ? '' : 'collapsed' }}" href="/usuarios"><i class="bi bi-people"></i><span>Usuarios </span></a></li>
                    <li><a class="nav-link {{ $activePage === 'administracion' ? '' : 'collapsed' }}" href="{{ route('administracion.index') }}"><i class="bi bi-shield-check"></i><span>Auditoría </span></a></li>
                    <li><a class="nav-link {{ $activePage === 'sync_monitor' ? '' : 'collapsed' }}" href="{{ route('administracion.sync.monitor') }}"><i class="bi bi-arrow-repeat"></i><span>Monitor Sync</span></a></li>
                    <li><a class="nav-link {{ $activePage === 'mongo_monitor' ? '' : 'collapsed' }}" href="{{ route('mongo_monitor.index') }}"><i class="bi bi-database-fill"></i><span>Monitor MongoDB</span></a></li>
                    <li><a class="nav-link {{ $activePage === 'tipo_de_cambio' ? '' : 'collapsed' }}" href="/tipo_de_cambio"><i class="bi bi-currency-exchange"></i><span>Tipo de Cambio</span></a></li>
                    <li><a class="nav-link {{ $activePage === 'api_explorer' ? '' : 'collapsed' }}" href="{{ route('api_explorer.index') }}"><i class="bi bi-braces-asterisk"></i><span>API Explorer</span></a></li>
                </ul>
            </li>
        @endif
        @if (auth()->user()->role == 1 or auth()->user()->role == 2)
            <li class="nav-item">
            <a class="nav-link {{ $activePage === 'promociones' ? '' : 'collapsed' }}" href="/promociones">
                <i class="bi bi-tag"></i>
                <span>Promociones</span>
            </a>
        </li>
        
        @endif


        <li class="nav-item">
            <a class="nav-link {{ $activePage === 'logoput' ? '' : 'collapsed' }}" href="/logout">
                <i class="bi bi-code-square"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>





    </ul>

</aside><!-- End Sidebar-->
