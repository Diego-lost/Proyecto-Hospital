<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medico;

class MedicoController extends Controller
{
    public function index(Request $request)
    {
        $q = Medico::query()->with('especialidad');

        if ($request->filled('especialidad_id')) {
            $q->where('especialidad_id', $request->input('especialidad_id'));
        }

        return $q->orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'dni' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/', 'unique:medicos,dni'],
            'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'foto' => ['nullable', 'string', 'max:255'],
        ]);

        $medico = Medico::create($data);

        return response()->json($medico->load('especialidad'), 201);
    }

    public function show(Medico $medico)
    {
        return $medico->load('especialidad');
    }

    public function update(Request $request, Medico $medico)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'dni' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/', 'unique:medicos,dni,'.$medico->id],
            'especialidad_id' => ['sometimes', 'required', 'integer', 'exists:especialidades,id'],
            'foto' => ['nullable', 'string', 'max:255'],
        ]);

        $medico->update($data);

        return $medico->load('especialidad');
    }

    public function destroy(Medico $medico)
    {
        $medico->delete();
        return response()->noContent();
    }
}
