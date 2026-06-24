<?php

namespace Tests\Unit;

use App\Support\MapRoutePreview;
use PHPUnit\Framework\TestCase;

class MapRoutePreviewTest extends TestCase
{
    public function test_embed_url_uses_google_api_when_key_is_operational(): void
    {
        $url = MapRoutePreview::embedUrl(-12.0653, -75.2046, -12.0910, -75.2180, true, 'test-key');

        $this->assertStringContainsString('google.com/maps/embed/v1/directions', $url);
        $this->assertStringContainsString('key=test-key', $url);
    }

    public function test_embed_url_uses_google_legacy_when_key_is_not_operational(): void
    {
        $url = MapRoutePreview::embedUrl(-12.0653, -75.2046, -12.0910, -75.2180, false, 'bad-key');

        $this->assertStringContainsString('google.com/maps?f=d', $url);
        $this->assertStringContainsString('output=embed', $url);
        $this->assertStringContainsString('saddr=-12.0653%2C-75.2046', $url);
        $this->assertStringContainsString('daddr=-12.091%2C-75.218', $url);
    }
}
