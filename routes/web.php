<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AutoatendimentoController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [AdminController::class, 'index']);
Route::post('/acao', [AdminController::class, 'acaoRapida']);
Route::get('/login', [AdminController::class, 'login']);
Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout']);

Route::any('/Conta/Login', [AutoatendimentoController::class, 'index']);
Route::any('/Conta/Logout', [AutoatendimentoController::class, 'doLogout']);
Route::any('/Conta/Perfil', [AutoatendimentoController::class, 'index']);
Route::any('/Conta/Termos', [AutoatendimentoController::class, 'index']);
Route::any('/User/ResetPassword', [AutoatendimentoController::class, 'index']);
Route::any('/User/Validate', [AutoatendimentoController::class, 'index']);
Route::any('/User/NewPassword', [AutoatendimentoController::class, 'index']);
Route::any('/User/NewUser', [AutoatendimentoController::class, 'index']);
Route::post('/sync-assinatura', [App\Http\Controllers\AdminController::class, 'syncAssinatura']);
Route::post('/acao/sync-assinatura', function() {
    return response()->json([
        'success' => false,
        'message' => '⚠️ Microsoft descontinuou a API de assinatura. Configure manualmente no Outlook Web.'
    ]);
});
