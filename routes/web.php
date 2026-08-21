<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AutoatendimentoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [AdminController::class, 'login'])->name('login');
Route::post('/login', [AdminController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.attempt');

Route::middleware('admin.auth')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/acao', [AdminController::class, 'acaoRapida'])->name('admin.action');
    Route::match(['get', 'post'], '/logout', [AdminController::class, 'logout'])->name('logout');
    Route::post('/sync-assinatura', [AdminController::class, 'syncAssinatura']);
    Route::post('/acao/sync-assinatura', [AdminController::class, 'syncAssinatura']);
});

Route::any('/Conta/Login', [AutoatendimentoController::class, 'index']);
Route::any('/Conta/Logout', [AutoatendimentoController::class, 'doLogout']);
Route::any('/Conta/Perfil', [AutoatendimentoController::class, 'index']);
Route::any('/Conta/Termos', [AutoatendimentoController::class, 'index']);
Route::any('/User/ResetPassword', [AutoatendimentoController::class, 'index']);
Route::any('/User/Validate', [AutoatendimentoController::class, 'index']);
Route::any('/User/NewPassword', [AutoatendimentoController::class, 'index']);
Route::any('/User/NewUser', [AutoatendimentoController::class, 'index']);
