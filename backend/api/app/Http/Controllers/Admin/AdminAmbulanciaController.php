<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulancia;
use App\Services\GeocodingService;
use App\Services\GoogleDirectionsService;
use App\Support\MapRoutePreview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAmbulanciaController extends Controller
{
    public function index(GoogleDirectionsService $directions): View
    {
        $origen = GoogleDirectionsService::origenClinica();
        $ambulancias = Ambulancia::query()
            ->orderByRaw("CASE estado WHEN 'disponible' THEN 0 WHEN 'en_ruta' THEN 1 ELSE 2 END")
            ->orderBy('codigo')
            ->get();

        $stats = [
            'disponibles' => $ambulancias->where('estado', Ambulancia::ESTADO_DISPONIBLE)->count(),
            'en_ruta' => $ambulancias->where('estado', Ambulancia::ESTADO_EN_RUTA)->count(),
            'mantenimiento' => $ambulancias->where('estado', Ambulancia::ESTADO_MANTENIMIENTO)->count(),
        ];

        $allowDispatchWithoutRoute = (bool) config('services.google_maps.dispatch_without_route', false);
        $mapsKey = (string) config('services.google_maps.key', '');
        $googleMapsOk = $directions->claveGoogleOperativa();
        $mapsDiagnostico = $googleMapsOk ? null : $directions->diagnosticoClave();
        $mapEmbedInicial = MapRoutePreview::embedUrl(
            (float) $origen['lat'],
            (float) $origen['lng'],
            -12.0910,
            -75.2180,
            $googleMapsOk,
            $mapsKey,
        );

        return view('admin.ambulancias.index', compact(
            'ambulancias',
            'stats',
            'origen',
            'allowDispatchWithoutRoute',
            'mapsKey',
            'googleMapsOk',
            'mapsDiagnostico',
            'mapEmbedInicial',
        ));
    }

    public function create(): View
    {
        return view('admin.ambulancias.form', ['ambulancia' => new Ambulancia]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Ambulancia::query()->create($data);

        return redirect()->route('admin.ambulancias.index')->with('status', 'Ambulancia registrada.');
    }

    public function edit(Ambulancia $ambulancia): View
    {
        return view('admin.ambulancias.form', compact('ambulancia'));
    }

    public function update(Request $request, Ambulancia $ambulancia): RedirectResponse
    {
        $data = $this->validated($request, $ambulancia);
        $ambulancia->update($data);

        return redirect()->route('admin.ambulancias.index')->with('status', 'Ambulancia actualizada.');
    }

    public function destroy(Ambulancia $ambulancia): RedirectResponse
    {
        if ($ambulancia->estaEnRuta()) {
            return back()->withErrors(['codigo' => 'No puedes eliminar una ambulancia que está en ruta. Márcala como regresada primero.']);
        }

        $ambulancia->delete();

        return redirect()->route('admin.ambulancias.index')->with('status', 'Ambulancia eliminada.');
    }

    public function previewRuta(Request $request, GoogleDirectionsService $directions, GeocodingService $geocoding): JsonResponse
    {
        $data = $request->validate([
            'destino_lat' => ['required', 'numeric', 'between:-90,90'],
            'destino_lng' => ['required', 'numeric', 'between:-180,180'],
            'destino_direccion' => ['nullable', 'string', 'max:500'],
        ]);

        $destinoLat = (float) $data['destino_lat'];
        $destinoLng = (float) $data['destino_lng'];
        $destinoDireccion = trim((string) ($data['destino_direccion'] ?? ''));

        if ($destinoDireccion !== '') {
            $geo = $geocoding->buscar($destinoDireccion);
            if ($geo) {
                $destinoLat = $geo['lat'];
                $destinoLng = $geo['lng'];
                $destinoDireccion = $geo['display_name'];
            }
        }

        $origen = GoogleDirectionsService::origenClinica();
        $ruta = $directions->consultarRuta(
            $origen['lat'],
            $origen['lng'],
            $destinoLat,
            $destinoLng,
        );

        if (! $ruta['ok']) {
            return response()->json([
                'ok' => false,
                'mensaje' => $ruta['mensaje'] ?? 'No se pudo calcular la ruta.',
            ], 422);
        }

        $mapsKey = (string) config('services.google_maps.key', '');
        $googleMapsOk = $directions->claveGoogleOperativa();

        return response()->json([
            'ok' => true,
            'destino_lat' => $destinoLat,
            'destino_lng' => $destinoLng,
            'destino_direccion' => $destinoDireccion !== '' ? $destinoDireccion : ($ruta['destino_direccion'] ?? null),
            'origen_direccion' => $ruta['origen_direccion'] ?? $origen['direccion'].', '.$origen['ciudad'],
            'distancia_metros' => $ruta['distancia_metros'],
            'duracion_segundos' => $ruta['duracion_segundos'],
            'resumen' => $ruta['resumen'],
            'proveedor' => $ruta['proveedor'] ?? 'google',
            'route_geometry' => $ruta['geometria'] ?? null,
            'maps_url' => $this->mapsUrl(
                $origen['lat'],
                $origen['lng'],
                $destinoLat,
                $destinoLng,
            ),
            'map_embed_url' => MapRoutePreview::embedUrl(
                $origen['lat'],
                $origen['lng'],
                $destinoLat,
                $destinoLng,
                $googleMapsOk,
                $mapsKey,
            ),
        ]);
    }

    public function despachar(Request $request, Ambulancia $ambulancia, GoogleDirectionsService $directions, GeocodingService $geocoding): RedirectResponse
    {
        if (! $ambulancia->estaDisponible()) {
            return back()->withErrors(['codigo' => 'La ambulancia '.$ambulancia->codigo.' no está disponible.']);
        }

        $data = $request->validate([
            'destino_lat' => ['required', 'numeric', 'between:-90,90'],
            'destino_lng' => ['required', 'numeric', 'between:-180,180'],
            'destino_direccion' => ['nullable', 'string', 'max:500'],
            'conductor' => ['nullable', 'string', 'max:120'],
        ]);

        $destinoLat = (float) $data['destino_lat'];
        $destinoLng = (float) $data['destino_lng'];
        $destinoDireccion = trim((string) ($data['destino_direccion'] ?? ''));

        if ($destinoDireccion !== '') {
            $geo = $geocoding->buscar($destinoDireccion);
            if ($geo) {
                $destinoLat = $geo['lat'];
                $destinoLng = $geo['lng'];
                $destinoDireccion = $geo['display_name'];
            }
        }

        $origen = GoogleDirectionsService::origenClinica();
        $ruta = $directions->consultarRuta(
            $origen['lat'],
            $origen['lng'],
            $destinoLat,
            $destinoLng,
        );

        $sinRutaGoogle = ! $ruta['ok'];

        if ($sinRutaGoogle && ! config('services.google_maps.dispatch_without_route', false)) {
            return back()->withErrors([
                'destino_lat' => $ruta['mensaje'] ?? 'No se pudo calcular la ruta con Google Maps.',
            ])->withInput();
        }

        $ambulancia->update([
            'estado' => Ambulancia::ESTADO_EN_RUTA,
            'conductor' => $data['conductor'] ?? $ambulancia->conductor,
            'origen_lat' => $origen['lat'],
            'origen_lng' => $origen['lng'],
            'destino_lat' => $destinoLat,
            'destino_lng' => $destinoLng,
            'destino_direccion' => $destinoDireccion !== '' ? $destinoDireccion : ($ruta['destino_direccion'] ?? null),
            'distancia_metros' => $sinRutaGoogle ? null : $ruta['distancia_metros'],
            'duracion_segundos' => $sinRutaGoogle ? null : $ruta['duracion_segundos'],
            'ruta_resumen' => $sinRutaGoogle ? 'Despacho manual (sin ruta Google Maps)' : $ruta['resumen'],
            'despachada_at' => now(),
            'regreso_at' => null,
        ]);

        $mensaje = 'Ambulancia '.$ambulancia->codigo.' despachada hacia '.($ambulancia->destino_direccion ?? 'destino indicado').'.';
        if ($sinRutaGoogle) {
            $mensaje .= ' (Sin distancia/tiempo: revisa GOOGLE_MAPS_API_KEY y la Directions API en Google Cloud.)';
        }

        return redirect()->route('admin.ambulancias.index')->with('status', $mensaje);
    }

    public function regresar(Ambulancia $ambulancia): RedirectResponse
    {
        if (! $ambulancia->estaEnRuta()) {
            return back()->withErrors(['codigo' => 'Solo puedes marcar regreso de ambulancias en ruta.']);
        }

        $codigo = $ambulancia->codigo;
        $ambulancia->update([
            'estado' => Ambulancia::ESTADO_DISPONIBLE,
            'destino_lat' => null,
            'destino_lng' => null,
            'destino_direccion' => null,
            'distancia_metros' => null,
            'duracion_segundos' => null,
            'ruta_resumen' => null,
            'regreso_at' => now(),
        ]);

        return redirect()->route('admin.ambulancias.index')->with('status', 'Ambulancia '.$codigo.' marcada como disponible.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Ambulancia $ambulancia = null): array
    {
        $estados = [
            Ambulancia::ESTADO_DISPONIBLE,
            Ambulancia::ESTADO_EN_RUTA,
            Ambulancia::ESTADO_MANTENIMIENTO,
        ];

        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                Rule::unique('ambulancias', 'codigo')->ignore($ambulancia?->id),
            ],
            'placa' => ['nullable', 'string', 'max:20'],
            'conductor' => ['nullable', 'string', 'max:120'],
            'estado' => ['required', Rule::in($estados)],
        ]);

        if (($data['estado'] ?? '') === Ambulancia::ESTADO_EN_RUTA && $ambulancia !== null && ! $ambulancia->estaEnRuta()) {
            throw ValidationException::withMessages([
                'estado' => 'Para poner en ruta usa el formulario de despacho en la lista de ambulancias.',
            ]);
        }

        if ($ambulancia?->estaEnRuta() && ($data['estado'] ?? '') !== Ambulancia::ESTADO_EN_RUTA) {
            $data['destino_lat'] = null;
            $data['destino_lng'] = null;
            $data['destino_direccion'] = null;
            $data['distancia_metros'] = null;
            $data['duracion_segundos'] = null;
            $data['ruta_resumen'] = null;
            $data['regreso_at'] = now();
        }

        return $data;
    }

    private function mapsUrl(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): string
    {
        return 'https://www.google.com/maps/dir/?api=1&origin='
            .urlencode($origenLat.','.$origenLng)
            .'&destination='
            .urlencode($destinoLat.','.$destinoLng)
            .'&travelmode=driving';
    }
}
