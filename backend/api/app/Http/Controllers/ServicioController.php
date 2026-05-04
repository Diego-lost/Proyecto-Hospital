<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $q = Servicio::query()->with('medico.especialidad');

        if ($request->filled('medico_id')) {
            $q->where('medico_id', $request->input('medico_id'));
        }

        return $q->orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
        ]);

        $servicio = Servicio::create($data);

        return response()->json($servicio->load('medico.especialidad'), 201);
    }

    public function show(Servicio $servicio)
    {
        return $servicio->load('medico.especialidad');
    }

    public function update(Request $request, Servicio $servicio)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'descripcion' => ['sometimes', 'required', 'string', 'max:2000'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:0'],
            'medico_id' => ['sometimes', 'required', 'integer', 'exists:medicos,id'],
        ]);

        $servicio->update($data);

        return $servicio->load('medico.especialidad');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();
        return response()->noContent();
    }
}
