<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPagoController extends Controller
{
    public function index(): View
    {
        $pagos = Pago::query()
            ->with('servicio:id,nombre')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('admin.pagos.index', compact('pagos'));
    }

    public function confirmar(Request $request, Pago $pago): RedirectResponse
    {
        if ($pago->estado === Pago::ESTADO_PAID) {
            return back()->with('status', 'El pago ya estaba confirmado.');
        }

        $data = $request->validate([
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        $pago->marcarPagado($data['notas'] ?? 'Confirmado manualmente desde el panel.');

        return back()->with('status', 'Pago #'.$pago->id.' marcado como pagado.');
    }
}
