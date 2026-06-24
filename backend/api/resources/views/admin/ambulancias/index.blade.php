@extends('admin.layout')

@section('title', 'Ambulancias')
@section('page_subtitle', 'Unidades disponibles, despacho y seguimiento de rutas.')

@section('content')
  @php
    $mapsKey = (string) ($mapsKey ?? config('services.google_maps.key', ''));
    $googleMapsOk = (bool) ($googleMapsOk ?? false);
    $estadoBadge = [
      'disponible' => 'badge--success',
      'en_ruta' => 'badge--warning',
      'mantenimiento' => 'badge--muted',
    ];
  @endphp

  @if (($allowDispatchWithoutRoute ?? false) || ($mapsKey !== '' && ! $googleMapsOk) || $mapsKey === '')
    <div class="callout" style="margin-bottom: 14px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      <div>
        <strong>Rutas con OpenStreetMap.</strong>
        El mapa traza el recorrido desde la clínica en Huancayo.
        @if ($mapsKey !== '' && ! $googleMapsOk)
          {{ $mapsDiagnostico['mensaje'] ?? 'La clave de Google Maps no está activa.' }}
          Para usar Google Maps, actualiza <code>GOOGLE_MAPS_API_KEY</code> en <code>.env</code>.
        @elseif ($mapsKey === '')
          Puedes definir <code>GOOGLE_MAPS_API_KEY</code> en <code>.env</code> si prefieres Google en producción.
        @endif
      </div>
    </div>
  @endif

  @if (session('status'))
    <div class="callout" style="margin-bottom: 14px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <div>{{ session('status') }}</div>
    </div>
  @endif

  @if ($errors->any())
    <div class="callout callout--error" style="margin-bottom: 14px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div>
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    </div>
  @endif

  <div class="stats-row" style="margin-bottom: 16px;">
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--teal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v5h-3"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $stats['disponibles'] }}</div>
        <div class="lbl">Disponibles</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--orange">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18"/><path d="M7 8l-4 4 4 4"/><path d="M17 16l4-4-4-4"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $stats['en_ruta'] }}</div>
        <div class="lbl">En ruta (despachadas)</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--purple">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $stats['mantenimiento'] }}</div>
        <div class="lbl">Mantenimiento</div>
      </div>
    </div>
  </div>

  <div class="page-toolbar">
    <div class="muted">Origen clínica: {{ $origen['direccion'] }} · {{ $origen['ciudad'] }} ({{ $origen['lat'] }}, {{ $origen['lng'] }})</div>
    <a class="btn btn-primary" href="{{ route('admin.ambulancias.create') }}">+ Nueva ambulancia</a>
  </div>

  <div class="dash-grid-bottom" style="margin-bottom: 16px;">
    <section class="card">
      <h2>Despachar ambulancia</h2>
      <p class="card-desc">Elige código, destino (lat/lng) y calcula la ruta con Google Maps antes de enviar.</p>

      <form method="POST" action="#" id="form-preview" class="row" style="align-items: end; gap: 12px; flex-wrap: wrap;">
        <div class="field" style="min-width: 160px;">
          <label>Latitud destino</label>
          <input type="text" name="destino_lat" id="destino_lat" value="{{ old('destino_lat', '-12.0910') }}" placeholder="-12.0910" required />
        </div>
        <div class="field" style="min-width: 160px;">
          <label>Longitud destino</label>
          <input type="text" name="destino_lng" id="destino_lng" value="{{ old('destino_lng', '-75.2180') }}" placeholder="-75.2180" required />
        </div>
        <div class="field" style="min-width: 220px; flex: 1;">
          <label>Dirección destino (opcional)</label>
          <input type="text" name="destino_direccion_preview" id="destino_direccion_preview" placeholder="Ej. Av. Progreso, El Tambo, Huancayo" />
        </div>
        <button class="btn" type="button" id="btn-preview">Calcular ruta</button>
      </form>

      <div id="ruta-preview" class="muted" style="margin-top: 12px; display: none;"></div>

      <form method="POST" action="#" id="form-despacho" style="margin-top: 16px;">
        @csrf
        <div class="row" style="align-items: end; gap: 12px; flex-wrap: wrap;">
          <div class="field" style="min-width: 180px;">
            <label>Código ambulancia</label>
            <select name="ambulancia_id" id="ambulancia_id" required>
              <option value="">Selecciona…</option>
              @foreach ($ambulancias->where('estado', 'disponible') as $a)
                <option value="{{ $a->id }}">{{ $a->codigo }} @if($a->placa) ({{ $a->placa }}) @endif</option>
              @endforeach
            </select>
          </div>
          <div class="field" style="min-width: 200px;">
            <label>Conductor</label>
            <input type="text" name="conductor" placeholder="Opcional" />
          </div>
          <input type="hidden" name="destino_lat" id="despacho_destino_lat" />
          <input type="hidden" name="destino_lng" id="despacho_destino_lng" />
          <input type="hidden" name="destino_direccion" id="despacho_destino_direccion" />
          <button class="btn btn-primary" type="submit" id="btn-despachar" disabled>Despachar</button>
        </div>
      </form>
    </section>

    <section class="card">
      <h2>Mapa de ruta</h2>
      <p class="card-desc">Vista embebida de Google Maps con el recorrido desde la clínica hasta el destino.</p>
      <div class="mapa-ruta-wrap">
        <iframe
          id="mapa-ruta"
          title="Mapa de ruta"
          src="{{ $mapEmbedInicial }}"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          allowfullscreen
        ></iframe>
      </div>
      <p style="margin-top: 10px;">
        <a class="btn btn-soft" id="maps-link" href="https://www.google.com/maps/dir/?api=1&origin={{ $origen['lat'] }},{{ $origen['lng'] }}&destination=-12.0910,-75.2180&travelmode=driving" target="_blank" rel="noopener">Abrir en Google Maps</a>
      </p>
    </section>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Flota</h2>
    <table class="table">
      <thead>
        <tr>
          <th>Código</th>
          <th>Placa</th>
          <th>Conductor</th>
          <th>Estado</th>
          <th>Destino / ruta</th>
          <th>Despachada</th>
          <th style="width: 240px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($ambulancias as $a)
          <tr>
            <td><strong>{{ $a->codigo }}</strong></td>
            <td class="muted">{{ $a->placa ?? '—' }}</td>
            <td>{{ $a->conductor ?? '—' }}</td>
            <td><span class="badge {{ $estadoBadge[$a->estado] ?? 'badge--muted' }}">{{ $a->etiquetaEstado() }}</span></td>
            <td class="muted">
              @if ($a->estaEnRuta())
                {{ $a->destino_direccion ?? ($a->destino_lat.', '.$a->destino_lng) }}
                @if ($a->ruta_resumen)
                  <br><small>{{ $a->ruta_resumen }}</small>
                @endif
              @else
                —
              @endif
            </td>
            <td class="muted">
              @if ($a->despachada_at && $a->estaEnRuta())
                {{ $a->despachada_at->format('d/m/Y H:i') }}
              @elseif ($a->regreso_at)
                Regresó {{ $a->regreso_at->format('d/m/Y H:i') }}
              @else
                —
              @endif
            </td>
            <td>
              <div class="row">
                @if ($a->estaEnRuta())
                  <form method="POST" action="{{ route('admin.ambulancias.regresar', $a) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-soft" type="submit">Marcar regreso</button>
                  </form>
                  @if ($a->destino_lat && $a->destino_lng)
                    <a class="btn" href="https://www.google.com/maps/dir/?api=1&origin={{ $origen['lat'] }},{{ $origen['lng'] }}&destination={{ $a->destino_lat }},{{ $a->destino_lng }}&travelmode=driving" target="_blank" rel="noopener">Ver ruta</a>
                  @endif
                @endif
                <a class="btn" href="{{ route('admin.ambulancias.edit', $a) }}">Editar</a>
                <form method="POST" action="{{ route('admin.ambulancias.destroy', $a) }}" data-admin-delete>
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger" type="submit">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted">Sin ambulancias. Ejecuta el seeder o crea una nueva.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

