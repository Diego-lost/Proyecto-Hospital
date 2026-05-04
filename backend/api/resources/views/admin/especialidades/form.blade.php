@extends('admin.layout')

@section('title', $especialidad->exists ? 'Editar especialidad' : 'Nueva especialidad')

@section('content')
  <div class="row" style="justify-content: space-between; margin-bottom: 12px;">
    <div>
      <h1 style="margin:0;">
        {{ $especialidad->exists ? 'Editar especialidad' : 'Nueva especialidad' }}
      </h1>
      <div class="muted">Completa la información y guarda.</div>
    </div>
    <a class="btn" href="{{ route('admin.especialidades.index') }}">Volver</a>
  </div>

  <div class="card">
    <form method="POST"
      action="{{ $especialidad->exists ? route('admin.especialidades.update', $especialidad) : route('admin.especialidades.store') }}">
      @csrf
      @if ($especialidad->exists)
        @method('PUT')
      @endif

      <div class="grid grid-2">
        <div class="field">
          <label>Nombre</label>
          <input name="nombre" value="{{ old('nombre', $especialidad->nombre) }}" required />
          @error('nombre')<div class="muted">{{ $message }}</div>@enderror
        </div>
        <div class="field">
          <label>Imagen (ruta o nombre archivo)</label>
          <input name="imagen" value="{{ old('imagen', $especialidad->imagen) }}" />
          @error('imagen')<div class="muted">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="row" style="margin-top: 12px;">
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
@endsection

