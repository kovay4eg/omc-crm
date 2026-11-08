<?php

namespace Tests\Unit;

use App\Services\DiskSpaceMonitor;
use RuntimeException;
use Tests\TestCase;

class DiskSpaceMonitorTest extends TestCase
{
    public function test_it_uses_trigger_and_recovery_hysteresis(): void
    {
        config()->set('media_archive.thresholds', [
            'trigger_percent' => 10,
            'recovery_percent' => 15,
            'trigger_bytes' => 0,
            'recovery_bytes' => 0,
        ]);

        $monitor = new DiskSpaceMonitor;

        $this->assertTrue($monitor->assess(1_000, 100)['low']);
        $this->assertFalse($monitor->assess(1_000, 100)['recovered']);
        $this->assertFalse($monitor->assess(1_000, 120)['low']);
        $this->assertFalse($monitor->assess(1_000, 120)['recovered']);
        $this->assertTrue($monitor->assess(1_000, 150)['recovered']);
    }

    public function test_it_rejects_invalid_disk_values(): void
    {
        $this->expectException(RuntimeException::class);

        (new DiskSpaceMonitor)->assess(0, 0);
    }
}
