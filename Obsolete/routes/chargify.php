<?php

use App\Http\Controllers\Chargify\ChargifyTokenController;
use App\Http\Controllers\Chargify\ChargifyWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('webhook', [ChargifyWebhookController::class, 'handleWebhook']);

Route::get('token', ChargifyTokenController::class)
    ->middleware(['trust.hosts', 'api'])
    ->name('chargify.token');
