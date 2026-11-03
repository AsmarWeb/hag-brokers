<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\MobileAuthController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PublicMarketplaceController;
use App\Http\Controllers\Api\V1\QuotationController;
use App\Http\Controllers\Api\V1\ShipmentController;
use App\Http\Controllers\StripeLiveWebhookController;
use App\Http\Controllers\StripeTestWebhookController;
use App\Http\Middleware\MobileApi;
use App\Http\Middleware\MobileToken;
use Illuminate\Support\Facades\Route;

// Route::prefix('v1')->group(function () {
//     Route::post('/auth/login', [AuthController::class, 'login']);
//     Route::post('/auth/register', [AuthController::class, 'register']);
//     Route::middleware('auth:sanctum')->group(function () {
//         Route::post('/auth/logout', [AuthController::class, 'logout']);
//         Route::get('/dashboard', [ShipmentController::class, 'dashboard']);
//         Route::apiResource('shipments', ShipmentController::class)->only(['index', 'show']);
//         Route::get('/shipments/{shipment}/tracking', [ShipmentController::class, 'tracking']);
//         Route::get('/shipments/{shipment}/documents', [DocumentController::class, 'index']);
//         Route::post('/shipments/{shipment}/documents', [DocumentController::class, 'store']);
//         Route::apiResource('quotations', QuotationController::class)->only(['index', 'store', 'show']);
//         Route::get('/notifications', [NotificationController::class, 'index']);
//         Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
//         Route::get('/profile', [ProfileController::class, 'show']);
//         Route::put('/profile', [ProfileController::class, 'update']);
//     });
// });

Route::post('/platform-billing/stripe/webhook', StripeTestWebhookController::class)->name('platform-billing.webhook');

Route::post('/platform-billing/stripe/live/webhook', StripeLiveWebhookController::class)->name('platform-billing.live-webhook');

// Versioned native client API. Stripe endpoints above are unchanged.
Route::prefix('v1')->middleware([MobileApi::class, 'throttle:120,1,mobile-api:'])->group(function () {
    $auth = MobileAuthController::class;
    $public = PublicMarketplaceController::class;
    Route::get('/catalog', [$public, 'catalog']);
    Route::get('/opportunities', [$public, 'index']);
    Route::get('/opportunities/{slug}', [$public, 'show']);
    Route::get('/companies/{slug}', [$public, 'company']);
    Route::post('/auth/register', [$auth, 'register'])->middleware('throttle:5,1,mobile-register:');
    Route::post('/auth/login', [$auth, 'login'])->middleware('throttle:5,1,mobile-login:');
    Route::post('/auth/forgot-password', [$auth, 'forgot'])->middleware('throttle:3,1,mobile-forgot:');
    Route::post('/auth/reset-password', [$auth, 'reset'])->middleware('throttle:5,1,mobile-reset:');
    Route::get('/auth/verify-email/{id}/{hash}', [$auth, 'verify'])->middleware('signed')->name('api.v1.verify');
    Route::middleware(['auth:sanctum', MobileToken::class])->group(function () use ($auth) {
        Route::get('/me', [$auth, 'me']);
        Route::get('/company/workspace', [$auth, 'workspace']);
        Route::post('/auth/logout', [$auth, 'logout']);
        Route::get('/auth/devices', [$auth, 'devices']);
        Route::delete('/auth/devices/{device}', [$auth, 'revoke'])->whereNumber('device');
        Route::post('/auth/verification-email', [$auth, 'resend'])->middleware('throttle:3,1,mobile-resend:');
    });
});
