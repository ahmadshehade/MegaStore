<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentManagement\Http\Controllers\Api\V1\InvoiceController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\LedgerEntryController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\PaymentController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\PaymentMethodController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\RefundController;
use Modules\PaymentManagement\Http\Controllers\PaymentManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    /**
     * Payment Method
     */
    Route::prefix('paymentMethods')->group(function () {
        Route::post('/', [PaymentMethodController::class, 'store'])
            ->name('paymentMethod.store');
        Route::get('/{paymentMethod}', [PaymentMethodController::class, 'show'])
            ->name('paymentMethod.show');
        Route::post('/{paymentMethod}', [PaymentMethodController::class, 'update'])
            ->name('paymentMethod.update');
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])
            ->name('paymentMethod.delete');
        Route::get('/', [PaymentMethodController::class, 'index'])
            ->name('paymentMethod.all');
    });


    /**
     *Payments
     */
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payment.all');
        Route::post('/', [PaymentController::class, 'store'])
            ->name('payment.store');
        Route::get('/{payment}', [PaymentController::class, 'show'])
            ->name('payment.show');
    });


    /**
     *Invoice
     */
    Route::prefix('invoices')
        ->middleware(['can:admin-job'])
        ->group(function () {
            Route::get('/trashed', [InvoiceController::class, 'getTrashedInvoices'])
                ->name('invoices.trashed');
            Route::get('/trashed/{invoice}', [InvoiceController::class, 'getTrashedInvoice'])
                ->name('invoices.trashed.show');
            Route::get('/', [InvoiceController::class, 'index'])
                ->name('invoices.all');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])
                ->name('invoice.show');
        });


    /**
     *Ledger Entry
     */
    Route::prefix('ledgerEntries')
        ->middleware(['can:admin-job'])
        ->group(function () {
            Route::get('/trashed', [LedgerEntryController::class, 'getTrashedLedgerEntries'])
                ->name('ledgerEntry.trashed');
            Route::get('/trashed/{ledgerEntry}', [LedgerEntryController::class, 'getTrashedLedgerEntry'])
                ->name('ledgerEntry.trashed.show');
            Route::get('/', [LedgerEntryController::class, 'index'])
                ->name('ledgerEntry.all');
            Route::get('/{ledgerEntry}', [LedgerEntryController::class, 'show'])
                ->name('ledgerEntry.show');
        });



    Route::prefix('refunds')->middleware(['can:admin-job'])
        ->group(function () {
            Route::get('/', [RefundController::class, 'index'])
                ->name('refunds.all');
            Route::get('/{refund}', [RefundController::class, 'show'])
                ->name('refund.show');
        });
});
