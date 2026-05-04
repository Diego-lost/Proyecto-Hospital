@extends('admin.layout')

@section('title', $servicio->exists ? 'Editar servicio' : 'Nuevo servicio')

@section('content')
  <div class="row" style="justify-content: space-between; margin-bottom: 12px;">
    <div>
      <h1 style="margin:0;">{{ $servicio->exists ? 'Editar servicio' : 'Nuevo servicio' }}</h1>
      <div class="muted">Completa los datos del servicio.</div>
    </div>
    <a class="btn" href="{{ route('admin.servicios.index') }}">Volver</a>
  </div>

  <div class="card">
    <form method="POST"
      action="{{ $servicio->exists ? route('admin.servicios.update', $servicio) : route('admin.servicios.store') }}">
      @csrf
      @if ($servicio->exists)
        @method('PUT')
      @endif

      <div class="grid grid-2">
        <div class="field">
          <label>Nombre</label>
          <input name="nombre" value="{{ old('nombre', $servicio->nombre) }}" required />
          @error('nombre')<div class="muted">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label>Precio</label>
          <input name="precio" inputmode="decimal" value="{{ old('precio', $servicio->precio) }}" required />
          @error('precio')<div class="muted">{{ $message }}</div>@enderror
        </div>

        <div class="field" style="grid-column: 1 / -1;">
          <label>Descripción</label>
          <textarea name="descripcion" rows="4" required>{{ old('descripcion', $servicio->descripcion) }}</textarea>
          @error('descripcion')<div class="muted">{{ $message }}</div>@enderror
        </div>

        <div class="field" style="grid-column: 1 / -1;">
          <label>Médico</label>
          <select name="medico_id" required>
            <option value="" disabled @selected(!old('medico_id', $servicio->medico_id))>Selecciona</option>
            @foreach ($medicos as $m)
              <option value="{{ $m->id }}" @selected(old('medico_id', $servicio->medico_id) == $m->id)>
                {{ $m->nombre }} ({{ $m->especialidad?->nombre ?? '—' }})
              </option>
            @endforeach
          </select>
          @error('medico_id')<div class="muted">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="row" style="margin-top: 12px;">
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
@endsection

