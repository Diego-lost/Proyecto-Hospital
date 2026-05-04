<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Clínica NovaSalud | Hospital Online</title>
    <style>
      body {
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #0f172a;
      }
      .box {
        max-width: 480px;
        padding: 28px;
        background: #fff;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.1);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
      }
      h1 {
        margin: 0 0 8px;
        font-size: 22px;
        letter-spacing: -0.3px;
      }
      p {
        margin: 0 0 14px;
        line-height: 1.55;
        color: #475569;
        font-size: 15px;
      }
      .muted {
        font-size: 13px;
        color: #64748b;
      }
      a.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 16px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        background: linear-gradient(135deg, #2563eb, #0ea5e9);
        color: #fff;
        margin-right: 8px;
        margin-top: 6px;
      }
      a.btn--ghost {
        background: #fff;
        color: #1e3a8a;
        border: 1px solid rgba(37, 99, 235, 0.35);
      }
      ul {
        margin: 12px 0 0;
        padding-left: 18px;
        color: #475569;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <div class="box">
      <h1>Clínica NovaSalud</h1>
      <p>
        Estás en la raíz de la aplicación Laravel. En XAMPP, la página pública con diseño completo
        vive en la carpeta <strong>frontend</strong> y suele abrirse sola al entrar aquí.
      </p>
      <p class="muted">
        Si ves esta pantalla (por ejemplo con <code>php artisan serve</code>), define
        <code>FRONTEND_URL</code> en <code>.env</code> con la URL del <code>index.html</code> del
        frontend, o abre el HTML directamente desde tu servidor.
      </p>
      <div>
        <a class="btn" href="{{ url('/admin') }}">Panel administrador</a>
        <a class="btn btn--ghost" href="{{ url('/api/status') }}">API /status</a>
      </div>
      @if ($especialidades->isNotEmpty())
        <p style="margin-top: 20px; margin-bottom: 6px; font-weight: 700; color: #0f172a;">Especialidades en base de datos</p>
        <ul>
          @foreach ($especialidades as $e)
            <li>{{ $e->nombre }}</li>
          @endforeach
        </ul>
      @endif
    </div>
  </body>
</html>
