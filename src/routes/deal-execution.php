<?php

use App\Http\Controllers\Admin\AdminDealExecutionController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    \App\Http\Middleware\EnsureUserNotBlocked::class,
    'role:super-admin|admin',
])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/deal-execution', [AdminDealExecutionController::class, 'index'])
        ->name('deal-execution.index');
    Route::get('/deal-execution/{dealDocument}/create', [AdminDealExecutionController::class, 'create'])
        ->whereNumber('dealDocument')->name('deal-execution.create');
    Route::post('/deal-execution/{dealDocument}', [AdminDealExecutionController::class, 'store'])
        ->whereNumber('dealDocument')->middleware('throttle:10,1')->name('deal-execution.store');
    Route::get('/deal-execution/shipments/{shipment}', [AdminDealExecutionController::class, 'show'])
        ->whereNumber('shipment')->name('deal-execution.show');
});
