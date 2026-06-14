@extends('admin.layout')

@section('title', 'Especialidades')
@section('page_subtitle', 'Crear, editar y eliminar especialidades.')

@section('content')
  <div class="page-toolbar">
    <div></div>
    <a class="btn btn-primary" href="{{ route('admin.especialidades.create') }}">+ Nueva</a>
  </div>

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Imagen</th>
          <th style="width: 220px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($especialidades as $e)
          <tr>
            <td>{{ $e->id }}</td>
            <td>{{ $e->nombre }}</td>
            <td class="muted">{{ $e->imagen ?? '—' }}</td>
            <td>
              <div class="row">
                <a class="btn" href="{{ route('admin.especialidades.edit', $e) }}">Editar</a>
                <form method="POST" action="{{ route('admin.especialidades.destroy', $e) }}" data-admin-delete>
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger" type="submit">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="muted">Sin registros.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

