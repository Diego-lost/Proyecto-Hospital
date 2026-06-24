@extends('admin.layout')

@section('title', $ambulancia->exists ? 'Editar ambulancia' : 'Nueva ambulancia')
@section('page_subtitle', 'Código único para identificar cada unidad en despacho y regreso.')

@section('content')
  <div class="card" style="max-width: 560px;">
    <form method="POST" action="{{ $ambulancia->exists ? route('admin.ambulancias.update', $ambulancia) : route('admin.ambulancias.store') }}">
      @csrf
      @if ($ambulancia->exists)
        @method('PUT')
      @endif

      <div class="field">
        <label for="codigo">Código de ambulancia *</label>
        <input id="codigo" name="codigo" type="text" value="{{ old('codigo', $ambulancia->codigo) }}" placeholder="AMB-LIM-01" required maxlength="30" />
        <small class="muted">Ej. AMB-LIM-01 — sirve para saber cuál salió y cuál regresó.</small>
      </div>

      <div class="field">
        <label for="placa">Placa</label>
        <input id="placa" name="placa" type="text" value="{{ old('placa', $ambulancia->placa) }}" placeholder="BCP-201" maxlength="20" />
      </div>

      <div class="field">
        <label for="conductor">Conductor</label>
        <input id="conductor" name="conductor" type="text" value="{{ old('conductor', $ambulancia->conductor) }}" maxlength="120" />
      </div>

      <div class="field">
        <label for="estado">Estado *</label>
        <select id="estado" name="estado" required>
          @php
            $estados = [
              'disponible' => 'Disponible',
              'en_ruta' => 'En ruta',
              'mantenimiento' => 'Mantenimiento',
            ];
          @endphp
          @foreach ($estados as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('estado', $ambulancia->estado ?: 'disponible') === $valor)>{{ $etiqueta }}</option>
          @endforeach
        </select>
        @if ($ambulancia->estaEnRuta())
          <small class="muted">Para despachar o marcar regreso usa la pantalla principal de Ambulancias.</small>
        @endif
      </div>

      <div class="row" style="margin-top: 16px;">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn" href="{{ route('admin.ambulancias.index') }}">Cancelar</a>
      </div>
    </form>
  </div>
@endsection
