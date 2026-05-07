<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Admin') | Hospital Online</title>
    <style>
      :root {
        --admin-bg: #0f172a;
        --admin-bg2: #1e293b;
        --admin-accent: #3b82f6;
        --admin-accent2: #0ea5e9;
        --admin-text: #f8fafc;
        --admin-muted: #94a3b8;
        --admin-page: #f1f5f9;
        --admin-card: #ffffff;
        --admin-line: rgba(15, 23, 42, 0.12);
        --admin-danger: #ef4444;
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        color: #0f172a;
        background: var(--admin-page);
        min-height: 100vh;
      }
      a { color: inherit; text-decoration: none; }
      .admin-layout {
        display: flex;
        min-height: 100vh;
      }
      .admin-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: linear-gradient(180deg, var(--admin-bg) 0%, #020617 100%);
        color: var(--admin-text);
        padding: 22px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        border-right: 1px solid rgba(148, 163, 184, 0.15);
      }
      .admin-sidebar__brand {
        font-weight: 800;
        font-size: 17px;
        letter-spacing: -0.4px;
        line-height: 1.2;
      }
      .admin-sidebar__tag {
        font-size: 12px;
        color: var(--admin-muted);
        margin-bottom: 12px;
      }
      .admin-nav {
        display: flex;
        flex-direction: column;
        gap: 4px;
      }
      .admin-nav a {
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: rgba(248, 250, 252, 0.92);
        border: 1px solid transparent;
      }
      .admin-nav a:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.25);
      }
      .admin-sidebar__foot {
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
        font-size: 12px;
        color: var(--admin-muted);
        line-height: 1.5;
      }
      .admin-sidebar__foot a {
        color: #7dd3fc;
        font-weight: 600;
      }
      .admin-sidebar__foot a:hover { text-decoration: underline; }
      .admin-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
      }
      .admin-topbar {
        background: var(--admin-card);
        border-bottom: 1px solid var(--admin-line);
        padding: 14px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
      }
      .admin-topbar h1 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.3px;
      }
      .admin-content {
        padding: 22px 24px 48px;
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
      }
      .callout {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(14, 165, 233, 0.08));
        border: 1px solid rgba(59, 130, 246, 0.22);
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.5;
        color: #0f172a;
      }
      .callout strong { color: #1d4ed8; }
      .container { width: 100%; }
      .card {
        background: var(--admin-card);
        border: 1px solid var(--admin-line);
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
      }
      .grid { display: grid; gap: 12px; }
      .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .table {
        width: 100%;
        border-collapse: collapse;
      }
      .table th,
      .table td {
        border-bottom: 1px solid var(--admin-line);
        padding: 11px 10px;
        text-align: left;
        font-size: 14px;
        vertical-align: top;
      }
      .table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #64748b;
        font-weight: 800;
      }
      .table tbody tr:hover {
        background: rgba(59, 130, 246, 0.04);
      }
      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 14px;
        border-radius: 11px;
        border: 1px solid rgba(15, 23, 42, 0.14);
        background: #fff;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
      }
      .btn:hover {
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.1);
        transform: translateY(-1px);
      }
      .btn-primary {
        background: linear-gradient(135deg, var(--admin-accent), var(--admin-accent2));
        border-color: transparent;
        color: #fff;
      }
      .btn-danger {
        background: var(--admin-danger);
        border-color: var(--admin-danger);
        color: #fff;
      }
      .btn-soft {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.25);
        color: #1e3a8a;
      }
      .row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
      }
      .field { display: grid; gap: 6px; }
      .field label {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
      }
      .field input,
      .field select,
      .field textarea {
        padding: 10px 11px;
        border-radius: 11px;
        border: 1px solid var(--admin-line);
        background: #fff;
        font: inherit;
      }
      .muted { color: #64748b; }
      .badge {
        font-size: 12px;
        font-weight: 800;
        padding: 4px 9px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        display: inline-block;
      }
      .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        margin-bottom: 12px;
      }
      .stat-kpi {
        background: #fff;
        border: 1px solid var(--admin-line);
        border-radius: 14px;
        padding: 12px 14px;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.05);
      }
      .stat-kpi .num {
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.5px;
        line-height: 1.1;
      }
      .stat-kpi .lbl {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        margin-top: 4px;
      }
      .stat-kpi.accent {
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        border-color: transparent;
        color: #fff;
      }
      .stat-kpi.accent .lbl { color: rgba(255, 255, 255, 0.88); }
      .mini-table td { font-size: 14px; }
      @media (max-width: 900px) {
        .admin-layout { flex-direction: column; }
        .admin-sidebar {
          width: 100%;
          flex-direction: row;
          flex-wrap: wrap;
          align-items: center;
        }
        .admin-nav { flex-direction: row; flex-wrap: wrap; flex: 1; }
        .admin-sidebar__foot { width: 100%; margin-top: 0; border-top: none; padding-top: 8px; }
        .grid-2 { grid-template-columns: 1fr; }
      }
    </style>
  </head>
  <body>
    <div class="admin-layout">
      <aside class="admin-sidebar" aria-label="Menú administración">
        <div class="admin-sidebar__brand">Hospital Online</div>
        <div class="admin-sidebar__tag">Administración del catálogo y citas</div>
        <nav class="admin-nav">
          <a href="{{ route('admin.dashboard') }}">Dashboard</a>
          <a href="{{ route('admin.especialidades.index') }}">Especialidades</a>
          <a href="{{ route('admin.medicos.index') }}">Médicos</a>
          <a href="{{ route('admin.servicios.index') }}">Servicios</a>
          <a href="{{ route('admin.solicitudes-citas.index') }}">Solicitudes de citas</a>
        </nav>
        <div class="admin-sidebar__foot">
          Los cambios en el catálogo se reflejan en la web pública al <strong>recargar</strong> la página del sitio NovaSalud (frontend estático + API).
          <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 6px;">
            <a href="{{ route('web.public') }}">Sitio web NovaSalud</a>
            <a href="{{ url('/') }}" target="_blank" rel="noopener" style="opacity: 0.85">Inicio Laravel (API)</a>
          </div>
        </div>
      </aside>
      <div class="admin-main">
        <header class="admin-topbar">
          <div style="font-weight: 800; font-size: 16px; letter-spacing: -0.3px;">@yield('title', 'Administración')</div>
          <div style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
            <span class="muted" style="font-size:14px;">{{ auth()->user()->email }}</span>
            <form method="post" action="{{ route('logout') }}" style="margin:0;">
              @csrf
              <button type="submit" class="btn btn-soft" style="cursor:pointer;">Cerrar sesión</button>
            </form>
            <a class="btn btn-soft" href="{{ route('web.public') }}">Ver sitio web</a>
          </div>
        </header>
        <div class="admin-content">
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
  </body>
</html>
