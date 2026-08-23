<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PortalApiController;
use App\Http\Controllers\WhatsAppWebhookController;

Route::prefix('v1/iam')->group(function () {
    Route::post('/login', [PortalApiController::class, 'login']);
    Route::post('/provisionar', [PortalApiController::class, 'provisionarConta']);
});

Route::get('/v1/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/v1/whatsapp/webhook', [WhatsAppWebhookController::class, 'receive']);
