<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AutoatendimentoController;
use App\Http\Controllers\CorporateAuthController;
use App\Http\Controllers\IdentityDirectoryController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [AdminController::class, 'index']);
Route::post('/acao', [AdminController::class, 'acaoRapida']);
Route::get('/login', [CorporateAuthController::class, 'login']);
Route::post('/login', [CorporateAuthController::class, 'login']);
Route::get('/logout', [CorporateAuthController::class, 'logout']);

Route::prefix('identity-directory')->group(function () {
    Route::get('/', [IdentityDirectoryController::class, 'index']);
    Route::post('/settings', [IdentityDirectoryController::class, 'saveSettings']);
    Route::post('/test', [IdentityDirectoryController::class, 'test']);
    Route::post('/sync', [IdentityDirectoryController::class, 'sync']);
});

Route::any('/Conta/Login', [AutoatendimentoController::class, 'index']);
Route::any('/Conta/Logout', [AutoatendimentoController::class, 'doLogout']);
Route::any('/Conta/Perfil', [AutoatendimentoController::class, 'index']);
Route::any('/Conta/Termos', [AutoatendimentoController::class, 'index']);
Route::any('/User/ResetPassword', [AutoatendimentoController::class, 'index']);
Route::any('/User/Validate', [AutoatendimentoController::class, 'index']);
Route::any('/User/NewPassword', [AutoatendimentoController::class, 'index']);
Route::any('/User/NewUser', [AutoatendimentoController::class, 'index']);
Route::post('/sync-assinatura', [AdminController::class, 'syncAssinatura']);
Route::post('/acao/sync-assinatura', function() {
    return response()->json([
        'success' => false,
        'message' => '⚠️ Microsoft descontinuou a API de assinatura. Configure manualmente no Outlook Web.'
    ]);
});
