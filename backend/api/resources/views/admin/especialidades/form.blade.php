@extends('admin.layout')

@section('title', $especialidad->exists ? 'Editar especialidad' : 'Nueva especialidad')

@section('page_subtitle', 'Completa la información y guarda.')

@section('content')
  <div class="page-toolbar">
    <div></div>
    <a class="btn btn-soft" href="{{ route('admin.especialidades.index') }}">← Volver</a>
  </div>

  <div class="card">
    <form method="POST"
      action="{{ $especialidad->exists ? route('admin.especialidades.update', $especialidad) : route('admin.especialidades.store') }}"
      enctype="multipart/form-data">
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
          <label>Imagen (subir archivo)</label>
          <input type="file" name="imagen_file" accept="image/*" />

          <input
            type="hidden"
            name="imagen_actual"
            value="{{ old('imagen_actual', $especialidad->getRawOriginal('imagen')) }}"
          />

          @if ($especialidad->imagen)
            <div style="margin-top:10px;">
              <div class="muted" style="margin-bottom:6px;">Vista previa (actual):</div>
              <img
                src="{{ $especialidad->imagen }}"
                alt="Imagen especialidad"
                style="max-width: 160px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.08);"
              />
            </div>
          @endif
          @error('imagen_file')<div class="muted">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="row" style="margin-top: 12px;">
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
@endsection

