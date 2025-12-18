<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonasController;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/personas/pendientes', [PersonasController::class, 'obtenerPersonas']);
//Route::get('/registrar-persona', [PersonasController::class, 'registrarPersona'])->name('personas.registrar');
Route::get('/personas/crear', [PersonasController::class, 'create'])->name('personas.crear');
Route::post('/personas/store', [PersonasController::class, 'store'])->name('personas.store');

Route::get('/personas', [PersonasController::class, 'index'])->name('personas.index');
Route::get('/personas/{id}/reporte', [PersonasController::class, 'reporte'])->name('personas.reporte');




