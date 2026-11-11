<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanyNotificationApiController;
use App\Http\Controllers\Api\V1\CompanyOpportunityController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\MobileAuthController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PublicMarketplaceController;
use App\Http\Controllers\Api\V1\QuotationController;
use App\Http\Controllers\Api\V1\ShipmentController;
use App\Http\Controllers\Api\V1\TradeOfferApiController;
use App\Http\Controllers\Auth\PhoneVerificationController;
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

Route::get('/v1/carrier/fleet', [\App\Http\Controllers\CarrierPartnerController::class, 'apiFleet'])
    ->middleware([MobileApi::class, 'auth:sanctum', 'throttle:60,1,carrier-api:'])->name('api.carrier.fleet');

// Versioned native client API. Stripe endpoints above are unchanged.
Route::prefix('v1')->middleware([MobileApi::class, 'throttle:120,1,mobile-api:'])->group(function () {
    $auth = MobileAuthController::class;
    $public = PublicMarketplaceController::class;
    Route::get('/freight/sailings', [\App\Http\Controllers\FreightSailingController::class, 'index'])->name('api.freight.sailings');
    Route::get('/maritime-map', [\App\Http\Controllers\PublicExperienceController::class, 'map']);
    Route::get('/app-config', \App\Http\Controllers\Api\V1\MobileAppConfigController::class);
    Route::get('/legal/{slug}', [\App\Http\Controllers\LegalDocumentController::class, 'api'])->where('slug','privacy|terms');
    Route::get('/catalog', [$public, 'catalog']);
    Route::get('/opportunities', [$public, 'index']);
    Route::get('/opportunities/{slug}', [$public, 'show']);
    Route::get('/companies/{slug}', [$public, 'company']);
    Route::post('/auth/register', [$auth, 'register'])->middleware('throttle:5,1,mobile-register:');
    Route::post('/auth/login', [$auth, 'login'])->middleware('throttle:5,1,mobile-login:');
    Route::post('/auth/forgot-password', [$auth, 'forgot'])->middleware('throttle:3,1,mobile-forgot:');
    Route::post('/auth/reset-password', [$auth, 'reset'])->middleware('throttle:5,1,mobile-reset:');
    Route::get('/auth/verify-email/{id}/{hash}', [$auth, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('api.v1.verify');
    Route::post('/auth/verification-email', [$auth, 'resend'])->middleware('throttle:3,1,mobile-resend:');
    Route::middleware(['auth:sanctum', MobileToken::class, \App\Http\Middleware\EnsureUserNotBlocked::class])->group(function () use ($auth) {
        Route::put('/auth/phone', [PhoneVerificationController::class, 'updatePhone'])->middleware('throttle:5,1,phone-number-update:');
        Route::post('/auth/phone-verification', [PhoneVerificationController::class, 'resend'])->middleware('throttle:3,1,phone-verification-send:');
        Route::post('/auth/verify-phone', [PhoneVerificationController::class, 'verify'])->middleware('throttle:10,1,phone-verification-verify:');
        $companyWorkspace = \App\Http\Controllers\CompanyWorkspaceController::class;
        Route::get('/company/onboarding', [$companyWorkspace, 'onboarding']);
        Route::post('/company/onboarding', [$companyWorkspace, 'store'])->middleware('throttle:5,1,company-onboarding:');
        $push=\App\Http\Controllers\Api\V1\MobilePushController::class;
        Route::get('/push/settings',[$push,'settings']);
        Route::put('/push/device',[$push,'register'])->middleware('throttle:20,1,mobile-push-register:');
        Route::delete('/push/device',[$push,'remove']);
        Route::get('/push/resolve',[$push,'resolve']);
        $account = \App\Http\Controllers\Api\V1\AccountCompletionController::class;
        Route::get('/auth/account', [$account,'show']);
        Route::put('/auth/account', [$account,'update'])->middleware('throttle:5,1,mobile-account:');
        Route::get('/auth/deletion-request', [$account,'deletion']);
        Route::post('/auth/deletion-request', [$account,'requestDeletion'])->middleware('throttle:3,1,mobile-delete:');
        Route::middleware(\App\Http\Middleware\EnsureCompanyWorkspace::class)->group(function () {
        $freight = \App\Http\Controllers\Api\V1\FreightQuotationController::class;
        Route::get('/freight/ports', [$freight, 'ports']);
        Route::get('/freight/quotations', [$freight, 'index']);
        Route::post('/freight/quotations', [$freight, 'store'])->middleware('throttle:10,1,mobile-freight:');
        Route::get('/freight/quotations/{quotationId}', [$freight, 'show'])->whereNumber('quotationId');
        $offers = TradeOfferApiController::class;
        $notices = CompanyNotificationApiController::class;
        Route::get('/company/trade-offers', [$offers, 'index']);
        Route::get('/company/opportunities/{opportunityId}/offers', [$offers, 'comparison'])->whereNumber('opportunityId');
        Route::post('/company/opportunities/{opportunityId}/offers', [$offers, 'store'])->whereNumber('opportunityId')->middleware('throttle:15,1,mobile-offers:');
        Route::get('/company/trade-offers/{offerId}', [$offers, 'show'])->whereNumber('offerId');
        Route::get('/company/trade-offers/{offerId}/events', [$offers, 'events'])->whereNumber('offerId');
        Route::post('/company/trade-offers/{offerId}/decision', [$offers, 'decide'])->whereNumber('offerId')->middleware('throttle:30,1,mobile-offer-decision:');
        Route::get('/company/trade-offers/{offerId}/attachments/{file}', [$offers, 'download'])->whereNumber(['offerId', 'file']);
        Route::get('/company/trade-offers/{offerId}/messages', [$offers, 'messages'])->whereNumber('offerId');
        Route::post('/company/trade-offers/{offerId}/messages', [$offers, 'send'])->whereNumber('offerId')->middleware('throttle:30,1,mobile-message:');
        Route::post('/company/trade-offers/{offerId}/read', [$offers, 'read'])->whereNumber('offerId')->middleware('throttle:60,1,mobile-read:');
        Route::get('/company/notifications', [$notices, 'index']);
        Route::patch('/company/notifications/{notificationId}/read', [$notices, 'read'])->whereUuid('notificationId')->middleware('throttle:60,1,mobile-notice-read:');
        $opportunities = CompanyOpportunityController::class;
        Route::get('/company/opportunities', [$opportunities, 'index']);
        Route::post('/company/opportunities', [$opportunities, 'store'])->middleware('throttle:15,1,mobile-publish:');
        Route::get('/company/opportunities/{opportunityId}', [$opportunities, 'show'])->whereNumber('opportunityId');
        Route::patch('/company/opportunities/{opportunityId}', [$opportunities, 'update'])->whereNumber('opportunityId')->middleware('throttle:30,1,mobile-edit:');
        Route::post('/company/opportunities/{opportunityId}/close', [$opportunities, 'close'])->whereNumber('opportunityId')->middleware('throttle:30,1,mobile-close:');
        });
        Route::put('/auth/password', [$auth, 'changePassword'])->middleware('throttle:5,1,mobile-password:');
        Route::get('/me', [$auth, 'me']);
        Route::get('/company/workspace', [$auth, 'workspace']);
        Route::post('/auth/logout', [$auth, 'logout']);
        Route::get('/auth/devices', [$auth, 'devices']);
        Route::delete('/auth/devices/{device}', [$auth, 'revoke'])->whereNumber('device');
    });
});
