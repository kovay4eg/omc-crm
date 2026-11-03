<?php

namespace Tests\Feature;

use App\Filament\Widgets\HolidayAppearanceControl;
use App\Filament\Widgets\MaintenanceModeControl;
use App\Models\AdminProAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_previewing_editor_loses_admin_and_admin_pro_permissions(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);

        $this->actingAs($adminPro);
        session(['preview_role' => 'editor']);

        $this->assertFalse($adminPro->isAdmin());
        $this->assertFalse($adminPro->isAdminPro());
        $this->assertTrue($adminPro->isEditor());
        $this->assertFalse(HolidayAppearanceControl::canView());
        $this->assertFalse(MaintenanceModeControl::canView());
    }

    public function test_admin_previewing_content_loses_admin_and_editor_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        session(['preview_role' => 'content']);

        $this->assertFalse($admin->isAdmin());
        $this->assertFalse($admin->isEditor());
        $this->assertTrue($admin->isContent());
    }

    public function test_preview_does_not_change_the_stored_role_or_panel_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        session(['preview_role' => 'content']);

        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));
    }
}
