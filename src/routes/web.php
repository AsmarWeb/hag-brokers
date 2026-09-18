<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminShipmentController;
use App\Http\Controllers\Admin\AdminQuotationController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
*/

Route::get('/language/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'ar'])) {
        abort(400);
    }

    session(['locale' => $locale]);

    return redirect()->back();
})->name('language.switch');


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        DashboardController::class,
        'index',
    ])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Shipments
    |--------------------------------------------------------------------------
    */

    Route::get('/shipments', [
        ShipmentController::class,
        'index',
    ])->name('shipments.index');

    Route::get('/shipments/{shipment}', [
        ShipmentController::class,
        'show',
    ])->name('shipments.show');

    Route::get('/shipments/{shipment}/tracking', [
        ShipmentController::class,
        'tracking',
    ])->name('shipments.tracking');


    /*
    |--------------------------------------------------------------------------
    | Shipment Documents
    |--------------------------------------------------------------------------
    */

    Route::get('/shipments/{shipment}/documents', [
        DocumentController::class,
        'index',
    ])->name('shipments.documents.index');

    Route::post('/shipments/{shipment}/documents', [
        DocumentController::class,
        'store',
    ])->name('shipments.documents.store');


    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    Route::get('/quotations', [
        QuotationController::class,
        'index',
    ])->name('quotations.index');

    Route::get('/quotations/create', [
        QuotationController::class,
        'create',
    ])->name('quotations.create');

    Route::post('/quotations', [
        QuotationController::class,
        'store',
    ])->name('quotations.store');

    Route::get('/quotations/{quotation}', [
        QuotationController::class,
        'show',
    ])->name('quotations.show');


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [
        NotificationController::class,
        'index',
    ])->name('notifications.index');

    Route::post('/notifications/{notification}/read', [
        NotificationController::class,
        'markRead',
    ])->name('notifications.read');


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update',
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy',
    ])->name('profile.destroy');
});


// ======================================================
// ADMIN + SUPER ADMIN
// ======================================================

Route::middleware(['auth', 'verified', 'role:super-admin|admin',])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index',
        ])->name('dashboard');


        // Shipments
        Route::get('/shipments', [
            AdminShipmentController::class,
            'index',
        ])->name('shipments.index');

        Route::get('/shipments/{shipment}', [
            AdminShipmentController::class,
            'show',
        ])->name('shipments.show');


        // Quotations
        Route::get('/quotations', [
            AdminQuotationController::class,
            'index',
        ])->name('quotations.index');

        Route::get('/quotations/{quotation}', [
            AdminQuotationController::class,
            'show',
        ])->name('quotations.show');

        Route::patch('/quotations/{quotation}/status', [
            AdminQuotationController::class,
            'updateStatus',
        ])->name('quotations.status');


        // Customers
        Route::get('/customers', [
            AdminCustomerController::class,
            'index',
        ])->name('customers.index');


        // Users
        Route::get('/users', [
            AdminUserController::class,
            'index',
        ])->name('users.index');

        Route::patch('/users/{user}/customer', [
            AdminUserController::class,
            'updateCustomer',
        ])->name('users.customer');
    });


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
