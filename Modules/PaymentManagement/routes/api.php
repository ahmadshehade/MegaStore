<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentManagement\Http\Controllers\Api\V1\InvoiceController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\LedgerEntryController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\PaymentController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\PaymentMethodController;
use Modules\PaymentManagement\Http\Controllers\Api\V1\RefundController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // -------------------------------
    // Payment Methods Routes
    // -------------------------------
    Route::prefix('paymentMethods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('paymentMethod.all');  // List all payment methods
        Route::post('/', [PaymentMethodController::class, 'store'])->name('paymentMethod.store'); // Create new payment method
        Route::get('/{paymentMethod}', [PaymentMethodController::class, 'show'])->name('paymentMethod.show'); // Get a payment method
        Route::post('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('paymentMethod.update'); // Update payment method
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('paymentMethod.delete'); // Delete payment method
    });

    // -------------------------------
    // Payments Routes
    // -------------------------------
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payment.all'); // List all payments
        Route::post('/', [PaymentController::class, 'store'])->name('payment.store'); // Create a new payment
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('payment.show'); // Get payment details
    });

    // -------------------------------
    // Invoices Routes (admin only)
    // -------------------------------
    Route::prefix('invoices')->middleware(['can:admin-job'])->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoices.all'); // List all invoices
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show'); // Get invoice details
        Route::get('/trashed', [InvoiceController::class, 'getTrashedInvoices'])->name('invoices.trashed'); // List soft-deleted invoices
        Route::get('/trashed/{invoice}', [InvoiceController::class, 'getTrashedInvoice'])->name('invoices.trashed.show'); // Get a trashed invoice
    });

    // -------------------------------
    // Ledger Entries Routes (admin only)
    // -------------------------------
    Route::prefix('ledgerEntries')->middleware(['can:admin-job'])->group(function () {
        Route::get('/', [LedgerEntryController::class, 'index'])->name('ledgerEntry.all'); // List all ledger entries
        Route::get('/{ledgerEntry}', [LedgerEntryController::class, 'show'])->name('ledgerEntry.show'); // Get ledger entry details
        Route::get('/trashed', [LedgerEntryController::class, 'getTrashedLedgerEntries'])->name('ledgerEntry.trashed'); // List soft-deleted ledger entries
        Route::get('/trashed/{ledgerEntry}', [LedgerEntryController::class, 'getTrashedLedgerEntry'])->name('ledgerEntry.trashed.show'); // Get a trashed ledger entry
    });

    // -------------------------------
    // Refunds Routes (admin only)
    // -------------------------------
    Route::prefix('refunds')->middleware(['can:admin-job'])->group(function () {
        Route::get('/', [RefundController::class, 'index'])->name('refunds.all'); // List all refunds
        Route::get('/{refund}', [RefundController::class, 'show'])->name('refund.show'); // Get refund details
    });
});
