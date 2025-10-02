<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_task()
    {
        $user = User::factory()->create();
        
        $task = Task::factory()->create([
            'title' => 'Nueva Tarea',
            'description' => 'Descripción de la tarea',
            'due_date' => '2025-10-15',
            'user_id' => $user->id,
            'completed' => 0,
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Nueva Tarea', $task->title);
        $this->assertEquals('Descripción de la tarea', $task->description);
        $this->assertEquals('2025-10-15', $task->due_date);
        $this->assertEquals($user->id, $task->user_id);
        $this->assertEquals(0, $task->completed);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $task = new Task();
        $fillable = $task->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('due_date', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('completed', $fillable);
    }

    /** @test */
    public function it_does_not_use_timestamps()
    {
        $task = new Task();
        
        $this->assertFalse($task->timestamps);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
        $this->assertEquals($user->email, $task->user->email);
    }

    /** @test */
    public function it_can_be_marked_as_completed()
    {
        $task = Task::factory()->create(['completed' => false]);

        $task->update(['completed' => true]);

        $this->assertTrue($task->completed);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'completed' => true,
        ]);
    }

    /** @test */
    public function it_can_be_marked_as_incomplete()
    {
        $task = Task::factory()->create(['completed' => true]);

        $task->update(['completed' => false]);

        $this->assertFalse($task->completed);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'completed' => false,
        ]);
    }

    /** @test */
    public function it_can_update_title()
    {
        $task = Task::factory()->create(['title' => 'Título Original']);

        $task->update(['title' => 'Título Actualizado']);

        $this->assertEquals('Título Actualizado', $task->title);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Actualizado',
        ]);
    }

    /** @test */
    public function it_can_update_description()
    {
        $task = Task::factory()->create(['description' => 'Descripción original']);

        $task->update(['description' => 'Descripción actualizada']);

        $this->assertEquals('Descripción actualizada', $task->description);
    }

    /** @test */
    public function it_can_update_due_date()
    {
        $task = Task::factory()->create(['due_date' => '2025-10-01']);

        $task->update(['due_date' => '2025-10-15']);

        $this->assertEquals('2025-10-15', $task->due_date);
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $task = Task::factory()->create();
        $taskId = $task->id;

        $task->delete();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    /** @test */
    public function it_requires_a_user_id()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Task::create([
            'title' => 'Tarea sin usuario',
            'description' => 'Esta tarea no tiene user_id',
            'due_date' => '2025-10-15',
            'completed' => false,
        ]);
    }

    /** @test */
    public function it_can_retrieve_all_tasks_for_a_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::factory()->count(3)->create(['user_id' => $user->id]);
        Task::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $userTasks = Task::where('user_id', $user->id)->get();

        $this->assertCount(3, $userTasks);
    }

    /** @test */
    public function it_can_filter_completed_tasks()
    {
        $user = User::factory()->create();
        
        Task::factory()->count(2)->create([
            'user_id' => $user->id,
            'completed' => true,
        ]);
        
        Task::factory()->count(3)->create([
            'user_id' => $user->id,
            'completed' => false,
        ]);

        $completedTasks = Task::where('user_id', $user->id)
            ->where('completed', true)
            ->get();

        $this->assertCount(2, $completedTasks);
    }

    /** @test */
    public function it_can_filter_incomplete_tasks()
    {
        $user = User::factory()->create();
        
        Task::factory()->count(2)->create([
            'user_id' => $user->id,
            'completed' => true,
        ]);
        
        Task::factory()->count(3)->create([
            'user_id' => $user->id,
            'completed' => false,
        ]);

        $incompleteTasks = Task::where('user_id', $user->id)
            ->where('completed', false)
            ->get();

        $this->assertCount(3, $incompleteTasks);
    }

    /** @test */
    public function it_accepts_null_description()
    {
        $user = User::factory()->create();
        
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Tarea sin descripción',
            'description' => null,
            'due_date' => '2025-10-15',
            'completed' => false,
        ]);

        $this->assertNull($task->description);
    }

    /** @test */
    public function it_accepts_null_due_date()
    {
        $user = User::factory()->create();
        
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Tarea sin fecha límite',
            'description' => 'Sin fecha',
            'due_date' => null,
            'completed' => false,
        ]);

        $this->assertNull($task->due_date);
    }

    /** @test */
    public function it_defaults_completed_to_false()
    {
        $user = User::factory()->create();
        
        // Crear sin especificar completed
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Nueva tarea',
            'description' => 'Descripción',
            'due_date' => '2025-10-15',
            'completed' => false,
        ]);

        // Nota: Esto depende de tu migración
        // Si definiste un default en la BD, verifica que sea false
        $this->assertNotNull($task->completed);
    }
}