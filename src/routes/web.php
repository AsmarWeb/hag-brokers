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
use App\Http\Controllers\Admin\AdminCompanySettingController;
use App\Http\Controllers\Admin\AdminVesselController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
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

    return redirect(app(\App\Services\Seo\PublicSeo::class)->languageReturn(request(), $locale));
})->name('language.switch');


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::middleware('maintenance.check')->group(function () {

    // Public website routes

    Route::get('/', [HomeController::class, 'index'])
        ->name('home');
});

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

    Route::patch('/notifications/read-all', [
        NotificationController::class,
        'markAllRead',
    ])->name('notifications.markAllRead');

    Route::get('/notifications/{notification}', [
        NotificationController::class,
        'show',
    ])->name('notifications.show');

    Route::patch('/notifications/{notification}/read', [
        NotificationController::class,
        'markRead',
    ])->name('notifications.markRead');

    Route::delete('/notifications/{notification}', [
        NotificationController::class,
        'destroy',
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

Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'index'])->name('seo.sitemap.index');
Route::get('/sitemaps/pages.xml', [\App\Http\Controllers\SeoController::class, 'pages'])->name('seo.sitemap.pages');
Route::get('/sitemaps/opportunities-{page}.xml', [\App\Http\Controllers\SeoController::class, 'opportunities'])->where('page', '[1-9][0-9]{0,5}')->name('seo.sitemap.opportunities');

Route::get('/opportunities', [
    OpportunityController::class,
    'index',
])->name('opportunities.index');

Route::get('/opportunities/create', [
    OpportunityController::class,
    'create',
])->middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class, 'throttle:30,1'])->name('opportunities.create');

Route::post('/opportunities', [
    OpportunityController::class,
    'store',
])->middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class, 'throttle:30,1'])->name('opportunities.store');

Route::post('/company/opportunity-assistant', \App\Http\Controllers\OpportunityAssistantController::class)
    ->middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class, 'throttle:4,1'])->name('company.opportunity-assistant');

Route::get('/opportunities/{opportunity:slug}', [
    OpportunityController::class,
    'show',
])->name('opportunities.show');

Route::get('/opportunities/{opportunity:slug}/interest', [
    OpportunityController::class,
    'interest',
])->middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class, 'throttle:30,1'])->name('opportunities.interest');

Route::post('/opportunities/{opportunity:slug}/interest', [
    OpportunityController::class,
    'storeInterest',
])->middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class, 'throttle:30,1'])->name('opportunities.interest.store');


Route::get('/bring-a-deal', [LeadController::class, 'create'])
    ->name('leads.create');

Route::post('/bring-a-deal', [LeadController::class, 'store'])
    ->name('leads.store');


// ======================================================
// ADMIN + SUPER ADMIN
// ======================================================

