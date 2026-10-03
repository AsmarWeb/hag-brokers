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
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\OpportunityController;

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

Route::middleware('maintenance.check')->group(function () {

    // Public website routes

    Route::get('/', function () {
        return view('welcome');
    })->name('home');
});


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

/*
|--------------------------------------------------------------------------
| Opportunities
|--------------------------------------------------------------------------
*/

Route::get('/opportunities', [
    OpportunityController::class,
    'index',
])->name('opportunities.index');

Route::get('/opportunities/create', [
    OpportunityController::class,
    'create',
])->middleware('auth')->name('opportunities.create');

Route::post('/opportunities', [
    OpportunityController::class,
    'store',
])->middleware('auth')->name('opportunities.store');

Route::get('/opportunities/{opportunity}', [
    OpportunityController::class,
    'show',
])->name('opportunities.show');

Route::get('/opportunities/{opportunity}/interest', [
    OpportunityController::class,
    'interest',
])->middleware('auth')->name('opportunities.interest');

Route::post('/opportunities/{opportunity}/interest', [
    OpportunityController::class,
    'storeInterest',
])->middleware('auth')->name('opportunities.interest.store');


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

        Route::resource('leads', \App\Http\Controllers\Admin\AdminLeadController::class);

        Route::resource('contacts', \App\Http\Controllers\Admin\AdminContactController::class);

        Route::resource('activities', \App\Http\Controllers\Admin\AdminActivityController::class);

        Route::resource('tasks', \App\Http\Controllers\Admin\AdminTaskController::class);

        Route::resource('opportunities', \App\Http\Controllers\Admin\AdminOpportunityController::class);


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


        Route::get('/settings', [AdminSettingController::class, 'edit'])
            ->name('settings.edit');

        Route::put('/settings', [AdminSettingController::class, 'update'])
            ->name('settings.update');


        // Customers
        Route::resource('customers', AdminCustomerController::class);

        Route::resource('ports', AdminPortController::class);

        Route::resource('carriers', AdminCarrierController::class);

        Route::resource('vessels', AdminVesselController::class);


        // Users
        Route::resource('users', AdminUserController::class)
            ->names('users');

        Route::patch(
            '/users/{user}/customer',
            [AdminUserController::class, 'updateCustomer']
        )->name('users.customer');

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
