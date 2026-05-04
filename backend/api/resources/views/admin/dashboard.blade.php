@extends('admin.layout')

@section('title', 'Resumen')

@section('content')
  @php
    $estadoEtiquetas = [
      'nueva' => 'Nueva',
      'reprogramada' => 'Reprogramada',
      'cancelada' => 'Cancelada',
    ];
  @endphp
  <div class="callout">
    <strong>Flujo recomendado:</strong> edita especialidades, médicos y servicios desde este panel.
    El sitio web NovaSalud (carpeta <code>frontend</code>) lee los datos por API; pide a los
    visitantes <strong>actualizar la página</strong> para ver los cambios.
  </div>

  <p class="muted" style="margin:0 0 16px;">Indicadores del catálogo y de las solicitudes de cita recibidas.</p>

  <div class="stats-row">
    <div class="stat-kpi accent">
      <div class="num">{{ $solicitudesTotal }}</div>
      <div class="lbl">Solicitudes (total)</div>
    </div>
    <div class="stat-kpi">
      <div class="num">{{ $solicitudesUltimos30Dias }}</div>
      <div class="lbl">Últimos 30 días</div>
    </div>
    <div class="stat-kpi">
      <div class="num">{{ $solicitudesHoy }}</div>
      <div class="lbl">Recibidas hoy</div>
    </div>
    <div class="stat-kpi">
      <div class="num">{{ $catalogo['especialidades'] }}</div>
      <div class="lbl">Especialidades</div>
    </div>
    <div class="stat-kpi">
      <div class="num">{{ $catalogo['medicos'] }}</div>
      <div class="lbl">Médicos</div>
    </div>
    <div class="stat-kpi">
      <div class="num">{{ $catalogo['servicios'] }}</div>
      <div class="lbl">Servicios</div>
    </div>
  </div>

  <div class="grid grid-2" style="margin-bottom: 12px;">
    <section class="card">
      <h2 style="margin:0 0 8px;">Solicitudes por estado</h2>
      <p class="muted" style="margin:0 0 12px;">Distribución según el flujo de gestión.</p>
      @if ($solicitudesPorEstado->isEmpty())
        <p class="muted" style="margin:0;">Aún no hay solicitudes registradas.</p>
      @else
        <table class="table mini-table">
          <thead>
            <tr>
              <th>Estado</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($solicitudesPorEstado as $fila)
              <tr>
                <td>
                  <span class="badge">{{ $estadoEtiquetas[$fila->estado] ?? $fila->estado }}</span>
                </td>
                <td style="font-weight: 800;">{{ $fila->total }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </section>

    <section class="card">
      <h2 style="margin:0 0 8px;">Próximas citas (pendientes)</h2>
      <p class="muted" style="margin:0 0 12px;">Solicitudes nuevas o reprogramadas con fecha desde hoy.</p>
      @if ($proximasCitas->isEmpty())
        <p class="muted" style="margin:0;">No hay citas próximas en el listado.</p>
      @else
        <table class="table mini-table">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Fecha</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($proximasCitas as $c)
              <tr>
                <td>{{ $c->nombre }}</td>
                <td>
                  @if ($c->fecha)
                    {{ \Illuminate\Support\Carbon::parse($c->fecha)->format('d/m/Y') }}
                    @if ($c->hora)
                      {{ \Illuminate\Support\Carbon::parse($c->hora)->format('H:i') }}
                    @endif
                  @else
                    —
                  @endif
                </td>
                <td><span class="badge">{{ $estadoEtiquetas[$c->estado] ?? $c->estado }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </section>
  </div>

  <div class="grid grid-2">
    <section class="card">
      <h2 style="margin:0 0 8px;">Catálogo</h2>
      <p class="muted" style="margin:0 0 12px;">
        Administra la información base: especialidades, médicos y servicios.
      </p>
      <div class="row">
        <a class="btn btn-primary" href="{{ route('admin.especialidades.index') }}">Especialidades</a>
        <a class="btn btn-primary" href="{{ route('admin.medicos.index') }}">Médicos</a>
        <a class="btn btn-primary" href="{{ route('admin.servicios.index') }}">Servicios</a>
      </div>
    </section>

    <section class="card">
      <h2 style="margin:0 0 8px;">Atención</h2>
      <p class="muted" style="margin:0 0 12px;">
        Revisa solicitudes recibidas desde la web y gestiona cancelaciones / reprogramaciones.
      </p>
      <div class="row">
        <a class="btn btn-primary" href="{{ route('admin.solicitudes-citas.index') }}">Solicitudes de citas</a>
      </div>
    </section>
  </div>
@endsection
