<?php

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\DummyController;
use Illuminate\Support\Facades\Route;



//Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Test básico de conexión
Route::get('/test', [DummyController::class, 'test']);

// Simular lista de usuarios
Route::get('/test/users', [DummyController::class, 'getUsers']);

// Test de POST (echo)
Route::post('/test/echo', [DummyController::class, 'echo']);

// Test de errores
Route::get('/test/error', [DummyController::class, 'testError']);

// Test de diferentes códigos HTTP
Route::get('/test/status/{code}', [DummyController::class, 'testStatus']);

// Test de delay (para probar loaders)
Route::get('/test/delay/{seconds?}', [DummyController::class, 'testDelay']);

// Autenticación
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    
    // Usuario autenticado
    Route::get('/me', [AuthController::class, 'me']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    
    // Gestión de tokens
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken']);
    
    // Contraseña
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Test protegido
    Route::get('/test/protected', [DummyController::class, 'testProtected']);
    
    // Obtener usuario
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/detail/{task}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/update/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
});

