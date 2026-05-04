<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\Servicio;
use Illuminate\Http\Request;

class AdminServicioController extends Controller
{
    public function index(Request $request)
    {
        $medicos = Medico::query()->with('especialidad')->orderBy('nombre')->get();

        $q = Servicio::query()->with('medico.especialidad')->orderBy('nombre');
        if ($request->filled('medico_id')) {
            $q->where('medico_id', $request->input('medico_id'));
        }
        $servicios = $q->get();

        return view('admin.servicios.index', compact('servicios', 'medicos'));
    }

    public function create()
    {
        $medicos = Medico::query()->with('especialidad')->orderBy('nombre')->get();
        return view('admin.servicios.form', ['servicio' => new Servicio(), 'medicos' => $medicos]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
        ]);

        Servicio::create($data);
        return redirect()->route('admin.servicios.index');
    }

    public function edit(Servicio $servicio)
    {
        $medicos = Medico::query()->with('especialidad')->orderBy('nombre')->get();
        return view('admin.servicios.form', compact('servicio', 'medicos'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
        ]);

        $servicio->update($data);
        return redirect()->route('admin.servicios.index');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();
        return redirect()->route('admin.servicios.index');
    }
}

