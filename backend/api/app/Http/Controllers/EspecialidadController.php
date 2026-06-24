<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Especialidad;

class EspecialidadController extends Controller
{
    public function index()
    {
        $rows = Especialidad::query()
            ->orderBy('nombre')
            ->get();

        return $this->dedupeByNombre($rows)->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Especialidad>  $rows
     * @return \Illuminate\Support\Collection<int, Especialidad>
     */
    private function dedupeByNombre($rows)
    {
        $seen = [];

        return $rows
            ->sortBy('id')
            ->filter(function (Especialidad $row) use (&$seen) {
                $key = mb_strtolower(trim($row->nombre));
                if (isset($seen[$key])) {
                    return false;
                }
                $seen[$key] = true;

                return true;
            })
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ]);

        $nombre = trim($data['nombre']);
        $existing = Especialidad::query()
            ->whereRaw('lower(trim(nombre)) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($existing) {
            return response()->json($existing);
        }

        $especialidad = Especialidad::create([
            ...$data,
            'nombre' => $nombre,
        ]);

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