@push('styles')
  <style>
    .mapa-ruta-wrap {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      background: #e2e8f0;
      border: 1px solid var(--ns-line);
      height: 320px;
    }
    #mapa-ruta {
      display: block;
      width: 100%;
      height: 100%;
      border: 0;
    }
  </style>
@endpush

@push('scripts')
  <script>
    (function () {
      var previewUrl = @json(route('admin.ambulancias.preview-ruta'));
      var csrf = @json(csrf_token());
      var origen = @json($origen);
      var mapsKey = @json($mapsKey);
      var googleMapsOk = @json($googleMapsOk);
      var allowDispatchWithoutRoute = @json($allowDispatchWithoutRoute ?? false);
      var destinoLat = document.getElementById('destino_lat');
      var destinoLng = document.getElementById('destino_lng');
      var previewBox = document.getElementById('ruta-preview');
      var btnPreview = document.getElementById('btn-preview');
      var btnDespachar = document.getElementById('btn-despachar');
      var formDespacho = document.getElementById('form-despacho');
      var ambulanciaSelect = document.getElementById('ambulancia_id');
      var mapsLink = document.getElementById('maps-link');
      var direccionPreview = document.getElementById('destino_direccion_preview');
      var mapIframe = document.getElementById('mapa-ruta');

      function legacyGoogleEmbedUrl(destLat, destLng) {
        return 'https://www.google.com/maps?f=d&hl=es'
          + '&saddr=' + encodeURIComponent(origen.lat + ',' + origen.lng)
          + '&daddr=' + encodeURIComponent(destLat + ',' + destLng)
          + '&output=embed';
      }

      function updateMapEmbed(data) {
        if (!mapIframe) return;

        var destLat = parseFloat(data.destino_lat);
        var destLng = parseFloat(data.destino_lng);
        if (!isFinite(destLat) || !isFinite(destLng)) return;

        mapIframe.src = data.map_embed_url || legacyGoogleEmbedUrl(destLat, destLng);
      }

      function updateMapsLink(lat, lng) {
        if (!mapsLink) return;
        mapsLink.href = 'https://www.google.com/maps/dir/?api=1&origin=' + encodeURIComponent(origen.lat + ',' + origen.lng)
          + '&destination=' + encodeURIComponent(lat + ',' + lng) + '&travelmode=driving';
      }

      function applyPreviewResult(data) {
        if (data.destino_lat != null) destinoLat.value = data.destino_lat;
        if (data.destino_lng != null) destinoLng.value = data.destino_lng;
        if (data.destino_direccion) direccionPreview.value = data.destino_direccion;
        updateMapEmbed(data);
        updateMapsLink(data.destino_lat, data.destino_lng);
      }

      function syncDespachoFields() {
        document.getElementById('despacho_destino_lat').value = destinoLat.value.trim();
        document.getElementById('despacho_destino_lng').value = destinoLng.value.trim();
        document.getElementById('despacho_destino_direccion').value = direccionPreview.value.trim();
      }

      function canEnableDispatch() {
        syncDespachoFields();
        var hasCoords = destinoLat.value.trim() !== '' && destinoLng.value.trim() !== '';
        return ambulanciaSelect.value && hasCoords;
      }

      function refreshDispatchButton() {
        btnDespachar.disabled = !canEnableDispatch();
      }

      function requestRoutePreview() {
        syncDespachoFields();
        previewBox.style.display = 'block';
        previewBox.textContent = 'Calculando ruta…';
        btnDespachar.disabled = true;

        return fetch(previewUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({
            destino_lat: parseFloat(destinoLat.value),
            destino_lng: parseFloat(destinoLng.value),
            destino_direccion: direccionPreview.value.trim()
          })
        })
          .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
          .then(function (result) {
            if (!result.ok || !result.data.ok) {
              var msg = result.data.mensaje || 'No se pudo calcular la ruta.';
              if (allowDispatchWithoutRoute) {
                previewBox.innerHTML = msg + '<br><strong>Puedes despachar igual</strong> con las coordenadas indicadas (modo local).';
                updateMapEmbed({
                  destino_lat: destinoLat.value,
                  destino_lng: destinoLng.value,
                });
                refreshDispatchButton();
                return;
              }
              previewBox.textContent = msg;
              return;
            }
            var d = result.data;
            applyPreviewResult(d);
            previewBox.innerHTML = '<strong>' + (d.resumen || 'Ruta calculada') + '</strong>'
              + (d.origen_direccion ? '<br>Desde: ' + d.origen_direccion : '')
              + (d.destino_direccion ? '<br>Hasta: ' + d.destino_direccion : '')
              + (d.proveedor === 'osrm' ? '<br><small>Ruta trazada con OpenStreetMap.</small>' : '');
            if (d.maps_url) {
              previewBox.innerHTML += '<br><a href="' + d.maps_url + '" target="_blank" rel="noopener">Abrir en Google Maps</a>';
            }
            refreshDispatchButton();
          })
          .catch(function () {
            if (allowDispatchWithoutRoute) {
              previewBox.innerHTML = 'Error de red al consultar la ruta.<br><strong>Puedes despachar igual</strong> con las coordenadas indicadas (modo local).';
              refreshDispatchButton();
              return;
            }
            previewBox.textContent = 'Error de red al consultar la ruta.';
          });
      }

      btnPreview.addEventListener('click', function () {
        requestRoutePreview();
      });

      direccionPreview.addEventListener('change', function () {
        if (direccionPreview.value.trim() !== '') {
          requestRoutePreview();
        }
      });

      window.addEventListener('load', function () {
        requestRoutePreview();
      });

      ambulanciaSelect.addEventListener('change', refreshDispatchButton);
      destinoLat.addEventListener('input', refreshDispatchButton);
      destinoLng.addEventListener('input', refreshDispatchButton);

      formDespacho.addEventListener('submit', function (e) {
        e.preventDefault();
        syncDespachoFields();
        var id = ambulanciaSelect.value;
        if (!id) return;
        formDespacho.action = @json(url('/admin/ambulancias')) + '/' + id + '/despachar';
        formDespacho.submit();
      });
    })();
  </script>
@endpush
