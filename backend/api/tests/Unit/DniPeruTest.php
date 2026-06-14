<?php

namespace Tests\Unit;

use App\Support\DniPeru;
use Tests\TestCase;

class DniPeruTest extends TestCase
{
    public function test_db_lookup_incluye_variante_con_cero_inicial(): void
    {
        $this->assertSame(['1234567', '01234567'], DniPeru::dbLookupCandidates('1234567'));
        $this->assertSame(['40604050'], DniPeru::dbLookupCandidates('40604050'));
    }

    public function test_reniec_query_rechaza_muy_corto(): void
    {
        $this->assertNull(DniPeru::forReniecQuery('1'));
        $this->assertNull(DniPeru::forReniecQuery('123456'));
    }

    public function test_reniec_query_acepta_siete_y_ocho_digitos(): void
    {
        $this->assertSame('01234567', DniPeru::forReniecQuery('1234567'));
        $this->assertSame('40604050', DniPeru::forReniecQuery('40604050'));
    }
}
