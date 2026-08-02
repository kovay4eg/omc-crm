<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_user_can_log_in_and_receive_token(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret-password',
            'device_name' => 'Test phone',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $token = $user->tokens()->firstOrFail();
        $this->assertSame(['crm:access'], $token->abilities);
        $this->assertNotNull($token->expires_at);
        $this->assertTrue($token->expires_at->isFuture());
        $response
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['role' => 'editor']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'Test phone',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('system_logs', [
            'user_id' => $user->id,
            'action' => 'mobile_login_failed',
            'source' => 'mobile_app',
        ]);
    }

    public function test_login_keeps_at_most_five_mobile_sessions(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
            'role' => 'admin',
        ]);

        foreach (range(1, 5) as $index) {
            $user->createToken('Old phone '.$index);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret-password',
            'device_name' => 'New phone',
        ])->assertOk();

        $this->assertCount(5, $user->fresh()->tokens);
        $this->assertTrue($user->fresh()->tokens->contains('name', 'New phone'));
    }

    public function test_token_without_crm_ability_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Limited token', [])->plainTextToken;

        $this->getJson('/api/v1/me', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Ця сесія не має доступу до мобільної CRM.');
    }

    public function test_enabled_two_factor_requires_and_accepts_totp_code(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
        $user->createTwoFactorAuth();
        $user->enableTwoFactorAuth();

        $payload = [
            'email' => $user->email,
            'password' => 'secret-password',
            'device_name' => 'Secure phone',
        ];

        $this->postJson('/api/v1/auth/login', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('two_factor_required', true);

        $this->postJson('/api/v1/auth/login', [
            ...$payload,
            'two_factor_code' => $user->makeTwoFactorCode(),
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_user_can_prepare_and_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Test phone')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->postJson('/api/v1/auth/two-factor/prepare', [], $headers)
            ->assertOk()
            ->assertJsonStructure(['data' => ['secret', 'uri']]);

        $user->refresh();
        $this->postJson('/api/v1/auth/two-factor/confirm', [
            'code' => $user->makeTwoFactorCode(),
        ], $headers)
            ->assertOk()
            ->assertJsonCount(10, 'data.recovery_codes');

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_without_crm_role_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
            'role' => 'user',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret-password',
            'device_name' => 'Test phone',
        ])->assertForbidden();
    }

    public function test_authenticated_user_can_get_profile_and_log_out(): void
    {
        $user = User::factory()->create(['role' => 'content']);
        $token = $user->createToken('Test phone')->plainTextToken;

        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/me', $headers)
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);

        $this->postJson('/api/v1/auth/logout', [], $headers)
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_can_list_only_their_mobile_sessions(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $otherUser = User::factory()->create(['role' => 'admin']);

        $currentToken = $user->createToken('Current phone');
        $user->createToken('Tablet');
        $otherUser->createToken('Other user phone');

        $this->getJson('/api/v1/auth/sessions', [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'device_name' => 'Current phone',
                'is_current' => true,
            ])
            ->assertJsonFragment([
                'device_name' => 'Tablet',
                'is_current' => false,
            ])
            ->assertJsonMissing([
                'device_name' => 'Other user phone',
            ]);
    }

    public function test_user_can_end_one_of_their_mobile_sessions(): void
    {
        $user = User::factory()->create(['role' => 'editor']);
        $currentToken = $user->createToken('Current phone');
        $otherToken = $user->createToken('Old phone');

        $this->deleteJson('/api/v1/auth/sessions/'.$otherToken->accessToken->getKey(), [], [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ])->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentToken->accessToken->getKey(),
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->getKey(),
        ]);
    }

    public function test_user_cannot_end_another_users_session(): void
    {
        $user = User::factory()->create(['role' => 'content']);
        $otherUser = User::factory()->create(['role' => 'content']);
        $currentToken = $user->createToken('Current phone');
        $otherToken = $otherUser->createToken('Other user phone');

        $this->deleteJson('/api/v1/auth/sessions/'.$otherToken->accessToken->getKey(), [], [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ])->assertNotFound();

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->getKey(),
        ]);
    }

    public function test_user_can_end_all_other_mobile_sessions(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $currentToken = $user->createToken('Current phone');
        $user->createToken('Tablet');
        $user->createToken('Old phone');

        $this->deleteJson('/api/v1/auth/sessions/others', [], [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ])
            ->assertOk()
            ->assertJsonPath('data.deleted_count', 2);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentToken->accessToken->getKey(),
        ]);
    }
}
