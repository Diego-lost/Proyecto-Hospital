<?php

namespace Tests\Unit;

use App\Services\Ai\TriageDolorService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TriageDolorServiceTest extends TestCase
{
    public function test_repair_text_encoding_fixes_mojibake(): void
    {
        $service = app(TriageDolorService::class);
        $method = new ReflectionMethod(TriageDolorService::class, 'repairTextEncoding');
        $method->setAccessible(true);

        $broken = 'inflamaci'.'├│'.'n com'.'├║'.'n del tal'.'├│'.'n';
        $fixed = $method->invoke($service, $broken);

        $this->assertStringContainsString('inflamación', $fixed);
        $this->assertStringContainsString('común', $fixed);
        $this->assertStringContainsString('talón', $fixed);
    }

    public function test_build_relief_recommendations_for_foot_pain(): void
    {
        $service = app(TriageDolorService::class);
        $method = new ReflectionMethod(TriageDolorService::class, 'buildReliefRecommendations');
        $method->setAccessible(true);

        $tips = $method->invoke($service, [
            'motivo' => 'me duele el pie',
            'ubicacion_dolor' => 'pie',
            'intensidad_dolor' => 5,
            'duracion_horas' => 24,
            'sintomas_asociados' => [],
            'comorbilidades' => [],
        ]);

        $this->assertGreaterThanOrEqual(4, count($tips));
        $joined = implode(' ', $tips);
        $this->assertStringContainsString('hielo', mb_strtolower($joined));
        $this->assertStringNotContainsString('solo reposo', mb_strtolower($joined));
    }
}
