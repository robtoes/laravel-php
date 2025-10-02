<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ==================== LOGIN TESTS ====================

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'email_verified_at', 'created_at']
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login exitoso',
                'token_type' => 'Bearer',
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /** @test */
    public function it_fails_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ]);
    }

    /** @test */
    public function it_fails_login_with_non_existent_user()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'noexiste@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ]);
    }

    /** @test */
    public function it_validates_required_fields_on_login()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password'])
            ->assertJson([
                'success' => false,
                'message' => 'Error de validacion',
            ]);
    }

    /** @test */
    public function it_validates_email_format_on_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_min_length_on_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '12345', // menos de 6 caracteres
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_creates_sanctum_token_on_successful_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->assertCount(0, $user->tokens);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertCount(1, $user->fresh()->tokens);
    }

    // ==================== REGISTER TESTS ====================

    /** @test */
    public function it_can_register_a_new_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'lastName' => 'DoeLonger',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'token_type',
                'user' => ['id', 'name', 'lastName', 'email']
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John',
        ]);
    }

    /** @test */
    public function it_hashes_password_on_registration()
    {
        $this->postJson('/api/register', [
            'name' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function it_creates_token_on_successful_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertNotEmpty($response->json('token'));
        
        $user = User::where('email', 'john@example.com')->first();
        $this->assertCount(1, $user->tokens);
    }

    /** @test */
    public function it_validates_required_fields_on_registration()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'lastName', 'email', 'password']);
    }

    /** @test */
    public function it_validates_unique_email_on_registration()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'lastName' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_confirmation_on_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_validates_password_min_length_on_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ==================== LOGOUT TESTS ====================

    /** @test */
    public function it_can_logout_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout exitoso',
            ]);

        // Verificar que el token fue eliminado
        $this->assertCount(0, $user->fresh()->tokens);
    }

    /** @test */
    public function it_requires_authentication_to_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_only_deletes_current_token_on_logout()
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('token-1')->plainTextToken;
        $token2 = $user->createToken('token-2')->plainTextToken;

        $this->assertCount(2, $user->fresh()->tokens);

        $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/logout');

        // Debe quedar 1 token (el token2)
        $this->assertCount(1, $user->fresh()->tokens);
    }

    // ==================== LOGOUT ALL TESTS ====================

    /** @test */
    public function it_can_logout_from_all_devices()
    {
        $user = User::factory()->create();
        $user->createToken('token-1');
        $user->createToken('token-2');
        $user->createToken('token-3');

        $this->assertCount(3, $user->tokens);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada en todos los dispositivos',
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    /** @test */
    public function it_requires_authentication_to_logout_all()
    {
        $response = $this->postJson('/api/logout-all');

        $response->assertStatus(401);
    }

    // ==================== ME (GET USER INFO) TESTS ====================

    /** @test */
    public function it_returns_authenticated_user_info()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_to_get_user_info()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    // ==================== REFRESH TOKEN TESTS ====================

    /** @test */
    public function it_can_refresh_token()
    {
        $user = User::factory()->create();
        $oldToken = $user->createToken('old-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $oldToken")
            ->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'token_type',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Token refrescado exitosamente',
                'token_type' => 'Bearer',
            ]);

        $newToken = $response->json('token');
        $this->assertNotEquals($oldToken, $newToken);
        
        // Debe haber solo 1 token (el nuevo)
        $this->assertCount(1, $user->fresh()->tokens);
    }

    /** @test */
    public function it_requires_authentication_to_refresh_token()
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401);
    }

    /** @test */
    public function old_token_is_invalid_after_refresh()
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('old-token');
        $oldToken = $tokenResult->plainTextToken;
        $oldTokenId = $tokenResult->accessToken->id;

        // Hacer refresh
        $response = $this->withHeader('Authorization', "Bearer $oldToken")
            ->postJson('/api/refresh');

        $response->assertStatus(200);

        // Verificar que el token viejo fue eliminado de la base de datos
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $oldTokenId,
        ]);

        // Verificar que solo hay 1 token (el nuevo)
        $this->assertCount(1, $user->fresh()->tokens);
    }

    // ==================== CHANGE PASSWORD TESTS ====================

    /** @test */
    public function it_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente',
            ]);

        // Verificar que la nueva contraseña funciona
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function it_fails_change_password_with_wrong_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta',
            ]);

        // Verificar que la contraseña NO cambió
        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    /** @test */
    public function it_validates_required_fields_on_change_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'new_password']);
    }

    /** @test */
    public function it_validates_new_password_confirmation_on_change()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function it_validates_new_password_min_length()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => '12345',
            'new_password_confirmation' => '12345',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function it_revokes_other_tokens_after_password_change()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        // Crear múltiples tokens
        $currentToken = $user->createToken('current')->plainTextToken;
        $user->createToken('device-2');
        $user->createToken('device-3');

        $this->assertCount(3, $user->tokens);

        $this->withHeader('Authorization', "Bearer $currentToken")
            ->postJson('/api/change-password', [
                'current_password' => 'oldpassword',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        // Solo debe quedar el token actual
        $this->assertCount(1, $user->fresh()->tokens);
    }

    /** @test */
    public function it_requires_authentication_to_change_password()
    {
        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401);
    }

    // ==================== TOKENS LIST TESTS ====================

    /** @test */
    public function it_returns_list_of_user_tokens()
    {
        $user = User::factory()->create();
        $user->createToken('token-1');
        $user->createToken('token-2');
        $user->createToken('token-3');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tokens');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'tokens')
            ->assertJsonStructure([
                'success',
                'tokens' => [
                    '*' => ['id', 'name', 'abilities', 'last_used_at', 'created_at']
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_to_list_tokens()
    {
        $response = $this->getJson('/api/tokens');

        $response->assertStatus(401);
    }

    // ==================== REVOKE TOKEN TESTS ====================

    /** @test */
    public function it_can_revoke_specific_token()
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('token-1');
        $token2 = $user->createToken('token-2');

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tokens/{$token1->accessToken->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token revocado exitosamente',
            ]);

        $this->assertCount(1, $user->fresh()->tokens);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token1->accessToken->id,
        ]);
    }

    /** @test */
    public function it_returns_404_when_token_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/tokens/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Token no encontrado',
            ]);
    }

    /** @test */
    public function it_cannot_revoke_token_of_another_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $token = $user2->createToken('user2-token');

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/tokens/{$token->accessToken->id}");

        $response->assertStatus(404);
        
        // Verificar que el token de user2 NO fue eliminado
        $this->assertCount(1, $user2->fresh()->tokens);
    }

    /** @test */
    public function it_requires_authentication_to_revoke_token()
    {
        $response = $this->deleteJson('/api/tokens/1');

        $response->assertStatus(401);
    }
}