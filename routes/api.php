<?php

use App\Http\Controllers\Api\ComandoController;
use App\Http\Controllers\Api\LecturaSensorController;
use Illuminate\Support\Facades\Route;

Route::post('/lecturas-multi', [LecturaSensorController::class, 'store']);

Route::get('/lecturas/ultima', [LecturaSensorController::class, 'ultima']);

Route::post('/comandos/desplegar', [ComandoController::class, 'desplegar']);
Route::get('/comandos/pendiente', [ComandoController::class, 'pendiente']);
