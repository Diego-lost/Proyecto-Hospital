<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar sesión | Hospital Online</title>
    <style>
      :root {
        --bg: #0f172a;
        --card: #1e293b;
        --text: #f8fafc;
        --muted: #94a3b8;
        --accent: #3b82f6;
        --accent2: #0ea5e9;
        --danger: #fca5a5;
        --line: rgba(148, 163, 184, 0.2);
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        min-height: 100vh;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        background: linear-gradient(160deg, var(--bg) 0%, #020617 100%);
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
      }
      .panel {
        width: 100%;
        max-width: 400px;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 28px 26px 26px;
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.35);
      }
      h1 {
        margin: 0 0 6px;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.4px;
      }
      .sub {
        margin: 0 0 22px;
        font-size: 14px;
        color: var(--muted);
        line-height: 1.45;
      }
      label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 6px;
      }
      input[type="email"],
      input[type="password"] {
        width: 100%;
        padding: 11px 12px;
        border-radius: 10px;
        border: 1px solid var(--line);
        background: rgba(15, 23, 42, 0.6);
        color: var(--text);
        font: inherit;
        margin-bottom: 14px;
      }
      input:focus {
        outline: 2px solid rgba(59, 130, 246, 0.45);
        outline-offset: 0;
      }
      .row-check {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        font-size: 14px;
        color: var(--muted);
      }
      .row-check input { margin: 0; width: auto; }
      button[type="submit"] {
        width: 100%;
        padding: 11px 14px;
        border: none;
        border-radius: 11px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        color: #fff;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
      }
      button[type="submit"]:hover {
        filter: brightness(1.06);
      }
      .error {
        margin: 0 0 14px;
        padding: 10px 12px;
        border-radius: 10px;
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: var(--danger);
        font-size: 14px;
      }
      .foot {
        margin-top: 18px;
        font-size: 13px;
        color: var(--muted);
        text-align: center;
      }
      .foot a {
        color: #7dd3fc;
        font-weight: 600;
      }
    </style>
  </head>
  <body>
    <div class="panel">
      <h1>Hospital Online</h1>
      <p class="sub">Acceso al panel de administración (catálogo y solicitudes de citas).</p>

      @if ($errors->any())
        <div class="error" role="alert">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="post" action="{{ route('login') }}" autocomplete="off">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus />

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required />

        <div class="row-check">
          <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }} />
          <label for="remember" style="margin:0;font-weight:600;">Recordarme en este equipo</label>
        </div>

        <button type="submit">Entrar</button>
      </form>

      <p class="foot">
        <a href="{{ route('web.public') }}">Volver al sitio público</a>
      </p>
    </div>
  </body>
</html>
