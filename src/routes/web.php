<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminShipmentController;
use App\Http\Controllers\Admin\AdminQuotationController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminUserController;

use App\Http\Controllers\Admin\AdminDocumentController;

use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminPortController;
use App\Http\Controllers\Admin\AdminCarrierController;
use App\Http\Controllers\Admin\AdminVesselController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\AuthController;
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

Route::post('/register', [AuthController::class, 'register'])
    ->name('register');

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
        'index'
    ])->name('notifications.index');

    Route::get('/notifications/{notification}', [
        NotificationController::class,
        'show'
    ])->name('notifications.show');

    Route::patch('/notifications/{notification}/read', [
        NotificationController::class,
        'markRead'
    ])->name('notifications.markRead');

    Route::patch('/notifications/read-all', [
        NotificationController::class,
        'markAllRead'
    ])->name('notifications.markAllRead');

    Route::delete('/notifications/{notification}', [
        NotificationController::class,
        'destroy'
    ])->name('notifications.destroy');


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
        Route::resource(
            'shipments',
            AdminShipmentController::class
        );


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
        Route::resource('customers', AdminCustomerController::class);

        Route::resource('ports', AdminPortController::class);

        Route::resource('carriers', AdminCarrierController::class);

        Route::resource('vessels', AdminVesselController::class);


        // Users
        Route::get('/users', [
            AdminUserController::class,
            'index',
        ])->name('users.index');

        Route::patch('/users/{user}/customer', [
            AdminUserController::class,
            'updateCustomer',
        ])->name('users.customer');

        Route::get('/documents', [
            AdminDocumentController::class,
            'index',
        ])->name('documents.index');

        Route::get('/quotations', [
            AdminQuotationController::class,
            'index',
        ])->name('quotations.index');

        Route::get('/notifications', [
            AdminNotificationController::class,
            'index',
        ])->name('notifications.index');

        Route::get('/reports', [
            AdminReportController::class,
            'index',
        ])->name('reports.index');
    });


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
