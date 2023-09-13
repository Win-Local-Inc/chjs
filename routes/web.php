<?php

use Illuminate\Support\Facades\Route;
use WinLocalInc\Chjs\Http\Controllers\TokenController;
use WinLocalInc\Chjs\Http\Controllers\WebhookController;
use WinLocalInc\Chjs\Http\Middleware\VerifyWebhookSignature;

Route::post('api/webhook/v2', [WebhookController::class, 'handleWebhook'])
    ->middleware(VerifyWebhookSignature::class)->name('webhook.v2');

Route::get('api/chargify/token', TokenController::class)
    ->middleware(['trust.hosts', 'api'])
    ->name('chargify.token');
