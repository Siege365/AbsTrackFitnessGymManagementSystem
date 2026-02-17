<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InventorySupplyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MembershipPaymentController;
use App\Http\Controllers\API\MemberApiController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\RefundController;

//Inventory Supply Routes
Route::get('/inventory', [InventorySupplyController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventorySupplyController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{id}', [InventorySupplyController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/bulk-delete', [InventorySupplyController::class, 'bulkDelete'])->name('inventory.bulk-delete');
Route::delete('/inventory/{id}', [InventorySupplyController::class, 'destroy'])->name('inventory.destroy');
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventorySupplyController::class, 'index'])->name('index');
    Route::post('/', [InventorySupplyController::class, 'store'])->name('store');
    Route::get('/next-product-number', [InventorySupplyController::class, 'getNextProductNumber'])->name('next-product-number');
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

    Route::get('/Session', [SessionController::class, 'index'])->name('Session');
    
    // Session Routes - PT Schedules
    Route::prefix('sessions')->name('sessions.')->group(function () {
        // KPI refresh route
        Route::get('/kpis', [SessionController::class, 'getKPIs'])->name('kpis');
        
        // PT Schedule routes
        Route::post('/pt-schedule', [SessionController::class, 'storePTSchedule'])->name('pt.store');
        Route::get('/pt-schedule/{id}', [SessionController::class, 'getPTSchedule'])->name('pt.show');
        Route::put('/pt-schedule/{id}', [SessionController::class, 'updatePTSchedule'])->name('pt.update');
        Route::delete('/pt-schedule/{id}', [SessionController::class, 'destroyPTSchedule'])->name('pt.destroy');
        Route::patch('/pt-schedule/{id}/status', [SessionController::class, 'updatePTStatus'])->name('pt.status');
        Route::post('/pt-schedule/book-next', [SessionController::class, 'bookNextSession'])->name('pt.book-next');
        
        // Attendance routes
        Route::post('/attendance', [SessionController::class, 'storeAttendance'])->name('attendance.store');
        Route::get('/attendance/{id}', [SessionController::class, 'getAttendance'])->name('attendance.show');
        Route::put('/attendance/{id}', [SessionController::class, 'updateAttendance'])->name('attendance.update');
        Route::delete('/attendance/{id}', [SessionController::class, 'destroyAttendance'])->name('attendance.destroy');
        Route::delete('/attendance/bulk-delete', [SessionController::class, 'bulkDeleteAttendance'])->name('attendance.bulk-delete');
        
        // PT Schedule bulk delete
        Route::delete('/pt-schedule/bulk-delete', [SessionController::class, 'bulkDeletePT'])->name('pt.bulk-delete');
    });
    
    // Payment Routes (consolidated)
    // Product Payment Routes
    Route::get('/payments/products', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/products', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/products/bulk-delete', [PaymentController::class, 'bulkDelete'])->name('payments.bulkDelete');
    
    // Payment History Routes
    Route::get('/payments/history', [PaymentHistoryController::class, 'index'])->name('payments.history');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/{payment}/receipt-data', [PaymentController::class, 'receiptData'])->name('payments.receiptData');
    Route::delete('/payments/{payment}', [PaymentHistoryController::class, 'destroy'])->name('payments.destroy');
    Route::get('/payments/membership', [PaymentController::class, 'membership'])->name('payments.membership');
    // ==========================================
    // MEMBERSHIP PAYMENT ROUTES (UPDATED)
    // ==========================================
    Route::prefix('membership-payment')->name('membership.payment.')->group(function () {
        Route::get('/', [MembershipPaymentController::class, 'index'])->name('index');
        Route::post('/', [MembershipPaymentController::class, 'store'])->name('store');
        Route::get('/{id}/receipt', [MembershipPaymentController::class, 'receiptData'])->name('receipt');
    });
    
    // Member search API
    Route::get('/api/members/search', [MemberApiController::class, 'search']);
    Route::get('/api/members/{id}', [MemberApiController::class, 'show']);
    
    // Payment History Routes
    Route::get('/payments/history', [PaymentHistoryController::class, 'index'])->name('payments.history');

    // Product Payment Routes (managed from Payment History page)
    Route::get('/payments/{id}/receipt-data', [PaymentHistoryController::class, 'getReceiptData'])->name('payments.receipt-data');
    Route::post('/payments/{id}/refund', [PaymentHistoryController::class, 'refundProduct'])->name('payments.refund');
    Route::delete('/payments/{id}', [PaymentHistoryController::class, 'destroy'])->name('payments.destroy');

    // Membership Payment History Routes
    Route::delete('/membership-payment/bulk-delete', [PaymentHistoryController::class, 'bulkDeleteMembership'])->name('membership.payment.bulkDelete');
    Route::post('/membership-payment/{id}/refund', [PaymentHistoryController::class, 'refundMembership'])->name('membership.payment.refund');
    Route::delete('/membership-payment/{id}', [PaymentHistoryController::class, 'destroyMembership'])->name('membership.payment.destroy');
    Route::get('/membership-payment/{id}/receipt', [PaymentHistoryController::class, 'getMembershipReceipt'])->name('membership.payment.receipt');

    // // Optional: Dedicated Refund Management Routes (if you want a separate refund dashboard)
    // Route::prefix('refunds')->name('refunds.')->group(function () {
    //     Route::get('/', [RefundController::class, 'index'])->name('index');
    //     Route::get('/statistics', [RefundController::class, 'getStatistics'])->name('statistics');
    //     Route::get('/export', [RefundController::class, 'export'])->name('export');
    //     Route::get('/{id}/details', [RefundController::class, 'getRefundDetails'])->name('details');
    //     Route::post('/{id}/cancel', [RefundController::class, 'cancelRefund'])->name('cancel');
    // });

    // Reports & Analytics Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/kpis', [ReportController::class, 'getKPIs'])->name('kpis');
        Route::get('/revenue-over-time', [ReportController::class, 'getRevenueOverTime'])->name('revenue-over-time');
        Route::get('/top-selling', [ReportController::class, 'getTopSellingProducts'])->name('top-selling');
        Route::get('/revenue-breakdown', [ReportController::class, 'getRevenueBreakdown'])->name('revenue-breakdown');
        Route::get('/transaction-history', [ReportController::class, 'getTransactionHistory'])->name('transaction-history');
        Route::get('/attendance-trend', [ReportController::class, 'getCustomerAttendance'])->name('attendance-trend');
        Route::post('/export', [ReportController::class, 'exportReport'])->name('export');
    });
    
    // Legacy route (redirect to new reports route)
    Route::get('/ReportAndBilling', [ReportController::class, 'index'])->name('ReportAndBilling');

    // User and Admin
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
    Route::get('memberships/kpis', [MembershipController::class, 'getKpis'])->name('memberships.kpis');
    Route::delete('memberships/bulk-delete', [MembershipController::class, 'bulkDelete'])->name('memberships.bulk-delete');
    Route::post('memberships/{membership}/renew', [MembershipController::class, 'renew'])->name('memberships.renew');
    Route::resource('memberships', MembershipController::class);
    Route::get('/members/search', [MembershipController::class, 'search'])->name('members.search');

    // Clients CRUD
    Route::get('clients/kpis', [ClientController::class, 'getKpis'])->name('clients.kpis');
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