<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ActionRouterController;
use App\Http\Controllers\AutoatendimentoController;
use App\Http\Controllers\CommunicationLogController;
use App\Http\Controllers\CorporateAuthController;
use App\Http\Controllers\DashboardRouterController;
use App\Http\Controllers\EnterpriseSettingsController;
use App\Http\Controllers\EnterpriseIntelligenceController;
use App\Http\Controllers\IdentityDirectoryController;

Route::get('/', fn () => redirect('/login'));

Route::get('/dashboard', [DashboardRouterController::class, 'index']);
Route::post('/acao', [ActionRouterController::class, 'handle']);
Route::get('/login', [CorporateAuthController::class, 'login']);
Route::post('/login', [CorporateAuthController::class, 'login']);
Route::get('/logout', [CorporateAuthController::class, 'logout']);

Route::prefix('enterprise-settings')->group(function () {
    Route::get('/identity', [EnterpriseSettingsController::class, 'identity']);
    Route::post('/identity', [EnterpriseSettingsController::class, 'saveIdentity']);
    Route::post('/identity/test', [EnterpriseSettingsController::class, 'testIdentity']);
    Route::post('/identity/sync', [EnterpriseSettingsController::class, 'syncIdentity']);
});

Route::prefix('enterprise')->group(function () {
    Route::get('/insights', [EnterpriseIntelligenceController::class, 'insights']);
    Route::get('/reports/summary', [EnterpriseIntelligenceController::class, 'reportSummary']);
    Route::get('/reports/operational', [EnterpriseIntelligenceController::class, 'operationalReport']);
});

Route::get('/communication-logs/emails', [CommunicationLogController::class, 'emails']);

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
Route::post('/acao/sync-assinatura', fn () => response()->json(['success'=>false,'message'=>'⚠️ Microsoft descontinuou a API de assinatura. Configure manualmente no Outlook Web.']));
