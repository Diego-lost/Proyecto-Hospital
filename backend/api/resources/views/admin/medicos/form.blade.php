@extends('admin.layout')

@section('title', $medico->exists ? 'Editar médico' : 'Nuevo médico')

@section('content')
  <div class="row" style="justify-content: space-between; margin-bottom: 12px;">
    <div>
      <h1 style="margin:0;">{{ $medico->exists ? 'Editar médico' : 'Nuevo médico' }}</h1>
      <div class="muted">Completa los datos del médico.</div>
    </div>
    <a class="btn" href="{{ route('admin.medicos.index') }}">Volver</a>
  </div>

  <div class="card">
    <form method="POST"
      action="{{ $medico->exists ? route('admin.medicos.update', $medico) : route('admin.medicos.store') }}">
      @csrf
      @if ($medico->exists)
        @method('PUT')
      @endif

      <div class="grid grid-2">
        <div class="field">
          <label>Nombre</label>
          <input name="nombre" value="{{ old('nombre', $medico->nombre) }}" required />
          @error('nombre')<div class="muted">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="medico-dni-input">DNI (solo números; único para búsqueda en la web)</label>
          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:stretch;">
            <input id="medico-dni-input" name="dni" value="{{ old('dni', $medico->dni) }}" inputmode="numeric" maxlength="8" placeholder="8 dígitos" style="flex:1; min-width:140px; padding:10px 12px; border-radius:10px; border:1px solid var(--admin-line);" />
            <button type="button" class="btn" id="medico-reniec-buscar" style="flex-shrink:0;">Buscar en RENIEC</button>
          </div>
          @error('dni')<div class="muted">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label>Especialidad</label>
          <select name="especialidad_id" required>
            <option value="" disabled @selected(!old('especialidad_id', $medico->especialidad_id))>Selecciona</option>
            @foreach ($especialidades as $e)
              <option value="{{ $e->id }}" @selected(old('especialidad_id', $medico->especialidad_id) == $e->id)>
                {{ $e->nombre }}
              </option>
            @endforeach
          </select>
          @error('especialidad_id')<div class="muted">{{ $message }}</div>@enderror
        </div>

        <div class="field" style="grid-column: 1 / -1;">
          <label>Foto (ruta o nombre archivo)</label>
          <input name="foto" value="{{ old('foto', $medico->foto) }}" />
          @error('foto')<div class="muted">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="row" style="margin-top: 12px;">
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>

  <script>
    (function () {
      var btn = document.getElementById("medico-reniec-buscar");
      var dniInput = document.getElementById("medico-dni-input");
      var nombreInput = document.querySelector('input[name="nombre"]');
      if (!btn || !dniInput || !nombreInput) return;
      var urlBase = @json(url('/api/busqueda/reniec'));
      btn.addEventListener("click", function () {
        var dni = String(dniInput.value || "").replace(/\s/g, "");
        if (dni.length !== 8 || !/^\d+$/.test(dni)) {
          alert("Ingresa un DNI de 8 dígitos.");
          return;
        }
        var prev = btn.textContent;
        btn.disabled = true;
        btn.textContent = "…";
        fetch(urlBase + "?dni=" + encodeURIComponent(dni), {
          headers: { Accept: "application/json" },
        })
          .then(function (r) {
            return r.json().then(function (j) {
              return { ok: r.ok, body: j };
            });
          })
          .then(function (x) {
            if (!x.ok && x.body && x.body.message) {
              alert(x.body.message);
              return;
            }
            if (!x.body || !x.body.encontrado || !x.body.datos || !x.body.datos.nombre) {
              var det = x.body && x.body.detalle ? x.body.detalle : "";
              var msgs = {
                sin_token: "Falta CONSULTASPERU_API_TOKEN en .env del servidor.",
                dni_invalido: "El DNI debe tener 8 dígitos.",
                red: "Error de conexión con Consultas Perú.",
                no_autorizado: "Token inválido o vencido (CONSULTASPERU_API_TOKEN).",
                error_http: "El servicio respondió con error.",
                sin_datos: "No hay datos en RENIEC para ese DNI.",
              };
              alert(msgs[det] || msgs.sin_datos);
              return;
            }
            nombreInput.value = x.body.datos.nombre;
          })
          .catch(function () {
            alert("No se pudo consultar RENIEC. Revisa la red y la URL del sitio.");
          })
          .finally(function () {
            btn.disabled = false;
            btn.textContent = prev;
          });
      });
    })();
  </script>
@endsection

