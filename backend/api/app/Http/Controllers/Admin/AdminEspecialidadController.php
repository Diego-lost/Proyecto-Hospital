<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;

class AdminEspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::query()->orderBy('nombre')->get();
        return view('admin.especialidades.index', compact('especialidades'));
    }

    public function create()
    {
        return view('admin.especialidades.form', ['especialidad' => new Especialidad()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        Especialidad::create($data);

        return redirect()->route('admin.especialidades.index');
    }

    public function edit(Especialidad $especialidad)
    {
        return view('admin.especialidades.form', compact('especialidad'));
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        $especialidad->update($data);

        return redirect()->route('admin.especialidades.index');
    }

    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();
        return redirect()->route('admin.especialidades.index');
    }
}

