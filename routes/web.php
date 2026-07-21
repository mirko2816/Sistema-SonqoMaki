<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/iniciar-sesion', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/iniciar-sesion', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/pacientes/archivados', [PatientController::class, 'archived'])->name('patients.archived');
    Route::patch('/pacientes/{patient}/estado', [PatientController::class, 'changeStatus'])->name('patients.status');
    Route::resource('/pacientes', PatientController::class)
        ->parameters(['pacientes' => 'patient'])
        ->names('patients')
        ->except('destroy');
    Route::delete('/pacientes/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
    Route::resource('/ejercicios', ExerciseController::class)
        ->parameters(['ejercicios' => 'exercise'])
        ->names('exercises');
    Route::post('/cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
