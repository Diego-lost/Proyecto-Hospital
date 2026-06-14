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
        return view('admin.especialidades.form', ['especialidad' => new Especialidad]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'imagen' => ['nullable', 'string', 'max:255'],
            'imagen_actual' => ['nullable', 'string', 'max:255'],
            'imagen_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('imagen_file')) {
            $disk = (string) config('filesystems.default', 'local');
            if ($disk === 'local') {
                $disk = 'public';
            }
            $data['imagen'] = $request->file('imagen_file')->store('especialidades', $disk);
        } else {
            $data['imagen'] = $data['imagen_actual'] ?? $data['imagen'] ?? null;
        }

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
            'imagen_actual' => ['nullable', 'string', 'max:255'],
            'imagen_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('imagen_file')) {
            $disk = (string) config('filesystems.default', 'local');
            if ($disk === 'local') {
                $disk = 'public';
            }
            $data['imagen'] = $request->file('imagen_file')->store('especialidades', $disk);
        } else {
            $data['imagen'] = $data['imagen_actual'] ?? $data['imagen'] ?? null;
        }

        $especialidad->update($data);

        return redirect()->route('admin.especialidades.index');
    }

    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();

        return redirect()->route('admin.especialidades.index');
    }
}
