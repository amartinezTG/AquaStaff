@include('layout.shared')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body class="toggle-sidebar">

  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="/dashboard" class="logo d-flex align-items-center">
        <img src="assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    @include('layout.nav-header')
  </header>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Usuarios</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Portada</a></li>
          <li class="breadcrumb-item active">Usuarios</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      @if (auth()->user()->role == 1)
      <div class="row">
        <div class="col-12">
          <div class="card" style="border-left: 5px solid #2399b7;">
            <div class="card-body pt-3">

              <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="card-title mb-0">Gestión de Usuarios</h5>
                <a href="{{ route('usuario') }}" class="btn btn-sm btn-warning">
                  <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
                </a>
              </div>

              {{-- Resumen de roles --}}
              <div class="row g-2 mb-4">
                @php
                  $superadmin   = $staff_users->where('role', 1)->where('active', 1)->count();
                  $admin        = $staff_users->where('role', 2)->where('active', 1)->count();
                  $manager      = $staff_users->where('role', 3)->where('active', 1)->count();
                  $inactivos    = $staff_users->where('active', '!=', 1)->count();
                @endphp
                <div class="col-6 col-md-3">
                  <div class="border rounded p-2 text-center" style="border-left: 3px solid #dc3545 !important;">
                    <div class="fw-bold fs-5 text-danger">{{ $superadmin }}</div>
                    <small class="text-muted">Superadmin</small>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="border rounded p-2 text-center" style="border-left: 3px solid #2399b7 !important;">
                    <div class="fw-bold fs-5" style="color:#2399b7">{{ $admin }}</div>
                    <small class="text-muted">Administrator</small>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="border rounded p-2 text-center" style="border-left: 3px solid #ffc107 !important;">
                    <div class="fw-bold fs-5 text-warning">{{ $manager }}</div>
                    <small class="text-muted">Manager</small>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="border rounded p-2 text-center" style="border-left: 3px solid #6c757d !important;">
                    <div class="fw-bold fs-5 text-secondary">{{ $inactivos }}</div>
                    <small class="text-muted">Inactivos</small>
                  </div>
                </div>
              </div>

              {{-- Filtro rápido --}}
              <div class="d-flex gap-2 mb-3 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">Todos</button>
                <button class="btn btn-sm btn-outline-success filter-btn" data-filter="active">Activos</button>
                <button class="btn btn-sm btn-outline-secondary filter-btn" data-filter="inactive">Inactivos</button>
                <input type="text" id="searchUser" class="form-control form-control-sm ms-auto" style="max-width:220px;" placeholder="Buscar usuario…">
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle" id="usersTable">
                  <thead style="background: linear-gradient(135deg, #2399b7 0%, #1a7a91 100%); color:#fff; font-size:.82rem;">
                    <tr>
                      <th>#</th>
                      <th>Nombre</th>
                      <th>Usuario</th>
                      <th>Rol</th>
                      <th class="text-center">Estado</th>
                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($staff_users as $u)
                    @php
                      $isActive = $u->active == 1;
                      $isSelf   = auth()->id() == $u->id;
                      $roleColors = ['1' => 'danger', '2' => 'primary', '3' => 'warning'];
                      $roleColor  = $roleColors[$u->role] ?? 'secondary';
                    @endphp
                    <tr data-status="{{ $isActive ? 'active' : 'inactive' }}" data-name="{{ strtolower($u->name) }} {{ strtolower($u->email) }}">
                      <td class="text-muted" style="font-size:.8rem;">{{ $u->id }}</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                               style="width:34px;height:34px;font-size:.8rem;flex-shrink:0;background:{{ $isActive ? '#2399b7' : '#adb5bd' }};">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                          </div>
                          <span style="font-size:.85rem;">{{ $u->name }}</span>
                          @if($isSelf)
                            <span class="badge bg-secondary" style="font-size:.68rem;">Tú</span>
                          @endif
                        </div>
                      </td>
                      <td style="font-size:.82rem;" class="text-muted">{{ $u->email }}</td>
                      <td>
                        <span class="badge bg-{{ $roleColor }}" style="font-size:.75rem;">
                          {{ $catalogs->role_type[$u->role] ?? 'N/A' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="badge status-badge bg-{{ $isActive ? 'success' : 'secondary' }}" style="font-size:.75rem;">
                          {{ $isActive ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="/editar_usuario/{{ $u->id }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="bi bi-pencil"></i>
                          </a>
                          @if(!$isSelf)
                          <button class="btn btn-sm btn-outline-{{ $isActive ? 'danger' : 'success' }} btn-toggle-active"
                                  data-id="{{ $u->id }}"
                                  data-active="{{ $u->active }}"
                                  title="{{ $isActive ? 'Desactivar' : 'Activar' }}">
                            <i class="bi bi-{{ $isActive ? 'person-dash' : 'person-check' }}"></i>
                          </button>
                          @endif
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

            </div>
          </div>
        </div>
      </div>
      @else
        <div class="alert alert-warning">No tienes permisos para ver esta sección.</div>
      @endif
    </section>

  </main>

  <footer id="footer" class="footer">
    <div class="copyright">&copy; Copyright <strong><span></span></strong>. All Rights Reserved</div>
  </footer>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  @include('layout.footer')

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script>
    const CSRF = '{{ csrf_token() }}';

    // Toggle activo/inactivo
    document.querySelectorAll('.btn-toggle-active').forEach(btn => {
      btn.addEventListener('click', function () {
        const id     = this.dataset.id;
        const active = parseInt(this.dataset.active);
        const action = active == 1 ? 'desactivar' : 'activar';

        Swal.fire({
          title: `¿${action.charAt(0).toUpperCase() + action.slice(1)} usuario?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1),
          cancelButtonText: 'Cancelar',
          confirmButtonColor: active == 1 ? '#dc3545' : '#28a745',
        }).then(result => {
          if (!result.isConfirmed) return;

          fetch(`/usuario/toggle-active/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
          })
          .then(r => r.json())
          .then(data => {
            if (data.error) { Swal.fire('Error', data.error, 'error'); return; }

            const row    = btn.closest('tr');
            const isNowActive = data.active == 1;

            // Badge estado
            const badge = row.querySelector('.status-badge');
            badge.textContent = data.label;
            badge.className   = `badge status-badge bg-${isNowActive ? 'success' : 'secondary'}`;

            // Avatar color
            row.querySelector('.rounded-circle').style.background = isNowActive ? '#2399b7' : '#adb5bd';

            // Botón
            btn.dataset.active = data.active;
            btn.className = `btn btn-sm btn-outline-${isNowActive ? 'danger' : 'success'} btn-toggle-active`;
            btn.title     = isNowActive ? 'Desactivar' : 'Activar';
            btn.querySelector('i').className = `bi bi-${isNowActive ? 'person-dash' : 'person-check'}`;

            // Atributo filtro
            row.dataset.status = isNowActive ? 'active' : 'inactive';

            // Actualizar contadores
            actualizarContadores();

            Swal.fire({ icon: 'success', title: data.label, timer: 1200, showConfirmButton: false });
          })
          .catch(() => Swal.fire('Error', 'No se pudo completar la acción.', 'error'));
        });
      });
    });

    // Filtro por estado
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        aplicarFiltros();
      });
    });

    // Búsqueda
    document.getElementById('searchUser').addEventListener('input', aplicarFiltros);

    function aplicarFiltros() {
      const filter = document.querySelector('.filter-btn.active').dataset.filter;
      const search = document.getElementById('searchUser').value.toLowerCase();

      document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        const statusOk = filter === 'all' || row.dataset.status === filter;
        const searchOk = row.dataset.name.includes(search);
        row.style.display = statusOk && searchOk ? '' : 'none';
      });
    }

    function actualizarContadores() {
      const rows = document.querySelectorAll('#usersTable tbody tr');
      let counts = { 1: 0, 2: 0, 3: 0, inactive: 0 };
      rows.forEach(row => {
        const badge = row.querySelector('.status-badge');
        const roleBadge = row.querySelector('.badge:not(.status-badge)');
        const isActive = row.dataset.status === 'active';
        if (!isActive) { counts.inactive++; return; }
        const roleText = roleBadge?.textContent.trim();
        if (roleText === 'SUPERADMIN')    counts[1]++;
        if (roleText === 'ADMINISTRATOR') counts[2]++;
        if (roleText === 'MANAGER')       counts[3]++;
      });
      // Actualiza los 4 mini-cards (en orden: superadmin, admin, manager, inactivos)
      const cards = document.querySelectorAll('.row.g-2 .fw-bold');
      if (cards[0]) cards[0].textContent = counts[1];
      if (cards[1]) cards[1].textContent = counts[2];
      if (cards[2]) cards[2].textContent = counts[3];
      if (cards[3]) cards[3].textContent = counts.inactive;
    }
  </script>
</body>
