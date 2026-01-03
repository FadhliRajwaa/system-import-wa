<?php

use App\Http\Controllers\SaungwaWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// SaungWA Webhook endpoint
Route::post('/webhook/saungwa', [SaungwaWebhookController::class, 'handle']);
