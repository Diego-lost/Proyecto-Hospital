<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Admin') | Clínica NovaSalud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
      :root {
        --ns-sidebar: #00334d;
        --ns-sidebar-hover: rgba(255, 255, 255, 0.08);
        --ns-sidebar-active: rgba(45, 212, 191, 0.18);
        --ns-teal: #14b8a6;
        --ns-teal-soft: #ccfbf1;
        --ns-teal-border: #99f6e4;
        --ns-page: #f4f7f9;
        --ns-card: #ffffff;
        --ns-text: #0f172a;
        --ns-muted: #64748b;
        --ns-line: #e2e8f0;
        --ns-navy: #00334d;
        --ns-navy-btn: #004d6e;
        --ns-danger: #dc2626;
        --ns-success: #059669;
        --ns-warning: #d97706;
        --ns-info: #2563eb;
        --shadow-card: 0 1px 3px rgba(0, 51, 77, 0.06), 0 8px 24px rgba(0, 51, 77, 0.06);
        --radius: 12px;
        --radius-sm: 8px;
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        color: var(--ns-text);
        background: var(--ns-page);
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
      }
      a { color: inherit; text-decoration: none; }
      button { font-family: inherit; }

      .admin-layout { display: flex; min-height: 100vh; }

      /* —— Sidebar —— */
      .admin-sidebar {
        width: 252px;
        flex-shrink: 0;
        background: var(--ns-sidebar);
        color: #f8fafc;
        display: flex;
        flex-direction: column;
        padding: 20px 14px;
      }
      .admin-sidebar__brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 8px 20px;
      }
      .admin-sidebar__logo {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--ns-teal), #0d9488);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }
      .admin-sidebar__logo svg { width: 20px; height: 20px; color: #fff; }
      .admin-sidebar__title {
        font-weight: 700;
        font-size: 14px;
        line-height: 1.25;
        letter-spacing: -0.02em;
      }
      .admin-sidebar__title span { display: block; font-size: 11px; font-weight: 500; color: rgba(248, 250, 252, 0.65); margin-top: 2px; }

      .admin-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
      .admin-nav a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        font-weight: 500;
        color: rgba(248, 250, 252, 0.88);
        transition: background 0.15s, color 0.15s;
      }
      .admin-nav a svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        opacity: 0.85;
      }
      .admin-nav a:hover {
        background: var(--ns-sidebar-hover);
        color: #fff;
      }
      .admin-nav a.is-active {
        background: var(--ns-sidebar-active);
        color: #fff;
        font-weight: 600;
        box-shadow: inset 3px 0 0 var(--ns-teal);
      }
      .admin-nav a.is-active svg { opacity: 1; color: var(--ns-teal); }

      .admin-sidebar__foot {
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        flex-direction: column;
        gap: 4px;
      }
      .admin-sidebar__foot a,
      .admin-sidebar__foot button.sidebar-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 500;
        color: rgba(248, 250, 252, 0.8);
        background: none;
        border: none;
        cursor: pointer;
        width: 100%;
        text-align: left;
      }
      .admin-sidebar__foot a:hover,
      .admin-sidebar__foot button.sidebar-link:hover {
        background: var(--ns-sidebar-hover);
        color: #fff;
      }
      .admin-sidebar__foot svg { width: 16px; height: 16px; opacity: 0.75; }
      .admin-sidebar__email {
        font-size: 11px;
        color: rgba(248, 250, 252, 0.5);
        padding: 8px 12px 0;
        word-break: break-all;
      }

      /* —— Main —— */
      .admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
      .admin-content {
        padding: 24px 28px 48px;
        max-width: 1280px;
        width: 100%;
      }
      .page-header { margin-bottom: 20px; }
      .page-header__row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
      }
      .page-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--ns-text);
      }
      .page-header .page-subtitle {
        margin: 4px 0 0;
        font-size: 14px;
        color: var(--ns-muted);
        font-weight: 500;
      }
      .page-header__actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
      }
      .btn-sitio-web {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: var(--radius-sm);
        background: var(--ns-navy-btn);
        border: 1px solid var(--ns-navy-btn);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        transition: background 0.15s, box-shadow 0.15s;
      }
      .btn-sitio-web:hover {
        background: var(--ns-navy);
        border-color: var(--ns-navy);
        box-shadow: 0 4px 14px rgba(0, 51, 77, 0.2);
        color: #fff;
      }
      .btn-sitio-web svg { width: 16px; height: 16px; opacity: 0.9; }

      .callout {
        background: var(--ns-teal-soft);
        border: 1px solid var(--ns-teal-border);
        border-radius: var(--radius);
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 13.5px;
        line-height: 1.55;
        color: #134e4a;
        display: flex;
        gap: 12px;
        align-items: flex-start;
      }
      .callout svg { width: 20px; height: 20px; flex-shrink: 0; color: #0d9488; margin-top: 1px; }
      .callout strong { color: #0f766e; font-weight: 700; }
      .callout--error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #7f1d1d;
      }
      .callout--error strong { color: #b91c1c; }

      .container { width: 100%; }
      .card {
        background: var(--ns-card);
        border: 1px solid var(--ns-line);
        border-radius: var(--radius);
        padding: 18px 20px;
        box-shadow: var(--shadow-card);
      }
      .card h2 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: -0.02em;
      }
      .card .card-desc {
        margin: 0 0 14px;
        font-size: 13px;
        color: var(--ns-muted);
      }

      .grid { display: grid; gap: 16px; }
      .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }

      .table { width: 100%; border-collapse: collapse; }
      .table th,
      .table td {
        border-bottom: 1px solid var(--ns-line);
        padding: 11px 10px;
        text-align: left;
        font-size: 13.5px;
        vertical-align: top;
      }
      .table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--ns-muted);
        font-weight: 700;
      }
      .table tbody tr:last-child td { border-bottom: none; }
      .table tbody tr:hover { background: #f8fafc; }

      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--ns-line);
        background: #fff;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: var(--ns-text);
        transition: box-shadow 0.15s, transform 0.1s, background 0.15s;
      }
      .btn:hover {
        box-shadow: 0 4px 12px rgba(0, 51, 77, 0.1);
      }
      .btn-primary {
        background: var(--ns-navy-btn);
        border-color: var(--ns-navy-btn);
        color: #fff;
      }
      .btn-primary:hover {
        background: var(--ns-navy);
        border-color: var(--ns-navy);
      }
      .btn-danger {
        background: var(--ns-danger);
        border-color: var(--ns-danger);
        color: #fff;
      }
      .btn-soft {
        background: #f1f5f9;
        border-color: var(--ns-line);
        color: var(--ns-navy);
      }
      .btn-block { width: 100%; }

      .row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
      }
      .page-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
      }
      .page-toolbar h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.03em; }

      .field { display: grid; gap: 6px; }
      .field label {
        font-size: 12px;
        color: var(--ns-muted);
        font-weight: 600;
      }
      .field input,
      .field select,
      .field textarea {
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--ns-line);
        background: #fff;
        font: inherit;
        font-size: 14px;
      }
      .field input:focus,
      .field select:focus,
      .field textarea:focus {
        outline: none;
        border-color: var(--ns-teal);
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15);
      }

      .muted { color: var(--ns-muted); }
      .badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        display: inline-block;
        text-transform: capitalize;
      }
      .badge--nueva { background: #dbeafe; color: #1d4ed8; }
      .badge--reprogramada { background: #ffedd5; color: #c2410c; }
      .badge--cancelada { background: #fee2e2; color: #b91c1c; }
      .badge--activo { background: #d1fae5; color: #047857; }

      .stats-row {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
      }
      .stat-kpi {
        background: var(--ns-card);
        border: 1px solid var(--ns-line);
        border-radius: var(--radius);
        padding: 14px 16px;
        box-shadow: var(--shadow-card);
        display: flex;
        align-items: flex-start;
        gap: 12px;
      }
      .stat-kpi__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }
      .stat-kpi__icon svg { width: 20px; height: 20px; }
      .stat-kpi__icon--teal { background: #ccfbf1; color: #0d9488; }
      .stat-kpi__icon--orange { background: #ffedd5; color: #ea580c; }
      .stat-kpi__icon--purple { background: #ede9fe; color: #7c3aed; }
      .stat-kpi__icon--blue { background: #dbeafe; color: #2563eb; }
      .stat-kpi__icon--green { background: #d1fae5; color: #059669; }
      .stat-kpi__icon--red { background: #fee2e2; color: #dc2626; }
      .stat-kpi__body { min-width: 0; }
      .stat-kpi .num {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.04em;
        line-height: 1.1;
      }
      .stat-kpi .lbl {
        font-size: 12px;
        color: var(--ns-muted);
        font-weight: 600;
        margin-top: 2px;
        line-height: 1.3;
      }

      .dash-grid-main {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 16px;
        margin-bottom: 16px;
      }
      .dash-grid-bottom {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 16px;
      }

      .bar-chart { display: flex; align-items: flex-end; gap: 10px; height: 120px; margin-bottom: 16px; padding: 0 4px; }
      .bar-chart__col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 0; }
      .bar-chart__bar {
        width: 100%;
        max-width: 48px;
        border-radius: 6px 6px 2px 2px;
        background: linear-gradient(180deg, var(--ns-teal), #0d9488);
        min-height: 4px;
        transition: height 0.3s;
      }
      .bar-chart__label { font-size: 10px; color: var(--ns-muted); font-weight: 600; text-align: center; text-transform: capitalize; }

      .status-list { list-style: none; margin: 0; padding: 0; }
      .status-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid var(--ns-line);
        font-size: 13.5px;
      }
      .status-list li:last-child { border-bottom: none; }
      .status-list__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
        flex-shrink: 0;
      }
      .status-list__left { display: flex; align-items: center; }

      .appt-list { list-style: none; margin: 0; padding: 0; }
      .appt-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--ns-line);
      }
      .appt-item:last-child { border-bottom: none; }
      .appt-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00334d, #0d9488);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }
      .appt-item__body { flex: 1; min-width: 0; }
      .appt-item__name { font-weight: 700; font-size: 14px; }
      .appt-item__meta { font-size: 12px; color: var(--ns-muted); margin-top: 2px; }

      .catalog-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 14px;
        background: #f1f5f9;
        padding: 4px;
        border-radius: var(--radius-sm);
      }
      .catalog-tabs a {
        flex: 1;
        text-align: center;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--ns-muted);
      }
      .catalog-tabs a:hover { color: var(--ns-text); background: rgba(255,255,255,0.6); }
      .catalog-tabs a.is-active {
        background: #fff;
        color: var(--ns-navy);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      }
      .catalog-links { display: flex; flex-direction: column; gap: 8px; }
      .catalog-links a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border: 1px solid var(--ns-line);
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        font-weight: 600;
        transition: border-color 0.15s, box-shadow 0.15s;
      }
      .catalog-links a:hover {
        border-color: var(--ns-teal-border);
        box-shadow: 0 2px 8px rgba(0, 51, 77, 0.06);
      }
      .catalog-links span.muted { font-weight: 500; font-size: 12px; }

      .mini-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 14px;
      }
      .mini-stat {
        background: #f8fafc;
        border: 1px solid var(--ns-line);
        border-radius: var(--radius-sm);
        padding: 12px;
        text-align: center;
      }
      .mini-stat .num { font-size: 20px; font-weight: 800; letter-spacing: -0.03em; }
      .mini-stat .lbl { font-size: 11px; color: var(--ns-muted); font-weight: 600; margin-top: 2px; }

      .sync-note {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--ns-line);
        font-size: 12px;
        color: var(--ns-muted);
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .sync-note__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--ns-teal);
        flex-shrink: 0;
      }

      @media (max-width: 1100px) {
        .stats-row { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dash-grid-main,
        .dash-grid-bottom { grid-template-columns: 1fr; }
      }
      @media (max-width: 768px) {
        .admin-layout { flex-direction: column; }
        .admin-sidebar { width: 100%; }
        .stats-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-2, .grid-3 { grid-template-columns: 1fr; }
        .admin-content { padding: 16px; }
      }
      @stack('styles')
    </style>
  </head>
  <body>
    @php
      $navItems = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'match' => 'admin.dashboard', 'icon' => 'grid'],
        ['route' => 'admin.especialidades.index', 'label' => 'Especialidades', 'match' => 'admin.especialidades.*', 'icon' => 'layers'],
        ['route' => 'admin.medicos.index', 'label' => 'Médicos', 'match' => 'admin.medicos.*', 'icon' => 'users'],
        ['route' => 'admin.servicios.index', 'label' => 'Servicios', 'match' => 'admin.servicios.*', 'icon' => 'box'],
        ['route' => 'admin.solicitudes-citas.index', 'label' => 'Solicitudes de citas', 'match' => 'admin.solicitudes-citas.*', 'icon' => 'calendar'],
        ['route' => 'admin.pagos.index', 'label' => 'Pagos / Órdenes', 'match' => 'admin.pagos.*', 'icon' => 'card'],
      ];
    @endphp
    <div class="admin-layout">
      <aside class="admin-sidebar" aria-label="Menú administración">
        <div class="admin-sidebar__brand">
          <div class="admin-sidebar__logo" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
          </div>
          <div class="admin-sidebar__title">
            Clínica NovaSalud
            <span>Admin</span>
          </div>
        </div>
        <nav class="admin-nav">
          @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['match'])])>
              @include('admin.partials.nav-icon', ['name' => $item['icon']])
              {{ $item['label'] }}
            </a>
          @endforeach
        </nav>
        <div class="admin-sidebar__foot">
          <a href="{{ route('web.public') }}" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Ver sitio web
          </a>
          <form method="post" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="sidebar-link">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Cerrar sesión
            </button>
          </form>
          <div class="admin-sidebar__email">{{ auth()->user()->email }}</div>
        </div>
      </aside>
      <div class="admin-main">
        <div class="admin-content">
          @hasSection('page_header')
            @yield('page_header')
          @else
            <header class="page-header">
              <div class="page-header__row">
                <div>
                  <h1>@yield('title', 'Administración')</h1>
                  @hasSection('page_subtitle')
                    <p class="page-subtitle">@yield('page_subtitle')</p>
                  @endif
                </div>
                <div class="page-header__actions">
                  @hasSection('page_actions')
                    @yield('page_actions')
                  @endif
                  <a class="btn-sitio-web" href="{{ route('web.public') }}" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Ver sitio web
                  </a>
                </div>
              </div>
            </header>
          @endif
          @yield('content')
        </div>
      </div>
    </div>
    <script>
      document.querySelectorAll("form[data-admin-delete]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
          if (!confirm("¿Eliminar este registro? Esta acción no se puede deshacer.")) {
            e.preventDefault();
          }
        });
      });
    </script>
    @stack('scripts')
  </body>
</html>
