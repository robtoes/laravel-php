<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user()
    {
        $user = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Juan Pérez', $user->name);
        $this->assertEquals('juan@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
        ]);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    /** @test */
    public function it_hashes_password_automatically()
    {
        $user = User::factory()->create([
            'password' => 'plainpassword',
        ]);

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    /** @test */
    public function it_casts_email_verified_at_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    /** @test */
    public function it_has_many_tasks()
    {
        $user = User::factory()->create();
        
        Task::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->tasks);
        $this->assertCount(3, $user->tasks);
        $this->assertInstanceOf(Task::class, $user->tasks->first());
    }

    /** @test */
    public function it_can_have_zero_tasks()
    {
        $user = User::factory()->create();

        $this->assertCount(0, $user->tasks);
        $this->assertTrue($user->tasks->isEmpty());
    }

    /** @test */
    public function it_uses_has_api_tokens_trait()
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'tokens'));
        $this->assertTrue(method_exists($user, 'createToken'));
    }

    /** @test */
    public function it_can_create_sanctum_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token);
        $this->assertInstanceOf(\Laravel\Sanctum\NewAccessToken::class, $token);
    }

    /** @test */
    public function it_uses_notifiable_trait()
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'notify'));
        $this->assertTrue(method_exists($user, 'notifications'));
    }

    /** @test */
    public function it_deletes_related_tasks_on_cascade()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $taskId = $task->id;
        $user->delete();

        // Verifica que el task fue eliminado por cascade
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
        
        // También verifica que el usuario fue eliminado
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function email_must_be_unique()
    {
        User::factory()->create(['email' => 'unique@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'unique@example.com']);
    }

    /** @test */
    public function it_can_retrieve_user_by_email()
    {
        $user = User::factory()->create(['email' => 'find@example.com']);

        $foundUser = User::where('email', 'find@example.com')->first();

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
}