Route::middleware(['auth', 'verified', 'role:super-admin|admin',])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/company',
            [AdminCompanySettingController::class, 'edit']
        )->name('company.edit');

        Route::put(
            '/company',
            [AdminCompanySettingController::class, 'update']
        )->name('company.update');

        // Phase 9D: read-only approved deal and commission reports.
        Route::get('/finance', [\App\Http\Controllers\Admin\AdminFinancialReportController::class, 'index'])->name('finance.index');
        Route::get('/finance/{deal}', [\App\Http\Controllers\Admin\AdminFinancialReportController::class, 'show'])->name('finance.show');

        // Phase 9D.2A: internal invoice drafts only.
        Route::get('/invoice-drafts', [\App\Http\Controllers\Admin\AdminInvoiceDraftController::class, 'index'])->name('invoice-drafts.index');
        Route::get('/invoice-drafts/{invoiceDraft}', [\App\Http\Controllers\Admin\AdminInvoiceDraftController::class, 'show'])->name('invoice-drafts.show');
        Route::patch('/invoice-drafts/{invoiceDraft}', [\App\Http\Controllers\Admin\AdminInvoiceDraftController::class, 'update'])->name('invoice-drafts.update');
        Route::post('/commissions/{commission}/invoice-draft', [\App\Http\Controllers\Admin\AdminInvoiceDraftController::class, 'store'])->name('invoice-drafts.store');

        // Phase 9D.2B: explicit review and immutable invoice issuance.
        Route::get('/platform/billing', [\App\Http\Controllers\Admin\AdminCompanyBillingController::class, 'index'])->name('platform.billing');
        Route::get('/platform/companies', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'index'])->name('platform.index');
        Route::post('/platform/companies', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'store'])->name('platform.store');
        Route::get('/platform/companies/{workspace}', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'show'])->name('platform.show');
        Route::post('/platform/companies/{workspace}/members', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'member'])->name('platform.members');
        Route::delete('/platform/companies/{workspace}/members/{member}', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'remove'])->name('platform.members.remove');
        Route::post('/platform/companies/{workspace}/trial', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'trial'])->name('platform.trial');
        Route::post('/platform/companies/{workspace}/status', [\App\Http\Controllers\Admin\AdminCompanyWorkspaceController::class, 'status'])->name('platform.status');
        Route::get('/invoices', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}/payments', [\App\Http\Controllers\Admin\AdminInvoicePaymentController::class, 'show'])->name('invoices.payments');
        Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\Admin\AdminInvoicePaymentController::class, 'store'])->name('invoices.payments.store');
        Route::post('/invoices/{invoice}/payments/{payment}/reverse', [\App\Http\Controllers\Admin\AdminInvoicePaymentController::class, 'reverse'])->name('invoices.payments.reverse');
        Route::get('/invoices/{invoice}/pdf/{language}', [\App\Http\Controllers\Admin\AdminInvoicePdfController::class, 'download'])->where('language', 'ar|en')->name('invoices.pdf');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoice-drafts/{invoiceDraft}/review', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'review'])->name('invoices.review');
        Route::post('/invoice-drafts/{invoiceDraft}/preview', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'preview'])->name('invoices.preview');
        Route::post('/invoice-drafts/{invoiceDraft}/issue', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'issue'])->name('invoices.issue');

        // Dashboard
        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index',
        ])->name('dashboard');

        Route::resource('leads', \App\Http\Controllers\Admin\AdminLeadController::class);

        Route::resource('contacts', \App\Http\Controllers\Admin\AdminContactController::class);

        Route::resource('activities', \App\Http\Controllers\Admin\AdminActivityController::class);

        Route::resource('tasks', \App\Http\Controllers\Admin\AdminTaskController::class);

        Route::post('/opportunities/{opportunity}/moderation', [\App\Http\Controllers\Admin\AdminOpportunityController::class, 'moderate'])->middleware('throttle:30,1')->name('opportunities.moderate');
        Route::get('/opportunities/{opportunity}/offers',[\App\Http\Controllers\CompanyTradeOfferController::class,'adminComparison'])->name('trade-offers.comparison');
        Route::get('/trade-offers/{offer}/room',[\App\Http\Controllers\TradeOfferRoomController::class,'adminShow'])->name('trade-offers.room');
        Route::get('/trade-offers/{offer}',[\App\Http\Controllers\CompanyTradeOfferController::class,'adminShow'])->name('trade-offers.show');
        Route::get('/trade-offers/{offer}/attachments/{file}',[\App\Http\Controllers\CompanyTradeOfferController::class,'adminDownload'])->whereNumber('file')->name('trade-offers.download');
        Route::resource('opportunities', \App\Http\Controllers\Admin\AdminOpportunityController::class)->only(['index', 'show']);


        // Phase 9B: approve agreement and lock commission.
        Route::patch(
            '/deals/{deal}/agreements/{agreement}/approve',
            [\App\Http\Controllers\Admin\AdminDealApprovalController::class, 'approve']
        )->middleware('can:approve-deal-agreement')->name('deals.agreements.approve');

        // Phase 9B: agreement drafts (admin only).
        Route::post(
            '/deals/{deal}/agreements',
            [\App\Http\Controllers\Admin\AdminDealAgreementController::class, 'store']
        )->name('deals.agreements.store');

        // Phase 9B: create a draft deal from an approved quotation.
        Route::post(
            '/quotations/{quotation}/deals',
            [\App\Http\Controllers\Admin\AdminDealController::class, 'storeFromQuotation']
        )->name('quotations.deals.store');

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

        Route::patch('/quotations/{quotation}/assign', [
            AdminQuotationController::class,
            'assign',
        ])->name('quotations.assign');

        Route::post('/quotations/{quotation}/offers', [
            \App\Http\Controllers\Admin\AdminQuotationOfferController::class,
            'store',
        ])->name('quotations.offers.store');

        Route::patch('/quotations/{quotation}/offers/{offer}/approve', [
            \App\Http\Controllers\Admin\AdminQuotationOfferController::class,
            'approve',
        ])->name('quotations.offers.approve');

        Route::post('/quotations/{quotation}/final-pdf', [
            \App\Http\Controllers\Admin\AdminFinalQuotationPdfController::class,
            'store',
        ])->name('quotations.final-pdf.store');

        Route::patch('/quotations/{quotation}/final-pdf/{document}/approve', [
            \App\Http\Controllers\Admin\AdminFinalQuotationApprovalController::class,
            'approve',
        ])->name('quotations.final-pdf.approve');

        Route::post('/quotations/{quotation}/final-pdf/{document}/send', [
            \App\Http\Controllers\Admin\AdminQuotationDeliveryController::class,
            'store',
        ])->name('quotations.final-pdf.send');




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

        /*
|--------------------------------------------------------------------------
| Document Center
|--------------------------------------------------------------------------
*/

        Route::get('/documents', [AdminDocumentController::class, 'index'])
            ->name('documents.index');

        Route::get('/documents/create', [AdminDocumentController::class, 'create'])
            ->name('documents.create');

        Route::post('/documents', [AdminDocumentController::class, 'store'])
            ->name('documents.store');

        Route::get('/documents/generate-pdf', [AdminDocumentController::class, 'pdfCreate'])
            ->name('documents.pdf.create');

        Route::post(
            '/documents/preview-pdf',
            [AdminDocumentController::class, 'previewPdf']
        )->name('documents.pdf.preview');

        Route::post('/documents/generate-pdf', [AdminDocumentController::class, 'generatePdf'])
            ->name('documents.pdf.generate');

        Route::get('/documents/{document}/edit', [AdminDocumentController::class, 'edit'])
            ->name('documents.edit');

        Route::put('/documents/{document}', [AdminDocumentController::class, 'update'])
            ->name('documents.update');

        Route::get('/documents/{document}/versions', [AdminDocumentController::class, 'versions'])
            ->name('documents.versions');

        Route::post('/documents/{document}/versions', [AdminDocumentController::class, 'uploadVersion'])
            ->name('documents.versions.store');

        Route::get('/documents/{document}/download', [AdminDocumentController::class, 'download'])
            ->name('documents.download');

        Route::get('/documents/{document}/preview', [AdminDocumentController::class, 'preview'])
            ->name('documents.preview');

        Route::delete('/documents/{document}', [AdminDocumentController::class, 'destroy'])
            ->name('documents.destroy');

        // ============================================================
        // DOCUMENT VERSIONS
        // ============================================================

        Route::get(
            '/documents/{document}/versions/{version}/download',
            [AdminDocumentController::class, 'downloadVersion']
        )->name('documents.versions.download');

        Route::get(
            '/documents/{document}/versions/{version}/preview',
            [AdminDocumentController::class, 'previewVersion']
        )->name('documents.versions.preview');


        // ============================================================
        // DOCUMENT TEMPLATES
        // ============================================================

        Route::resource(
            'document-templates',
            \App\Http\Controllers\Admin\AdminDocumentTemplateController::class
        )->except(['show']);

        Route::get('/quotations', [
            AdminQuotationController::class,
            'index',
        ])->name('quotations.index');

        Route::get('/notifications', [
            AdminNotificationController::class,
            'index',
        ])->name('notifications.index');

        Route::get('/notifications/create', [
            AdminNotificationController::class,
            'create',
        ])->name('notifications.create');

        Route::post('/notifications', [
            AdminNotificationController::class,
            'store',
        ])->name('notifications.store');

        Route::patch('/notifications/read-all', [
            AdminNotificationController::class,
            'markAllRead',
        ])->name('notifications.markAllRead');

        Route::patch('/notifications/{notification}/read', [
            AdminNotificationController::class,
            'markRead',
        ])->name('notifications.markRead');


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

