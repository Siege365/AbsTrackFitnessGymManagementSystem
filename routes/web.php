<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InventorySupplyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MembershipPaymentController;
use App\Http\Controllers\Api\MemberApiController;
use App\Http\Controllers\RefundsController;

//Inventory Supply Routes
Route::get('/inventory', [InventorySupplyController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventorySupplyController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{id}', [InventorySupplyController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/bulk-delete', [InventorySupplyController::class, 'bulkDelete'])->name('inventory.bulk-delete');
Route::delete('/inventory/{id}', [InventorySupplyController::class, 'destroy'])->name('inventory.destroy');
Route::prefix('inventory')->name('inventory.')->group(function () {
Route::get('/', [InventorySupplyController::class, 'index'])->name('index');
Route::post('/', [InventorySupplyController::class, 'store'])->name('store');
Route::put('/{id}', [InventorySupplyController::class, 'update'])->name('update');
Route::delete('/{id}', [InventorySupplyController::class, 'destroy'])->name('destroy');
Route::delete('/', [InventorySupplyController::class, 'bulkDelete'])->name('bulk-delete');

// Stock transaction routes
Route::post('/{id}/stock-transaction', [InventorySupplyController::class, 'stockTransaction'])->name('stock-transaction');
Route::get('/{id}/transaction-history', [InventorySupplyController::class, 'transactionHistory'])->name('transaction-history');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Dashboard (Protected)
Route::get('/', function () {
    return view('pages.dashboard');
})->middleware('auth')->name('dashboard');

// Protected Routes (Require Authentication)
Route::middleware(['auth'])->group(function () {
    // UI Elements
    Route::get('/ui/buttons', function () {
        return view('pages.ui.buttons');
    })->name('ui.buttons');

    Route::get('/ui/dropdowns', function () {
        return view('pages.ui.dropdowns');
    })->name('ui.dropdowns');

    Route::get('/ui/typography', function () {
        return view('pages.ui.typography');
    })->name('ui.typography');

    // Forms
    Route::get('/forms/basic-elements', function () {
        return view('pages.forms.basic-elements');
    })->name('forms.basic-elements');

    // Tables
    Route::get('/tables/basic-table', function () {
        return view('pages.tables.basic-table');
    })->name('tables.basic-table');

    // Charts
    Route::get('/charts/chartjs', function () {
        return view('pages.charts.chartjs');
    })->name('charts.chartjs');

    // Icons
    Route::get('/icons/mdi', function () {
        return view('pages.icons.mdi');
    })->name('icons.mdi');

    Route::get('/Session', function () {
        return view('Sessions.Session');
    })->name('Session');
    
    // Payment Routes (consolidated)
    Route::get('/PaymentAndBilling', [PaymentController::class, 'index'])->name('PaymentAndBilling');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/{payment}/receipt-data', [PaymentController::class, 'receiptData'])->name('payments.receiptData');
    Route::delete('/payments/bulk-delete', [PaymentController::class, 'bulkDelete'])->name('payments.bulkDelete');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/payments/membership', [PaymentController::class, 'membership'])
    ->name('payments.membership');
    
    Route::middleware(['auth'])->group(function () {
    // Payment routes
    Route::get('/membership-payment', [MembershipPaymentController::class, 'index'])
        ->name('membership.payment.index');
    Route::post('/membership-payment', [MembershipPaymentController::class, 'store'])
        ->name('membership.payment.store');
    Route::get('/membership-payment/{id}/receipt', [MembershipPaymentController::class, 'receiptData'])
        ->name('membership.payment.receipt');
    Route::delete('/membership-payment/{id}', [MembershipPaymentController::class, 'destroy'])
        ->name('membership.payment.destroy');
    Route::delete('/membership-payment-bulk', [MembershipPaymentController::class, 'bulkDelete'])
        ->name('membership.payment.bulkDelete');
    
    // Member search API
    Route::get('/api/members/search', [MemberApiController::class, 'search']);
    Route::get('/api/members/{id}', [MemberApiController::class, 'show']);
    
    // Refunds Routes
    Route::get('/refunds', [RefundsController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/search-customer', [RefundsController::class, 'searchCustomer'])->name('refunds.search-customer');
    Route::get('/refunds/details', [RefundsController::class, 'getPaymentDetails'])->name('refunds.get-details');
    Route::get('/refunds/summary', [RefundsController::class, 'getSummary'])->name('refunds.get-summary');
    Route::get('/refunds/audit/{type}/{paymentId}', [RefundsController::class, 'auditHistory'])->name('refunds.audit-history');
    Route::get('/refunds/{payment}/transactions', [RefundsController::class, 'getTransactions'])->name('refunds.transactions');
    Route::post('/refunds', [RefundsController::class, 'store'])->name('refunds.store');
    Route::post('/refunds/{payment}/process-refund', [RefundsController::class, 'processRefund'])->name('refunds.process-refund');
    Route::post('/refunds/{refund}/cancel', [RefundsController::class, 'cancel'])->name('refunds.cancel');
    Route::get('/refunds/{id}/show', [RefundsController::class, 'show'])->name('refunds.show');
    Route::delete('/refunds/{refund}', [RefundsController::class, 'destroy'])->name('refunds.destroy');
    Route::delete('/refunds-bulk-delete', [RefundsController::class, 'bulkDelete'])->name('refunds.bulk-delete');
    });
    
    Route::get('/ReportAndBilling', function () {
        return view('ReportAndBilling.ReportAndBilling');
    })->name('ReportAndBilling');

    // User and Admin //
    Route::get('/UserAndAdmin/UserManagement', function () {
        return view('UserAndAdmin.UserManagement');
    })->name('UserAndAdmin.UserManagement');

    Route::get('/UserAndAdmin/TrainerManagement', function () {
        return view('UserAndAdmin.TrainerManagement');
    })->name('UserAndAdmin.TrainerManagement');
    
    Route::get('/UserAndAdmin/CashierActivity', function () {
        return view('UserAndAdmin.CashierActivity');
    })->name('UserAndAdmin.CashierActivity');

    // Memberships CRUD
    Route::delete('memberships/bulk-delete', [MembershipController::class, 'bulkDelete'])->name('memberships.bulk-delete');
    Route::post('memberships/{membership}/renew', [MembershipController::class, 'renew'])->name('memberships.renew');
    Route::resource('memberships', MembershipController::class);
    Route::get('/members/search', [MembershipController::class, 'search'])->name('members.search');

    // Clients CRUD
    Route::delete('clients/bulk-delete', [ClientController::class, 'bulkDelete'])->name('clients.bulk-delete');
    Route::post('clients/{client}/renew', [ClientController::class, 'renew'])->name('clients.renew');
    Route::resource('clients', ClientController::class);
});

// Sample Pages
Route::get('/samples/blank-page', function () {
    return view('pages.samples.blank-page');
})->name('samples.blank-page');

Route::get('/samples/error-404', function () {
    return view('pages.samples.error-404');
})->name('samples.error-404');

Route::get('/samples/error-500', function () {
    return view('pages.samples.error-500');
})->name('samples.error-500');

Route::get('/samples/login', function () {
    return view('pages.samples.login');
})->name('samples.login');

Route::get('/samples/register', function () {
    return view('pages.samples.register');
})->name('samples.register');