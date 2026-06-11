@include('layout.shared')
@include('layout.includes')

<meta name="csrf-token" content="{{ csrf_token() }}">
<body class="toggle-sidebar">

    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="/dashboard" class="logo d-flex align-items-center">
                <img src="/assets/img/AQUA-CAR-CLUB-LOGO-N.png" alt="">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        @include('layout.nav-header')
    </header>

    <main id="main" class="main">
        <style>
            .api-card {
                border-radius: 12px;
                border: none;
                box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            }
            .badge-get    { background: #28a745; color: #fff; font-size: .75rem; padding: 3px 8px; border-radius: 4px; }
            .badge-post   { background: #f39c12; color: #fff; font-size: .75rem; padding: 3px 8px; border-radius: 4px; }
            .endpoint-btn {
                text-align: left;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 10px 14px;
                background: #fff;
                cursor: pointer;
                transition: all .2s;
                width: 100%;
            }
            .endpoint-btn:hover { background: #f8f9fa; border-color: #adb5bd; }
            .endpoint-btn.active { background: #e8f4fd; border-color: #3498db; }
            .response-box {
                background: #1e1e2e;
                color: #cdd6f4;
                border-radius: 10px;
                padding: 16px;
                font-family: 'Courier New', monospace;
                font-size: .82rem;
                max-height: 520px;
                overflow-y: auto;
                white-space: pre-wrap;
                word-break: break-word;
            }
            .token-badge {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                border-radius: 6px;
                padding: 6px 12px;
                font-size: .8rem;
                color: #155724;
                word-break: break-all;
            }
            .token-badge.expired { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
            #loginSection { transition: all .3s; }
            .param-row { display: flex; gap: 8px; margin-bottom: 6px; }
            .param-row input { flex: 1; }
            .spinner-border-sm { width: 1rem; height: 1rem; }
            .stats-bar { font-size: .78rem; color: #6c757d; margin-top: 6px; }
        </style>

        <div class="pagetitle mb-3">
            <h1><i class="bi bi-braces-asterisk me-2"></i>API Explorer</h1>
            <nav><ol class="breadcrumb"><li class="breadcrumb-item">Configuración</li><li class="breadcrumb-item active">API Explorer</li></ol></nav>
        </div>

        <div class="row g-3">

            {{-- LOGIN --}}
            <div class="col-12">
                <div class="card api-card" id="loginSection">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div id="loginForm" class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold text-muted small">Autenticación</span>
                                <input type="text"  id="inputClientId"     class="form-control form-control-sm" style="width:310px" placeholder="Client ID (UserId)" value="236168dd-fb41-46c9-9ac3-68a44d1cc68c">
                                <input type="password" id="inputClientSecret" class="form-control form-control-sm" style="width:310px" placeholder="Client Secret (UserSecret)" value="1ff2d909-ab5e-4f07-97aa-c7333d62ce9f">
                                <button class="btn btn-sm btn-primary" id="btnLogin">
                                    <span id="loginSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                    <i class="bi bi-key me-1"></i>Obtener Token
                                </button>
                            </div>
                            <div id="tokenDisplay" class="d-none flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-semibold text-muted small">Token activo</span>
                                    <span class="token-badge" id="tokenShort"></span>
                                    <span class="stats-bar" id="tokenExpiry"></span>
                                    <button class="btn btn-sm btn-outline-secondary ms-auto" id="btnLogout"><i class="bi bi-arrow-counterclockwise me-1"></i>Cambiar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ENDPOINTS --}}
            <div class="col-md-4">
                <div class="card api-card h-100">
                    <div class="card-header bg-white fw-semibold border-0 pb-0">
                        <i class="bi bi-list-ul me-1"></i> Endpoints
                    </div>
                    <div class="card-body p-2">
                        <div class="d-flex flex-column gap-1" id="endpointList">

                            <div class="small text-muted px-2 pt-1 pb-0 fw-semibold">Clientes</div>

                            <button class="endpoint-btn" data-method="GET" data-endpoint="/api/Data/Clients" data-label="Todos los clientes">
                                <span class="badge-get me-2">GET</span>/api/Data/Clients
                            </button>

                            <button class="endpoint-btn" data-method="GET" data-endpoint="/api/Data/ClientIds" data-label="IDs de clientes">
                                <span class="badge-get me-2">GET</span>/api/Data/ClientIds
                            </button>

                            <button class="endpoint-btn" data-method="GET" data-endpoint="/api/Data/Client/{id}" data-label="Cliente por ID" data-params='[{"key":"id","label":"Client ID","required":true}]'>
                                <span class="badge-get me-2">GET</span>/api/Data/Client/<span class="text-primary">{id}</span>
                            </button>

                            <div class="small text-muted px-2 pt-2 pb-0 fw-semibold">Membresías</div>

                            <button class="endpoint-btn" data-method="POST" data-endpoint="/api/Data/ClientMemberships" data-label="Membresías por rango de fechas"
                                data-body='{"startDate":"2025-01-01T00:00:00","endDate":"2026-12-31T23:59:59"}'>
                                <span class="badge-post me-2">POST</span>/api/Data/ClientMemberships
                            </button>

                            <button class="endpoint-btn" data-method="GET" data-endpoint="/api/Data/ClientMembership/{clientMembershipId}" data-label="Membresía por ID"
                                data-params='[{"key":"clientMembershipId","label":"Membership ID","required":true}]'>
                                <span class="badge-get me-2">GET</span>/api/Data/ClientMembership/<span class="text-primary">{id}</span>
                            </button>

                            <button class="endpoint-btn" data-method="GET" data-endpoint="/api/Data/MembershipsByClient/{clientId}" data-label="Membresías de un cliente"
                                data-params='[{"key":"clientId","label":"Client ID","required":true}]'>
                                <span class="badge-get me-2">GET</span>/api/Data/MembershipsByClient/<span class="text-primary">{clientId}</span>
                            </button>

                            <div class="small text-muted px-2 pt-2 pb-0 fw-semibold">Transacciones</div>

                            <button class="endpoint-btn" data-method="POST" data-endpoint="/api/Data/LocalTransactions" data-label="Transacciones locales"
                                data-body='{"initDate":"2026-05-01T00:00:00","endDate":"2026-05-31T23:59:59"}'>
                                <span class="badge-post me-2">POST</span>/api/Data/LocalTransactions
                            </button>

                            <button class="endpoint-btn" data-method="POST" data-endpoint="/api/Data/Orders" data-label="Órdenes"
                                data-body='{"startDate":"2026-05-01T00:00:00","endDate":"2026-05-31T23:59:59"}'>
                                <span class="badge-post me-2">POST</span>/api/Data/Orders
                            </button>

                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL DERECHO --}}
            <div class="col-md-8">
                <div class="card api-card">
                    <div class="card-body p-3">

                        <div id="placeholderMsg" class="text-center text-muted py-5">
                            <i class="bi bi-cursor-fill fs-1 d-block mb-2" style="opacity:.3"></i>
                            Selecciona un endpoint de la lista
                        </div>

                        <div id="requestPanel" class="d-none">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span id="panelMethodBadge"></span>
                                <code id="panelEndpointLabel" class="fs-6"></code>
                            </div>

                            {{-- Params --}}
                            <div id="paramsSection" class="d-none mb-3">
                                <label class="form-label fw-semibold small">Parámetros de ruta</label>
                                <div id="paramsContainer"></div>
                            </div>

                            {{-- Body --}}
                            <div id="bodySection" class="d-none mb-3">
                                <label class="form-label fw-semibold small">Body (JSON)</label>
                                <textarea id="requestBody" class="form-control form-control-sm font-monospace" rows="4"></textarea>
                            </div>

                            <button class="btn btn-sm btn-success" id="btnSend">
                                <span id="sendSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                                <i class="bi bi-send me-1"></i>Ejecutar
                            </button>
                            <span id="responseStats" class="stats-bar ms-3"></span>
                        </div>
                    </div>
                </div>

                {{-- Response --}}
                <div class="card api-card mt-3" id="responseCard" style="display:none!important">
                    <div class="card-header bg-white border-0 d-flex align-items-center gap-2 pb-0">
                        <span class="fw-semibold small">Respuesta</span>
                        <span id="statusBadge" class="badge ms-1"></span>
                        <button class="btn btn-sm btn-outline-secondary ms-auto py-0" id="btnCopy" style="font-size:.75rem"><i class="bi bi-clipboard me-1"></i>Copiar</button>
                    </div>
                    <div class="card-body p-2">
                        <div class="response-box" id="responseBox"></div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    @include('layout.footer')

    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let token = localStorage.getItem('aqua_api_token') || '';
    let tokenExpiry = localStorage.getItem('aqua_api_token_expiry') || '';
    let currentEndpoint = null;

    // ── TOKEN ──────────────────────────────────────────────
    function updateTokenUI() {
        if (token) {
            document.getElementById('loginForm').classList.add('d-none');
            document.getElementById('tokenDisplay').classList.remove('d-none');
            document.getElementById('tokenShort').textContent = token.substring(0, 40) + '…';
            if (tokenExpiry) {
                const exp = new Date(tokenExpiry);
                const now = new Date();
                const diff = Math.round((exp - now) / 60000);
                const el = document.getElementById('tokenExpiry');
                if (diff > 0) {
                    el.textContent = `Expira en ${diff} min`;
                    document.getElementById('tokenShort').classList.remove('expired');
                } else {
                    el.textContent = 'Token expirado — vuelve a autenticarte';
                    document.getElementById('tokenShort').classList.add('expired');
                }
            }
        } else {
            document.getElementById('loginForm').classList.remove('d-none');
            document.getElementById('tokenDisplay').classList.add('d-none');
        }
    }

    document.getElementById('btnLogin').addEventListener('click', async () => {
        const clientId     = document.getElementById('inputClientId').value.trim();
        const clientSecret = document.getElementById('inputClientSecret').value.trim();
        if (!clientId || !clientSecret) return;

        toggleSpinner('loginSpinner', true);
        try {
            const res = await fetch('{{ route("api_explorer.login") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ client_id: clientId, client_secret: clientSecret })
            });
            const data = await res.json();
            if (data?.data?.authToken) {
                token = data.data.authToken;
                // Decodificar exp del JWT
                const payload = JSON.parse(atob(token.split('.')[1]));
                tokenExpiry = new Date(payload.exp * 1000).toISOString();
                localStorage.setItem('aqua_api_token', token);
                localStorage.setItem('aqua_api_token_expiry', tokenExpiry);
                updateTokenUI();
            } else {
                alert('No se pudo obtener el token: ' + JSON.stringify(data));
            }
        } catch(e) {
            alert('Error de conexión: ' + e.message);
        }
        toggleSpinner('loginSpinner', false);
    });

    document.getElementById('btnLogout').addEventListener('click', () => {
        token = ''; tokenExpiry = '';
        localStorage.removeItem('aqua_api_token');
        localStorage.removeItem('aqua_api_token_expiry');
        updateTokenUI();
    });

    // ── ENDPOINTS ──────────────────────────────────────────
    document.querySelectorAll('.endpoint-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.endpoint-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            currentEndpoint = {
                method:   btn.dataset.method,
                endpoint: btn.dataset.endpoint,
                params:   btn.dataset.params   ? JSON.parse(btn.dataset.params) : [],
                body:     btn.dataset.body     || '',
            };

            document.getElementById('placeholderMsg').classList.add('d-none');
            document.getElementById('requestPanel').classList.remove('d-none');

            const methodBadge = document.getElementById('panelMethodBadge');
            methodBadge.className = currentEndpoint.method === 'GET' ? 'badge-get' : 'badge-post';
            methodBadge.textContent = currentEndpoint.method;
            document.getElementById('panelEndpointLabel').textContent = currentEndpoint.endpoint;

            // Params
            const paramsSection = document.getElementById('paramsSection');
            const paramsContainer = document.getElementById('paramsContainer');
            paramsContainer.innerHTML = '';
            if (currentEndpoint.params.length > 0) {
                paramsSection.classList.remove('d-none');
                currentEndpoint.params.forEach(p => {
                    const row = document.createElement('div');
                    row.className = 'param-row';
                    row.innerHTML = `<span class="input-group-text small" style="width:200px;font-size:.8rem">${p.label}</span>
                        <input type="text" class="form-control form-control-sm param-input" data-key="${p.key}" placeholder="${p.key}">`;
                    paramsContainer.appendChild(row);
                });
            } else {
                paramsSection.classList.add('d-none');
            }

            // Body
            const bodySection = document.getElementById('bodySection');
            if (currentEndpoint.method === 'POST') {
                bodySection.classList.remove('d-none');
                document.getElementById('requestBody').value = currentEndpoint.body
                    ? JSON.stringify(JSON.parse(currentEndpoint.body), null, 2)
                    : '{}';
            } else {
                bodySection.classList.add('d-none');
            }

            document.getElementById('responseCard').style.display = 'none';
            document.getElementById('responseStats').textContent = '';
        });
    });

    // ── SEND ───────────────────────────────────────────────
    document.getElementById('btnSend').addEventListener('click', async () => {
        if (!token) { alert('Primero obtén un token de autenticación.'); return; }
        if (!currentEndpoint) return;

        let endpoint = currentEndpoint.endpoint;

        // Sustituir params en la URL
        document.querySelectorAll('.param-input').forEach(input => {
            endpoint = endpoint.replace(`{${input.dataset.key}}`, input.value.trim());
        });

        if (endpoint.includes('{')) {
            alert('Completa todos los parámetros requeridos.');
            return;
        }

        toggleSpinner('sendSpinner', true);
        const t0 = performance.now();

        try {
            const res = await fetch('{{ route("api_explorer.call") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    token,
                    method:   currentEndpoint.method,
                    endpoint,
                    body: currentEndpoint.method === 'POST' ? document.getElementById('requestBody').value : null,
                })
            });

            const elapsed = Math.round(performance.now() - t0);
            const data = await res.json();

            // Status badge
            const statusBadge = document.getElementById('statusBadge');
            statusBadge.textContent = res.status;
            statusBadge.className = 'badge ms-1 ' + (res.ok ? 'bg-success' : 'bg-danger');

            // Stats
            const count = Array.isArray(data) ? `${data.length} registros · ` : '';
            document.getElementById('responseStats').textContent = `${count}${elapsed}ms`;

            // Response
            document.getElementById('responseBox').textContent = JSON.stringify(data, null, 2);
            document.getElementById('responseCard').style.removeProperty('display');

        } catch(e) {
            document.getElementById('responseBox').textContent = 'Error: ' + e.message;
            document.getElementById('responseCard').style.removeProperty('display');
        }

        toggleSpinner('sendSpinner', false);
    });

    // ── COPY ───────────────────────────────────────────────
    document.getElementById('btnCopy').addEventListener('click', () => {
        const text = document.getElementById('responseBox').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('btnCopy');
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copiado';
            setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copiar', 1500);
        });
    });

    function toggleSpinner(id, show) {
        document.getElementById(id).classList.toggle('d-none', !show);
    }

    // Init
    updateTokenUI();
    setInterval(updateTokenUI, 60000);
    </script>
</body>
