<?php

namespace Tests\Feature\Api\V1;

use App\Models\AdminProAssignment;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportAndAdminProTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_support_chat_and_only_admin_pro_can_answer(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        $editor = User::factory()->create(['role' => 'editor']);
        $ordinaryAdmin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($editor);
        $conversationId = $this->postJson('/api/v1/support/conversations', [
            'subject' => 'Не працює збереження',
            'message' => 'Після натискання нічого не відбувається.',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($ordinaryAdmin);
        $this->getJson('/api/v1/support/conversations')->assertForbidden();
        $this->postJson("/api/v1/support/conversations/{$conversationId}/messages", [
            'message' => 'Стороння відповідь',
        ])->assertForbidden();

        Sanctum::actingAs($adminPro);
        $this->getJson('/api/v1/support/conversations')
            ->assertOk()
            ->assertJsonPath('data.0.subject', 'Не працює збереження');
        $this->postJson("/api/v1/support/conversations/{$conversationId}/messages", [
            'message' => 'Перевіряємо проблему.',
        ])->assertCreated()->assertJsonPath('data.sender_type', 'admin_pro');

        $this->assertDatabaseHas('support_conversations', [
            'id' => $conversationId,
            'status' => 'waiting_user',
        ]);
        $this->assertDatabaseHas('system_logs', ['action' => 'create_support_message']);
    }

    public function test_user_cannot_open_another_users_conversation(): void
    {
        $owner = User::factory()->create(['role' => 'content']);
        $other = User::factory()->create(['role' => 'editor']);
        $conversation = SupportConversation::query()->create([
            'user_id' => $owner->id,
            'requester_name' => $owner->name,
            'requester_email' => $owner->email,
            'subject' => 'Особисте звернення',
            'status' => 'open',
        ]);

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/support/conversations/'.$conversation->id)->assertForbidden();
    }

    public function test_admin_pro_is_hidden_from_user_management_and_cannot_be_changed_there(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonMissing(['email' => $adminPro->email]);
        $this->patchJson('/api/v1/users/'.$adminPro->id, [
            'name' => 'Спроба зміни',
            'email' => $adminPro->email,
            'role' => 'admin',
        ])->assertForbidden();
        $this->deleteJson('/api/v1/users/'.$adminPro->id)->assertForbidden();

        Sanctum::actingAs($adminPro);
        $this->getJson('/api/v1/me')->assertJsonPath('data.is_admin_pro', true);
    }

    public function test_only_current_admin_pro_can_transfer_single_assignment(): void
    {
        $password = 'secure-password';
        $adminPro = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);
        $target = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);

        Sanctum::actingAs($otherAdmin);
        $this->postJson('/api/v1/admin-pro/transfer', [
            'target_user_id' => $target->id,
            'current_password' => $password,
        ])->assertForbidden();

        Sanctum::actingAs($adminPro);
        $this->postJson('/api/v1/admin-pro/transfer', [
            'target_user_id' => $target->id,
            'current_password' => 'wrong-password',
        ])->assertUnprocessable();
        $this->postJson('/api/v1/admin-pro/transfer', [
            'target_user_id' => $target->id,
            'current_password' => $password,
        ])->assertOk();

        $this->assertSame($target->id, AdminProAssignment::currentUserId());
        $this->assertDatabaseCount('admin_pro_assignments', 1);
        $this->assertDatabaseHas('system_logs', ['action' => 'transfer_admin_pro']);
    }
}
