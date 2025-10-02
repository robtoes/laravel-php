<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;


class TaskController extends Controller
{
    // Listar todas las tareas del usuario autenticado
    public function index()
    {
        $user = Auth::user();
        return response()->json($user->tasks, 200);
    }

    // Mostrar una tarea específica (sólo si pertenece al usuario)
    public function show(Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        return response()->json($task, 200);
    }

    // Crear una nueva tarea
    public function store(TaskStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $task = $user->tasks()->create($validated);
        return response()->json($task, 201);
    }

    // Actualizar una tarea existente
    public function update(TaskUpdateRequest $request, Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            return response()->json(['error' => 'No autorizado', 'user_id_task' => $task , 'user_id' => $user->id], 403);
        }

        $validated = $request->validated();
        $task->update($validated);
        return response()->json($task, 200);
    }

    // Eliminar una tarea
    public function destroy(Task $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $task->delete();
        return response()->json(null, 204);
    }
}