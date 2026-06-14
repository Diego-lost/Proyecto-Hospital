<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMedicoController extends Controller
{
    public function index(Request $request)
    {
        $especialidades = Especialidad::query()->orderBy('nombre')->get();

        $q = Medico::query()->with('especialidad')->orderBy('nombre');
        if ($request->filled('especialidad_id')) {
            $q->where('especialidad_id', $request->input('especialidad_id'));
        }
        $medicos = $q->get();

        return view('admin.medicos.index', compact('medicos', 'especialidades'));
    }

    public function create()
    {
        $especialidades = Especialidad::query()->orderBy('nombre')->get();

        return view('admin.medicos.form', ['medico' => new Medico, 'especialidades' => $especialidades]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'dni' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/', Rule::unique('medicos', 'dni')],
            'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'foto' => ['nullable', 'string', 'max:255'],
            'foto_actual' => ['nullable', 'string', 'max:255'],
            'foto_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('foto_file')) {
            $disk = (string) config('filesystems.default', 'local');
            if ($disk === 'local') {
                $disk = 'public';
            }
            $data['foto'] = $request->file('foto_file')->store('medicos', $disk);
        } else {
            $data['foto'] = $data['foto_actual'] ?? $data['foto'] ?? null;
        }

        Medico::create($data);

        return redirect()->route('admin.medicos.index');
    }

    public function edit(Medico $medico)
    {
        $especialidades = Especialidad::query()->orderBy('nombre')->get();

        return view('admin.medicos.form', compact('medico', 'especialidades'));
    }

    public function update(Request $request, Medico $medico)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'dni' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/', Rule::unique('medicos', 'dni')->ignore($medico->id)],
            'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'foto' => ['nullable', 'string', 'max:255'],
            'foto_actual' => ['nullable', 'string', 'max:255'],
            'foto_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('foto_file')) {
            $disk = (string) config('filesystems.default', 'local');
            if ($disk === 'local') {
                $disk = 'public';
            }
            $data['foto'] = $request->file('foto_file')->store('medicos', $disk);
        } else {
            $data['foto'] = $data['foto_actual'] ?? $data['foto'] ?? null;
        }

        $medico->update($data);

        return redirect()->route('admin.medicos.index');
    }

    public function destroy(Medico $medico)
    {
        $medico->delete();

        return redirect()->route('admin.medicos.index');
    }
}
