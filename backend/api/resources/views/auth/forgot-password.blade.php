<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recuperar contraseña | Clínica NovaSalud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
      :root { --bg:#00334d; --card:#fff; --text:#0f172a; --muted:#64748b; --accent:#004d6e; --accent2:#14b8a6; --danger:#b91c1c; --line:#e2e8f0; --ok:#166534; }
      * { box-sizing:border-box; }
      body { margin:0; min-height:100vh; font-family:"Inter",system-ui,sans-serif; background:linear-gradient(145deg,var(--bg) 0%,#001a28 100%); display:flex; align-items:center; justify-content:center; padding:24px; }
      .panel { width:100%; max-width:420px; background:var(--card); border:1px solid var(--line); border-radius:16px; padding:28px 26px; box-shadow:0 24px 48px rgba(0,51,77,.25); }
      h1 { margin:0 0 6px; font-size:22px; font-weight:800; }
      .sub { margin:0 0 22px; font-size:14px; color:var(--muted); line-height:1.45; }
      label { display:block; font-size:12px; font-weight:700; color:var(--muted); margin-bottom:6px; }
      input { width:100%; padding:11px 12px; border-radius:10px; border:1px solid var(--line); font:inherit; margin-bottom:14px; }
      button[type="submit"] { width:100%; padding:11px; border:none; border-radius:11px; font-weight:700; color:#fff; background:var(--accent); cursor:pointer; }
      .error,.ok { margin:0 0 14px; padding:10px 12px; border-radius:10px; font-size:14px; }
      .error { background:rgba(239,68,68,.15); border:1px solid rgba(239,68,68,.35); color:var(--danger); }
      .ok { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.35); color:var(--ok); }
      .foot { margin-top:18px; font-size:13px; color:var(--muted); text-align:center; }
      .foot a { color:var(--accent); font-weight:600; }
    </style>
  </head>
  <body>
    <div class="panel">
      <h1>Recuperar contraseña</h1>
      <p class="sub">Esperando confirmación: te enviaremos un enlace a tu correo registrado.</p>

      @if (session('status'))
        <div class="ok" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="error" role="alert">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus />
        <button type="submit">Enviar enlace</button>
      </form>

      <p class="foot">
        <a href="{{ route('login') }}">Volver a iniciar sesión</a>
      </p>
    </div>
  </body>
</html>
