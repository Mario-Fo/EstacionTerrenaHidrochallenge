<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CameraCommandTest extends TestCase
{
    public function test_camera_command_is_consumed_once_without_changing_deployment_command(): void
    {
        $originalStorage = storage_path();
        $temporaryStorage = sys_get_temp_dir().'/camera-command-'.bin2hex(random_bytes(8));
        $this->app->useStoragePath($temporaryStorage);

        try {
            $this->postJson('/api/comandos/camara')->assertOk()->assertJson(['ok' => true, 'command' => 'c']);
            $this->get('/api/comandos/pendiente')->assertOk()->assertContent('c');
            $this->get('/api/comandos/pendiente')->assertOk()->assertContent('');
            $this->postJson('/api/comandos/desplegar')->assertOk()->assertJson(['command' => 'e']);
            $this->get('/api/comandos/pendiente')->assertOk()->assertContent('e');
        } finally {
            $this->app->useStoragePath($originalStorage);
            File::deleteDirectory($temporaryStorage);
        }
    }
}
