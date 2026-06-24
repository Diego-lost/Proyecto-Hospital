@extends('admin.layout')

@section('title', 'Solicitudes de citas')
@section('page_subtitle', 'Bandeja de solicitudes recibidas desde la web.')

@section('content')

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>DNI pac.</th>
          <th>Dirección</th>
          <th>Paciente</th>
          <th>Contacto</th>
          <th>Médico</th>
          <th>Especialidad</th>
          <th>Fecha/Hora</th>
          <th>Pago</th>
          <th>Estado</th>
          <th style="width: 320px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($solicitudes as $s)
          <tr>
            <td>{{ $s->id }}</td>
            <td class="muted">{{ $s->paciente_dni ?? '—' }}</td>
            <td class="muted" style="max-width: 200px;">{{ $s->paciente_direccion ?? '—' }}</td>
            <td>
              <div style="font-weight: 800;">{{ $s->nombre }}</div>
              <div class="muted">{{ $s->motivo ?? '—' }}</div>
            </td>
            <td>
              <div>{{ $s->telefono }}</div>
              <div class="muted">{{ $s->email ?? '—' }}</div>
            </td>
            <td class="muted">
              @if ($s->medico)
                {{ $s->medico->nombre }} ({{ $s->medico->dni ?? 'sin DNI' }})
              @else
                —
              @endif
            </td>
            <td class="muted">{{ $s->especialidad ?? '—' }}</td>
            <td class="muted">
              {{ $s->fecha ?? '—' }}
              {{ $s->hora ?? '' }}
            </td>
            <td class="muted">
              @if ($s->pago)
                <div><strong>#{{ $s->pago->id }}</strong> — {{ $s->pago->estado }}</div>
                <div>{{ strtoupper($s->pago->moneda ?? 'pen') }} {{ number_format((float) $s->pago->monto, 2) }}</div>
                <div class="muted">{{ $s->pago->metodo ?? '—' }}</div>
              @else
                —
              @endif
            </td>
            <td><span class="badge">{{ $s->estado }}</span></td>
            <td>
              <div class="grid" style="gap: 10px;">
                <form class="row" method="POST" action="{{ route('admin.solicitudes-citas.reprogramar', $s) }}">
                  @csrf
                  @method('PATCH')
                  <input type="date" name="fecha" value="{{ $s->fecha }}" required />
                  <input type="time" name="hora" value="{{ is_string($s->hora) ? substr($s->hora,0,5) : '' }}" required />
                  <button class="btn btn-primary" type="submit">Reprogramar</button>
                </form>

                <form class="row" method="POST" action="{{ route('admin.solicitudes-citas.cancelar', $s) }}">
                  @csrf
                  @method('PATCH')
                  <input
                    name="motivo_cancelacion"
                    placeholder="Motivo (opcional)"
                    value="{{ $s->motivo_cancelacion }}"
                  />
                  <button class="btn btn-danger" type="submit">Cancelar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="11" class="muted">Sin registros.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

