@extends('admin.layout')

@section('title', 'Resumen')
@section('page_subtitle', 'Panel de administración')

@section('content')
  @php
    $estadoEtiquetas = [
      'nueva' => 'Nueva',
      'reprogramada' => 'Reprogramada',
      'cancelada' => 'Cancelada',
    ];
    $estadoColores = [
      'nueva' => '#2563eb',
      'reprogramada' => '#d97706',
      'cancelada' => '#dc2626',
    ];
    $maxEstado = (int) $solicitudesPorEstado->max('total');
    $proximasHoy = $proximasCitas->filter(function ($c) {
      return $c->fecha && \Illuminate\Support\Carbon::parse($c->fecha)->isToday();
    })->count();
    $conteoPorEstado = $solicitudesPorEstado->keyBy('estado');
    $miniNueva = (int) ($conteoPorEstado->get('nueva')?->total ?? 0);
    $miniReprog = (int) ($conteoPorEstado->get('reprogramada')?->total ?? 0);
    $miniCancel = (int) ($conteoPorEstado->get('cancelada')?->total ?? 0);
    $miniTotal = $solicitudesTotal;
  @endphp

  @if (! empty($dashboardDbError))
    <div class="callout callout--error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div>
        <strong>No se pudieron cargar los datos del resumen</strong>
        <p class="muted" style="margin:8px 0 0;">{{ $dashboardDbError }}</p>
        <p class="muted" style="margin:8px 0 0;">
          Puedes usar el menú lateral para ir a Especialidades / Médicos / Servicios. Usa siempre la misma URL con la que iniciaste sesión.
        </p>
        @if (config('app.debug') && app()->isLocal() && str_contains((string) $dashboardDbError, 'could not find driver'))
          <p class="muted" style="margin:12px 0 0;">
            <strong>Diagnóstico:</strong>
            <a href="{{ url('/__nova/php-db-check') }}" target="_blank" rel="noopener">/__nova/php-db-check</a>
          </p>
        @endif
      </div>
    </div>
  @endif

  <div class="callout">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <div>
      <strong>Este es el panel (backend):</strong> aquí agregas, editas o eliminas especialidades, médicos, servicios y gestionas solicitudes de cita.
      El <strong>sitio web público</strong> es aparte (React); consume la misma base de datos por la API.
      Tras guardar cambios, abre <strong>«Ver sitio público»</strong> en el menú o pide al visitante <strong>recargar</strong> la página.
    </div>
  </div>

  <div class="stats-row">
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--teal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $solicitudesTotal }}</div>
        <div class="lbl">Solicitudes totales</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--orange">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $proximasHoy }}</div>
        <div class="lbl">Próximas hoy</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--purple">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $catalogo['medicos'] }}</div>
        <div class="lbl">Médicos activos</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $catalogo['especialidades'] }}</div>
        <div class="lbl">Especialidades</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $catalogo['servicios'] }}</div>
        <div class="lbl">Servicios</div>
      </div>
    </div>
    <div class="stat-kpi">
      <div class="stat-kpi__icon stat-kpi__icon--red">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="stat-kpi__body">
        <div class="num">{{ $solicitudesHoy }}</div>
        <div class="lbl">Recibidas hoy</div>
      </div>
    </div>
  </div>

  <div class="dash-grid-main">
    <section class="card">
      <h2>Solicitudes por estado</h2>
      <p class="card-desc">Distribución según el flujo de gestión.</p>
      @if ($solicitudesPorEstado->isEmpty())
        <p class="muted" style="margin:0;">Aún no hay solicitudes registradas.</p>
      @else
        <div class="bar-chart" role="img" aria-label="Gráfico de solicitudes por estado">
          @foreach ($solicitudesPorEstado as $fila)
            @php
              $pct = $maxEstado > 0 ? max(8, round(($fila->total / $maxEstado) * 100)) : 8;
            @endphp
            <div class="bar-chart__col">
              <div class="bar-chart__bar" style="height: {{ $pct }}px;"></div>
              <span class="bar-chart__label">{{ $estadoEtiquetas[$fila->estado] ?? $fila->estado }}</span>
            </div>
          @endforeach
        </div>
        <ul class="status-list">
          @foreach ($solicitudesPorEstado as $fila)
            <li>
              <span class="status-list__left">
                <span class="status-list__dot" style="background: {{ $estadoColores[$fila->estado] ?? '#94a3b8' }};"></span>
                {{ $estadoEtiquetas[$fila->estado] ?? $fila->estado }}
              </span>
              <strong>{{ $fila->total }}</strong>
            </li>
          @endforeach
        </ul>
      @endif
    </section>

    <section class="card">
      <h2>Próximas citas</h2>
      <p class="card-desc">Solicitudes nuevas o reprogramadas con fecha desde hoy.</p>
      @if ($proximasCitas->isEmpty())
        <p class="muted" style="margin:0;">No hay citas próximas en el listado.</p>
      @else
        <ul class="appt-list">
          @foreach ($proximasCitas as $c)
            @php
              $partes = preg_split('/\s+/', trim((string) $c->nombre), 2);
              $iniciales = strtoupper(mb_substr($partes[0] ?? '?', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
              $estadoClass = match ($c->estado) {
                'nueva' => 'badge--nueva',
                'reprogramada' => 'badge--reprogramada',
                'cancelada' => 'badge--cancelada',
                default => '',
              };
            @endphp
            <li class="appt-item">
              <div class="appt-avatar" aria-hidden="true">{{ $iniciales }}</div>
              <div class="appt-item__body">
                <div class="appt-item__name">{{ $c->nombre }}</div>
                <div class="appt-item__meta">
                  {{ $c->especialidad ?? 'Sin especialidad' }}
                  @if ($c->fecha)
                    · {{ \Illuminate\Support\Carbon::parse($c->fecha)->format('d/m/Y') }}
                    @if ($c->hora)
                      {{ \Illuminate\Support\Carbon::parse($c->hora)->format('H:i') }}
                    @endif
                  @endif
                </div>
              </div>
              <span class="badge {{ $estadoClass }}">{{ $estadoEtiquetas[$c->estado] ?? $c->estado }}</span>
            </li>
          @endforeach
        </ul>
      @endif
    </section>
  </div>

  <div class="dash-grid-bottom">
    <section class="card">
      <h2>Catálogo</h2>
      <p class="card-desc">Administra la información base del sitio público.</p>
      <div class="catalog-tabs" role="tablist" aria-label="Secciones del catálogo">
        <a href="{{ route('admin.especialidades.index') }}" class="is-active">Especialidades</a>
        <a href="{{ route('admin.medicos.index') }}">Médicos</a>
        <a href="{{ route('admin.servicios.index') }}">Servicios</a>
      </div>
      <div class="catalog-links">
        <a href="{{ route('admin.especialidades.index') }}">
          Especialidades
          <span class="muted">{{ $catalogo['especialidades'] }} registros</span>
        </a>
        <a href="{{ route('admin.medicos.index') }}">
          Médicos
          <span class="muted">{{ $catalogo['medicos'] }} registros</span>
        </a>
        <a href="{{ route('admin.servicios.index') }}">
          Servicios
          <span class="muted">{{ $catalogo['servicios'] }} registros</span>
        </a>
      </div>
      <div style="margin-top: 14px;">
        <a class="btn btn-soft" href="{{ route('admin.especialidades.create') }}">+ Agregar nuevo</a>
      </div>
    </section>

    <section class="card">
      <h2>Atención</h2>
      <p class="card-desc">Revisa solicitudes recibidas desde la web.</p>
      <div class="mini-stats">
        <div class="mini-stat">
          <div class="num">{{ $miniTotal }}</div>
          <div class="lbl">Solicitudes</div>
        </div>
        <div class="mini-stat">
          <div class="num">{{ $miniReprog }}</div>
          <div class="lbl">Reprogramadas</div>
        </div>
        <div class="mini-stat">
          <div class="num">{{ $miniCancel }}</div>
          <div class="lbl">Canceladas</div>
        </div>
        <div class="mini-stat">
          <div class="num">{{ $miniNueva }}</div>
          <div class="lbl">Nuevas</div>
        </div>
      </div>
      <a class="btn btn-primary btn-block" href="{{ route('admin.solicitudes-citas.index') }}">Gestionar solicitudes de cita</a>
      <div class="sync-note">
        <span class="sync-note__dot" aria-hidden="true"></span>
        <span>Los cambios en el catálogo se reflejan en la web pública al <strong>recargar</strong> la página del sitio NovaSalud (React + API).</span>
      </div>
    </section>

    <section class="card">
      <h2>Ambulancias</h2>
      <p class="card-desc">Unidades disponibles y despachadas con ruta Google Maps.</p>
      <div class="mini-stats">
        <div class="mini-stat">
          <div class="num">{{ $ambulanciasStats['disponibles'] }}</div>
          <div class="lbl">Disponibles</div>
        </div>
        <div class="mini-stat">
          <div class="num">{{ $ambulanciasStats['en_ruta'] }}</div>
          <div class="lbl">En ruta</div>
        </div>
        <div class="mini-stat">
          <div class="num">{{ $ambulanciasStats['total'] }}</div>
          <div class="lbl">Total flota</div>
        </div>
      </div>
      @if ($ambulanciasEnRuta->isNotEmpty())
        <ul class="list-plain" style="margin: 12px 0 0; padding: 0; list-style: none;">
          @foreach ($ambulanciasEnRuta as $amb)
            <li style="display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-top:1px solid var(--ns-line);">
              <div>
                <strong>{{ $amb->codigo }}</strong>
                <div class="muted" style="font-size:12px;">{{ $amb->destino_direccion ?? 'Destino en mapa' }}</div>
              </div>
              <span class="badge badge--warning">En ruta</span>
            </li>
          @endforeach
        </ul>
      @else
        <p class="muted" style="margin-top:10px;">Ninguna ambulancia despachada en este momento.</p>
      @endif
      <a class="btn btn-primary btn-block" href="{{ route('admin.ambulancias.index') }}" style="margin-top:14px;">Gestionar ambulancias</a>
    </section>
  </div>
@endsection
