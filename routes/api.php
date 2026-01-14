<?php

use App\Http\Controllers\WablasWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Wablas Webhook endpoint
Route::post('/webhook/wablas', [WablasWebhookController::class, 'handle']);
