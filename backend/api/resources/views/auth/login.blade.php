<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar sesión | Clínica NovaSalud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
      :root {
        --bg: #00334d;
        --card: #ffffff;
        --text: #0f172a;
        --muted: #64748b;
        --accent: #004d6e;
        --accent2: #14b8a6;
        --danger: #b91c1c;
        --line: #e2e8f0;
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        min-height: 100vh;
        font-family: "Inter", system-ui, sans-serif;
        background: linear-gradient(145deg, var(--bg) 0%, #001a28 100%);
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
        box-shadow: 0 24px 48px rgba(0, 51, 77, 0.25);
      }
      .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
      }
      .brand__logo {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--accent2), #0d9488);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
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
        background: #fff;
        color: var(--text);
        font: inherit;
        margin-bottom: 14px;
      }
      input:focus {
        outline: 2px solid rgba(20, 184, 166, 0.35);
        border-color: var(--accent2);
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
        background: var(--accent);
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
        color: var(--accent);
        font-weight: 600;
      }
    </style>
  </head>
  <body>
    <div class="panel">
      <div class="brand">
        <div class="brand__logo" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <div>
          <h1 style="margin:0;">Clínica NovaSalud</h1>
          <p class="sub" style="margin:4px 0 0;">Accede a tu cuenta</p>
        </div>
      </div>
      <p class="sub" style="margin-top:0;">Pacientes entran al sitio web; administradores al panel.</p>

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
        <p style="margin:-8px 0 14px;font-size:13px;text-align:right;">
          <a href="{{ route('password.request') }}" style="color:var(--accent);font-weight:600;">¿Olvidaste tu contraseña?</a>
        </p>

        <div class="row-check">
          <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }} />
          <label for="remember" style="margin:0;font-weight:600;">Recordarme en este equipo</label>
        </div>

        <button type="submit">Entrar</button>
      </form>

      <p class="foot">
        ¿No tienes cuenta? <a href="{{ route('register') }}">Registrarse</a>
        · <a href="{{ route('web.public') }}">Ver sitio web</a>
      </p>
    </div>
  </body>
</html>