Route::get('/company/workspace', [\App\Http\Controllers\CompanyWorkspaceController::class, 'show'])->middleware(['auth', 'verified'])->name('company.workspace');

Route::middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class])->prefix('company')->name('company.')->group(function () {
    Route::get('/trade-offers/{offer}/room',[\App\Http\Controllers\TradeOfferRoomController::class,'show'])->name('trade-offers.room');
    Route::post('/trade-offers/{offer}/room',[\App\Http\Controllers\TradeOfferRoomController::class,'store'])->middleware('throttle:30,1')->name('trade-offers.room.store');
    Route::post('/trade-offers/{offer}/room/read',[\App\Http\Controllers\TradeOfferRoomController::class,'read'])->middleware('throttle:60,1')->name('trade-offers.room.read');
    Route::get('/trade-offers',[\App\Http\Controllers\CompanyTradeOfferController::class,'index'])->name('trade-offers.index');
    Route::get('/trade-offers/{offer}',[\App\Http\Controllers\CompanyTradeOfferController::class,'show'])->name('trade-offers.show');
    Route::post('/trade-offers/{offer}/decision',[\App\Http\Controllers\CompanyTradeOfferController::class,'decide'])->middleware('throttle:30,1')->name('trade-offers.decide');
    Route::get('/trade-offers/{offer}/attachments/{file}',[\App\Http\Controllers\CompanyTradeOfferController::class,'download'])->whereNumber('file')->name('trade-offers.download');
    Route::get('/opportunities/{opportunity}/offers',[\App\Http\Controllers\CompanyTradeOfferController::class,'comparison'])->name('trade-offers.comparison');
    Route::get('/opportunities/{opportunity}/offer',[\App\Http\Controllers\CompanyTradeOfferController::class,'create'])->name('trade-offers.create');
    Route::post('/opportunities/{opportunity}/offer',[\App\Http\Controllers\CompanyTradeOfferController::class,'store'])->middleware('throttle:15,1')->name('trade-offers.store');
    Route::get('/marketplace', [\App\Http\Controllers\CompanyMarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/opportunities/{opportunity}/edit', [\App\Http\Controllers\CompanyMarketplaceController::class, 'edit'])->name('opportunities.edit');
    Route::patch('/opportunities/{opportunity:slug}', [\App\Http\Controllers\CompanyMarketplaceController::class, 'update'])->name('opportunities.update');
    Route::post('/opportunities/{opportunity}/close', [\App\Http\Controllers\CompanyMarketplaceController::class, 'close'])->name('opportunities.close');
    Route::post('/applications/{interest}/decision', [\App\Http\Controllers\CompanyMarketplaceController::class, 'decide'])->name('applications.decide');
});

Route::middleware(['auth', \App\Http\Middleware\EnsureCompanyEmailVerified::class])->prefix('company/billing')->name('company.billing')->group(function () {
    Route::get('/', [\App\Http\Controllers\CompanyBillingController::class, 'show']);
    Route::post('/checkout', [\App\Http\Controllers\CompanyBillingController::class, 'checkout'])->middleware('throttle:10,1')->name('.checkout');
    Route::post('/refresh', [\App\Http\Controllers\CompanyBillingController::class, 'refresh'])->middleware('throttle:10,1')->name('.refresh');
    Route::post('/cancel', [\App\Http\Controllers\CompanyBillingController::class, 'cancel'])->middleware('throttle:10,1')->name('.cancel');
    Route::post('/portal', [\App\Http\Controllers\CompanyBillingController::class, 'portal'])->middleware('throttle:10,1')->name('.portal');
});

// Company-controlled public profile; no internal customer identifiers are accepted.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/company/profile', [\App\Http\Controllers\CompanyProfileController::class, 'edit'])->name('company.profile.edit');
    Route::put('/company/profile', [\App\Http\Controllers\CompanyProfileController::class, 'update'])->middleware('throttle:20,1')->name('company.profile.update');
    Route::get('/company/profile/preview', [\App\Http\Controllers\CompanyProfileController::class, 'preview'])->name('company.profile.preview');
});
Route::get('/companies/{slug}', [\App\Http\Controllers\CompanyProfileController::class, 'show'])->where('slug', 'company-[a-f0-9-]{36}')->name('companies.show');
