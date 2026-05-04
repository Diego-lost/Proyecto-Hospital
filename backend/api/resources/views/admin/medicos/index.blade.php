@extends('admin.layout')

@section('title', 'Médicos')

@section('content')
  <div class="row" style="justify-content: space-between; margin-bottom: 12px;">
    <div>
      <div class="muted">Gestiona médicos y su especialidad.</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.medicos.create') }}">Nuevo</a>
  </div>

  <div class="card" style="margin-bottom: 12px;">
    <form method="GET" class="row">
      <div class="field" style="min-width: 260px;">
        <label>Filtrar por especialidad</label>
        <select name="especialidad_id">
          <option value="">Todas</option>
          @foreach ($especialidades as $e)
            <option value="{{ $e->id }}" @selected(request('especialidad_id') == $e->id)>{{ $e->nombre }}</option>
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
          <th>DNI</th>
          <th>Nombre</th>
          <th>Especialidad</th>
          <th>Foto</th>
          <th style="width: 220px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($medicos as $m)
          <tr>
            <td>{{ $m->id }}</td>
            <td class="muted">{{ $m->dni ?? '—' }}</td>
            <td>{{ $m->nombre }}</td>
            <td>{{ $m->especialidad?->nombre ?? '—' }}</td>
            <td class="muted">{{ $m->foto ?? '—' }}</td>
            <td>
              <div class="row">
                <a class="btn" href="{{ route('admin.medicos.edit', $m) }}">Editar</a>
                <form method="POST" action="{{ route('admin.medicos.destroy', $m) }}" data-admin-delete>
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger" type="submit">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">Sin registros.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

