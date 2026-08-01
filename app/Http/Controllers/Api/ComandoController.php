<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class ComandoController extends Controller
{
    public function desplegar(): JsonResponse
    {
        $path = $this->commandPath();
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, 'e', LOCK_EX);

        return response()->json([
            'ok' => true,
            'message' => 'Comando de despliegue encolado.',
            'command' => 'e',
        ]);
    }

    public function pendiente(): Response
    {
        $path = $this->commandPath();
        File::ensureDirectoryExists(dirname($path));

        $file = fopen($path, 'c+');
        if ($file === false) {
            return response('', 500);
        }

        flock($file, LOCK_EX);
        rewind($file);
        $command = trim((string) stream_get_contents($file));
        ftruncate($file, 0);
        fflush($file);
        flock($file, LOCK_UN);
        fclose($file);

        return response($command, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'no-store');
    }

    private function commandPath(): string
    {
        return storage_path('app/private/esp32_command.txt');
    }
}
