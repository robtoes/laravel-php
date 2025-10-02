<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_returns_all_tasks_for_authenticated_user()
    {
        // Crear tareas para el usuario autenticado
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        // Crear tareas de otro usuario (no deben aparecer)
        $otherUser = User::factory()->create();
        Task::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_returns_empty_array_when_user_has_no_tasks()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /** @test */
    public function it_requires_authentication_to_list_tasks()
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_shows_a_specific_task_for_authenticated_user()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Mi Tarea',
            'description' => 'Descripción de prueba',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/tasks/detail/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $task->id,
                'title' => 'Mi Tarea',
                'description' => 'Descripción de prueba',
                'user_id' => $this->user->id,
            ]);
    }

    /** @test */
    public function it_returns_403_when_showing_task_of_another_user()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/tasks/detail/{$task->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'No autorizado']);
    }

    /** @test */
    public function it_requires_authentication_to_show_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/detail/{$task->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_a_new_task()
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción de la nueva tarea',
            'due_date' => '2025-12-31',
            'completed' => false,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Nueva Tarea',
                'description' => 'Descripción de la nueva tarea',
                'user_id' => $this->user->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Nueva Tarea',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_task()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_validates_title_max_length_when_creating()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => str_repeat('a', 256), // 256 caracteres (excede el máximo)
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_validates_due_date_format_when_creating()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Tarea',
                'due_date' => 'fecha-invalida',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function it_accepts_valid_date_formats_when_creating()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Tarea con fecha',
                'due_date' => '2025-12-31',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Tarea con fecha',
            'due_date' => '2025-12-31',
        ]);
    }

    /** @test */
    public function it_allows_nullable_description_when_creating()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Tarea sin descripción',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Tarea sin descripción',
            'description' => null,
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_create_task()
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_update_own_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Título Original',
            'completed' => false,
        ]);

        $updateData = [
            'title' => 'Título Actualizado',
            'description' => 'Descripción actualizada',
            'completed' => true,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Título Actualizado',
                'description' => 'Descripción actualizada',
                'completed' => true,
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Actualizado',
        ]);
    }

    /** @test */
    public function it_returns_403_when_updating_task_of_another_user()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'title' => 'Intento de actualización',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'No autorizado']);

        // Verificar que la tarea NO fue actualizada
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
            'title' => 'Intento de actualización',
        ]);
    }

    /** @test */
    public function it_validates_fields_when_updating_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'title' => '', // título vacío
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_validates_title_max_length_when_updating()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'title' => str_repeat('a', 256), // Excede máximo
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_validates_due_date_format_when_updating()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'due_date' => 'no-es-fecha',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function it_validates_completed_must_be_boolean_when_updating()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'completed' => 'no-es-booleano',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed']);
    }

    /** @test */
    public function it_allows_partial_update_with_sometimes_rule()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Título Original',
            'description' => 'Descripción Original',
        ]);

        // Solo actualizar completed, sin enviar title
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'completed' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Original', // Se mantiene
            'completed' => true, // Se actualizó
        ]);
    }

    /** @test */
    public function it_allows_nullable_fields_when_updating()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'Descripción original',
            'due_date' => '2025-12-31',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'description' => null,
                'due_date' => null,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => null,
            'due_date' => null,
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_update_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson("/api/tasks/update/{$task->id}", [
            'title' => 'Actualizado',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_delete_own_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function it_returns_403_when_deleting_task_of_another_user()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403)
            ->assertJson(['error' => 'No autorizado']);

        // Verificar que la tarea NO fue eliminada
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_delete_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_404_when_task_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks/detail/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_only_completed_status()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Tarea Original',
            'completed' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/update/{$task->id}", [
                'title' => 'Tarea Original',
                'completed' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Tarea Original',
            'completed' => true,
        ]);
    }

    /** @test */
    public function it_returns_tasks_with_correct_structure()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Tarea de prueba',
            'description' => 'Descripción',
            'due_date' => '2025-12-31',
            'completed' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'user_id',
                    'completed',
                ]
            ]);
    }
}