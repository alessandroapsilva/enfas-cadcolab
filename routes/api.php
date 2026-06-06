<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PortalApiController;

// Grupo de APIs REST (Substitui as antigas Views)
Route::prefix('v1/iam')->group(function () {
    Route::post('/login', [PortalApiController::class, 'login']);
    Route::post('/provisionar', [PortalApiController::class, 'provisionarConta']);
});
