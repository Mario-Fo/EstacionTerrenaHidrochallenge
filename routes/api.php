<?php

use App\Http\Controllers\Api\LecturaSensorController;
use Illuminate\Support\Facades\Route;

Route::post('/lecturas-multi', [LecturaSensorController::class, 'store']);

Route::get('/lecturas/ultima', [LecturaSensorController::class, 'ultima']);
