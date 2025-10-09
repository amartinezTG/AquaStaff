<!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link <?= (substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/"))=='/index.php'?'':'collapsed'); ?> " href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link <?= (substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/"))=='/vending.php'?'':'collapsed'); ?>" href="vending.php">
          <i class="bi bi-safe2"></i>
          <span>Vending Machine</span>
        </a>
      </li><!-- End Vending Machine Nav -->

      <li class="nav-item">
        <a class="nav-link <?= (substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/"))=='/cajero.php'?'':'collapsed'); ?>" href="cajero.php">
          <i class="bi bi-inboxes"></i>
          <span>Cajero</span>
        </a>
      </li><!-- End Cajero Nav -->

      <li class="nav-item">
        <a class="nav-link <?= (substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/"))=='/membresias.php'?'':'collapsed'); ?>" href="membresias.php">
          <i class="bi bi-person-badge"></i>
          <span>Membresías</span>
        </a>
      </li><!-- End Membresías Nav -->

      <li class="nav-item">
        <a class="nav-link <?= (substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/"))=='/inventarios.php'?'':'collapsed'); ?>" href="inventarios.php">
          <i class="bi bi-tags"></i>
          <span>Control de Inventarios</span>
        </a>
      </li><!-- End Control de Inventarios Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="facturacion.php">
          <i class="bi bi-receipt"></i>
          <span>Facturación</span>
        </a>
      </li><!-- End Membresías Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="index.php">
          <i class="bi bi-code-square"></i>
          <span>Integración CompaQ</span>
        </a>
      </li><!-- End Control de Inventarios Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Configuración</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="#">
              <i class="bi bi-circle"></i><span>Usuarios</span>
            </a>
          </li>
          <li>
            <a href="#">
              <i class="bi bi-circle"></i><span>Sucursales</span>
            </a>
          </li>
          <li>
            <a href="#">
              <i class="bi bi-circle"></i><span>Niveles de inventario</span>
            </a>
          </li>
        </ul>
      </li><!-- End Charts Nav -->


     
      
    </ul>

  </aside><!-- End Sidebar-->