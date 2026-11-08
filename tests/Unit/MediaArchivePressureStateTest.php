<?php

namespace Tests\Unit;

use App\Services\MediaArchivePressureState;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MediaArchivePressureStateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('media-archive:pressure-active');
    }

    public function test_pressure_remains_active_between_thresholds_until_recovery(): void
    {
        $state = app(MediaArchivePressureState::class);

        $this->assertTrue($state->reconcile(['low' => true, 'recovered' => false]));
        $this->assertTrue($state->reconcile(['low' => false, 'recovered' => false]));
        $this->assertTrue(app(MediaArchivePressureState::class)->active());
        $this->assertFalse($state->reconcile(['low' => false, 'recovered' => true]));
        $this->assertFalse(app(MediaArchivePressureState::class)->active());
    }
}
