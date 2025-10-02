<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DummyController extends Controller
{
    /**
     * Endpoint público para verificar conexión básica
     * GET /api/test
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => '¡Conexión exitosa con Laravel!',
            'data' => [
                'server_time' => now()->format('Y-m-d H:i:s'),
                'laravel_version' => app()->version(),
                'php_version' => phpversion(),
            ]
        ], 200);
    }

    /**
     * Endpoint protegido (requiere autenticación)
     * GET /api/test/protected
     */
    public function testProtected()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'message' => 'Acceso autorizado',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token_info' => [
                'valid' => true,
                'checked_at' => now()->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Endpoint para probar POST
     * POST /api/test/echo
     */
    public function echo(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Datos recibidos correctamente',
            'received_data' => $request->all(),
            'received_at' => now()->format('Y-m-d H:i:s'),
        ], 200);
    }

    /**
     * Endpoint para simular datos de usuario
     * GET /api/test/users
     */
    public function getUsers()
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Juan Pérez',
                    'email' => 'juan@example.com',
                    'role' => 'admin',
                ],
                [
                    'id' => 2,
                    'name' => 'María García',
                    'email' => 'maria@example.com',
                    'role' => 'user',
                ],
                [
                    'id' => 3,
                    'name' => 'Carlos López',
                    'email' => 'carlos@example.com',
                    'role' => 'user',
                ],
            ],
            'total' => 3,
        ], 200);
    }

    /**
     * Endpoint para simular error
     * GET /api/test/error
     */
    public function testError()
    {
        return response()->json([
            'success' => false,
            'message' => 'Este es un error simulado',
            'error_code' => 'TEST_ERROR',
        ], 400);
    }

    /**
     * Endpoint para probar diferentes códigos HTTP
     * GET /api/test/status/{code}
     */
    public function testStatus($code)
    {
        $messages = [
            200 => 'OK - Solicitud exitosa',
            201 => 'Created - Recurso creado',
            400 => 'Bad Request - Solicitud incorrecta',
            401 => 'Unauthorized - No autorizado',
            403 => 'Forbidden - Prohibido',
            404 => 'Not Found - No encontrado',
            500 => 'Internal Server Error - Error del servidor',
        ];

        return response()->json([
            'status_code' => (int)$code,
            'message' => $messages[$code] ?? 'Código de estado desconocido',
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ], (int)$code);
    }

    /**
     * Endpoint para simular delay (útil para testing de loaders)
     * GET /api/test/delay/{seconds}
     */
    public function testDelay($seconds = 2)
    {
        sleep(min($seconds, 10)); // Máximo 10 segundos
        
        return response()->json([
            'success' => true,
            'message' => "Respuesta después de {$seconds} segundos",
            'delayed_seconds' => $seconds,
        ], 200);
    }
}