<?php

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CompararController;
use App\Http\Controllers\DatosHistoricosController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SimulacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);

Route::get('/comparacion', [CompararController::class, 'index'])->name('comparacion');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/datosh', [DatosHistoricosController::class, 'index'])->name('datosh');

Route::get('/config', [ConfigController::class, 'edit'])->name('config');
Route::post('/config', [ConfigController::class, 'update'])->name('config.update');

Route::get('/simulacion', function () {
    return view('Simulacion.sim');
})->name('simulacion');

Route::post('/simulacion/run', [SimulacionController::class, 'run'])->name('simulacion.run');
