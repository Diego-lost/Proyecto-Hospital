@extends('admin.layout')

@section('title', 'Servicios')

@section('content')
  <div class="row" style="justify-content: space-between; margin-bottom: 12px;">
    <div>
      <div class="muted">Gestiona servicios y su médico asociado.</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.servicios.create') }}">Nuevo</a>
  </div>

  <div class="card" style="margin-bottom: 12px;">
    <form method="GET" class="row">
      <div class="field" style="min-width: 320px;">
        <label>Filtrar por médico</label>
        <select name="medico_id">
          <option value="">Todos</option>
          @foreach ($medicos as $m)
            <option value="{{ $m->id }}" @selected(request('medico_id') == $m->id)>
              {{ $m->nombre }} ({{ $m->especialidad?->nombre ?? '—' }})
            </option>
          @endforeach
        </select>
      </div>
      <div style="align-self: end;">
        <button class="btn" type="submit">Aplicar</button>
      </div>
    </form>
  </div>

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Médico</th>
          <th>Precio</th>
          <th style="width: 220px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($servicios as $s)
          <tr>
            <td>{{ $s->id }}</td>
            <td>{{ $s->nombre }}</td>
            <td>{{ $s->medico?->nombre ?? '—' }}</td>
            <td>{{ $s->precio }}</td>
            <td>
              <div class="row">
                <a class="btn" href="{{ route('admin.servicios.edit', $s) }}">Editar</a>
                <form method="POST" action="{{ route('admin.servicios.destroy', $s) }}" data-admin-delete>
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger" type="submit">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="muted">Sin registros.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

