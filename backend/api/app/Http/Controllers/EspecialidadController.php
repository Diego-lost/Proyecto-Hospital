<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Especialidad;

class EspecialidadController extends Controller
{
    public function index()
    {
        return Especialidad::query()
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        $especialidad = Especialidad::create($data);

        return response()->json($especialidad, 201);
    }

    public function show(Especialidad $especialidad)
    {
        return $especialidad;
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        $especialidad->update($data);

        return $especialidad;
    }

    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();

        return response()->noContent();
    }

}
