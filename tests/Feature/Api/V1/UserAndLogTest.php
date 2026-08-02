<?php

namespace Tests\Feature\Api\V1;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserAndLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_crm_users(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $this->getJson('/api/v1/users', $this->headers($editor))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/users', [
            'name' => 'Новий редактор',
            'email' => 'editor@example.com',
            'password' => 'secure-password',
            'role' => 'editor',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'editor@example.com');

        $this->assertDatabaseHas('system_logs', ['action' => 'create_user']);
    }

    public function test_activity_logs_are_admin_only_and_include_mobile_context(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);
        SystemLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'mobile_login',
            'description' => 'Вхід із застосунку',
            'source' => 'mobile_app',
            'device_name' => 'Test phone',
            'platform' => 'android',
        ]);

        $this->getJson('/api/v1/activity-logs', $this->headers($editor))->assertForbidden();
        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/activity-logs')
            ->assertOk()
            ->assertJsonPath('data.0.source', 'mobile_app')
            ->assertJsonPath('data.0.device_name', 'Test phone');
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('Test')->plainTextToken];
    }
}
