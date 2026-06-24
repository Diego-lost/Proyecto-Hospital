<?php

namespace App\Support;

final class MapRoutePreview
{
    public static function embedUrl(
        float $origenLat,
        float $origenLng,
        float $destinoLat,
        float $destinoLng,
        bool $googleOk,
        ?string $googleKey,
    ): string {
        if ($googleOk && is_string($googleKey) && $googleKey !== '') {
            return self::googleApiDirectionsEmbed($origenLat, $origenLng, $destinoLat, $destinoLng, $googleKey);
        }

        return self::googleLegacyDirectionsEmbed($origenLat, $origenLng, $destinoLat, $destinoLng);
    }

    public static function googleApiDirectionsEmbed(
        float $origenLat,
        float $origenLng,
        float $destinoLat,
        float $destinoLng,
        string $googleKey,
    ): string {
        return 'https://www.google.com/maps/embed/v1/directions?key='.urlencode($googleKey)
            .'&origin='.urlencode($origenLat.','.$origenLng)
            .'&destination='.urlencode($destinoLat.','.$destinoLng)
            .'&mode=driving';
    }

    /** Google Maps embebido sin API key (misma interfaz que maps.google.com). */
    public static function googleLegacyDirectionsEmbed(
        float $origenLat,
        float $origenLng,
        float $destinoLat,
        float $destinoLng,
    ): string {
        return 'https://www.google.com/maps?f=d&hl=es'
            .'&saddr='.rawurlencode($origenLat.','.$origenLng)
            .'&daddr='.rawurlencode($destinoLat.','.$destinoLng)
            .'&output=embed';
    }

    public static function openStreetMapEmbed(
        float $origenLat,
        float $origenLng,
        float $destinoLat,
        float $destinoLng,
    ): string {
        $padding = 0.35;
        $minLat = min($origenLat, $destinoLat) - $padding;
        $maxLat = max($origenLat, $destinoLat) + $padding;
        $minLng = min($origenLng, $destinoLng) - $padding;
        $maxLng = max($origenLng, $destinoLng) + $padding;

        return sprintf(
            'https://www.openstreetmap.org/export/embed.html?bbox=%s,%s,%s,%s&layer=mapnik&marker=%s,%s&marker=%s,%s',
            rawurlencode((string) $minLng),
            rawurlencode((string) $minLat),
            rawurlencode((string) $maxLng),
            rawurlencode((string) $maxLat),
            rawurlencode((string) $origenLat),
            rawurlencode((string) $origenLng),
            rawurlencode((string) $destinoLat),
            rawurlencode((string) $destinoLng),
        );
    }

    public static function externalDirectionsUrl(
        float $origenLat,
        float $origenLng,
        float $destinoLat,
        float $destinoLng,
    ): string {
        return 'https://www.google.com/maps/dir/?api=1&origin='
            .urlencode($origenLat.','.$origenLng)
            .'&destination='
            .urlencode($destinoLat.','.$destinoLng)
            .'&travelmode=driving';
    }
}
