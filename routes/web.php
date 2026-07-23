<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanRoutineController;
use App\Http\Controllers\RoutineTemplateController;
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
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
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
    Route::get('/rutinas/buscar-ejercicios', [RoutineTemplateController::class, 'exerciseSearch'])
        ->name('routine-templates.exercise-search');
    Route::resource('/rutinas', RoutineTemplateController::class)
        ->parameters(['rutinas' => 'routine_template'])
        ->names('routine-templates');
    Route::patch('/planes/{plan}/estado', [PlanController::class, 'changeStatus'])->name('plans.status');
    Route::get('/planes/{plan}/duplicar', [PlanController::class, 'duplicateForm'])->name('plans.duplicate-form');
    Route::post('/planes/{plan}/duplicar', [PlanController::class, 'duplicate'])->name('plans.duplicate');
    Route::get('/planes/{plan}/rutinas/copiar', [PlanRoutineController::class, 'copyForm'])->name('plans.routines.copy-form');
    Route::post('/planes/{plan}/rutinas/copiar', [PlanRoutineController::class, 'copy'])->name('plans.routines.copy');
    Route::resource('/planes/{plan}/rutinas', PlanRoutineController::class)->parameters(['rutinas' => 'routine'])->names('plans.routines')->except(['index', 'show']);
    Route::resource('/planes', PlanController::class)->parameters(['planes' => 'plan'])->names('plans')->except('destroy');
    Route::post('/cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
