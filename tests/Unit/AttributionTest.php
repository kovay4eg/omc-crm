<?php

namespace Tests\Unit;

use Tests\TestCase;

class AttributionTest extends TestCase
{
    public function test_project_authorship_fingerprint_is_preserved(): void
    {
        $this->assertSame('Roman Koshovyi', config('attribution.developer'));
        $this->assertSame('Developed by Roman Koshovyi', config('attribution.signature'));
        $this->assertSame('OMC-RK-2026-PLT-7F3A', config('attribution.fingerprint'));
    }
}
