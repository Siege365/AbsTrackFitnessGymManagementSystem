<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventorySupplyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MembershipPaymentController;
use App\Http\Controllers\GymConfigurationController;
use App\Http\Controllers\API\MemberApiController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\PTpaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ActivityLogController;



// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Dashboard (Protected)
Route::get('/', [DashboardController::class, 'index'])
    ->middleware('auth')->name('dashboard');

// Protected Routes (Require Authentication)
Route::middleware(['auth'])->group(function () {

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Account Settings
    Route::get('/account/settings', [AccountSettingsController::class, 'show'])->name('account.settings');
    Route::put('/account/profile', [AccountSettingsController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountSettingsController::class, 'updatePassword'])->name('account.password.update');
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

    Route::get('/sessions/training-sessions', [SessionController::class, 'ptIndex'])->name('sessions.pt.index');
    Route::get('/sessions/customer-attendance', [SessionController::class, 'attendanceIndex'])->name('sessions.attendance.index');
    
    // Session Routes - PT Schedules & Attendance
    Route::prefix('sessions')->name('sessions.')->group(function () {

        // KPI refresh route
        Route::get('/kpis', [SessionController::class, 'getKPIs'])->name('kpis');
        
        // PT Schedule bulk delete (must be before {id} routes to avoid wildcard conflict)
        Route::delete('/pt-schedule/bulk-delete', [SessionController::class, 'bulkDeletePT'])->name('pt.bulk-delete');
        
        // PT Schedule routes
        Route::post('/pt-schedule', [SessionController::class, 'storePTSchedule'])->name('pt.store');
        Route::post('/pt-schedule/book-next', [SessionController::class, 'bookNextSession'])->name('pt.book-next');
        Route::get('/pt-schedule/{id}', [SessionController::class, 'getPTSchedule'])->name('pt.show');
        Route::put('/pt-schedule/{id}', [SessionController::class, 'updatePTSchedule'])->name('pt.update');
        Route::delete('/pt-schedule/{id}', [SessionController::class, 'destroyPTSchedule'])->name('pt.destroy');
        Route::patch('/pt-schedule/{id}/status', [SessionController::class, 'updatePTStatus'])->name('pt.status');
        
        // Attendance bulk delete (must be before {id} routes to avoid wildcard conflict)
        Route::delete('/attendance/bulk-delete', [SessionController::class, 'bulkDeleteAttendance'])->name('attendance.bulk-delete');
        
        // Attendance routes
        Route::post('/attendance', [SessionController::class, 'storeAttendance'])->name('attendance.store');
        Route::get('/attendance/{id}', [SessionController::class, 'getAttendance'])->name('attendance.show');
        Route::put('/attendance/{id}', [SessionController::class, 'updateAttendance'])->name('attendance.update');
        Route::delete('/attendance/{id}', [SessionController::class, 'destroyAttendance'])->name('attendance.destroy');

        // Customer search (combined clients + memberships)
        Route::get('/customers/search', [SessionController::class, 'searchCustomers'])->name('customers.search');
        
        // Trainer search for autocomplete
        Route::get('/trainers/search', [SessionController::class, 'searchTrainers'])->name('trainers.search');
    });
    
    //Inventory Supply Routes
    Route::get('/inventory/inventory-logs', [InventorySupplyController::class, 'logsIndex'])->name('inventory.logs');
    Route::prefix('inventory/products')->name('inventory.')->group(function () {
        Route::get('/', [InventorySupplyController::class, 'index'])->name('index');
        Route::post('/', [InventorySupplyController::class, 'store'])->name('store');
        Route::get('/next-product-number', [InventorySupplyController::class, 'getNextProductNumber'])->name('next-product-number');
        Route::put('/{id}', [InventorySupplyController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventorySupplyController::class, 'destroy'])->name('destroy');
        Route::delete('/', [InventorySupplyController::class, 'bulkDelete'])->name('bulk-delete');

        // Category check route
        Route::get('/check-category', [InventorySupplyController::class, 'checkCategory'])->name('check-category');

        // Stock transaction routes
        Route::post('/{id}/stock-transaction', [InventorySupplyController::class, 'stockTransaction'])->name('stock-transaction');
        Route::get('/{id}/transaction-history', [InventorySupplyController::class, 'transactionHistory'])->name('transaction-history');
        Route::get('/{id}/stock-history-json', [InventorySupplyController::class, 'stockHistoryJson'])->name('stock-history-json');
    });
        // Payment Routes (consolidated)
    // Product Payment Routes
    Route::get('/payments/products', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/products', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/products/bulk-delete', [PaymentController::class, 'bulkDelete'])->name('payments.bulkDelete');
    
    // ==========================================
    // UNIFIED PAYMENT SYSTEM (SPA-STYLE)
    // ==========================================
    Route::prefix('payments-billing/payment-system')->name('payment.system.')->group(function () {
        Route::get('/membership-payment', [PaymentController::class, 'membership'])->defaults('paymentType', 'membership')->name('membership');
        Route::get('/pt-payment', [PaymentController::class, 'membership'])->defaults('paymentType', 'pt')->name('pt');
        Route::get('/product-payment', [PaymentController::class, 'membership'])->defaults('paymentType', 'product')->name('product');
        
        // Default fallback - redirect to membership
        Route::get('/', function() {
            return redirect()->route('payment.system.membership');
        })->name('index');
    });
    
    // Legacy aliases for backward compatibility
    Route::get('/payments/membership', function() {
        return redirect()->route('payment.system.membership');
    })->name('payments.membership');
    
    // Payment History & Transaction Management Routes
    Route::get('/payments-billing/payment-history', [PaymentHistoryController::class, 'index'])->name('payments.history');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/{id}/receipt-data', [PaymentHistoryController::class, 'getReceiptData'])->name('payments.receipt-data');
    Route::post('/payments/{id}/refund', [PaymentHistoryController::class, 'refundProduct'])->name('payments.refund');
    Route::delete('/payments/{id}', [PaymentHistoryController::class, 'destroy'])->name('payments.destroy');
    
    // ==========================================
    // MEMBERSHIP PAYMENT ROUTES (UPDATED)
    Route::prefix('membership-payment')->name('membership.payment.')->group(function () {
        Route::get('/', [MembershipPaymentController::class, 'index'])->name('index');
        Route::post('/', [MembershipPaymentController::class, 'store'])->name('store');
        Route::get('/{id}/receipt', [MembershipPaymentController::class, 'receiptData'])->name('receipt');
    });

    // ==========================================
    // LEGACY SEPARATE PAYMENT PAGES (Deprecated - use /payments-billing/* instead)
    // ==========================================
    Route::get('/pt-payment', function() {
        return redirect()->route('payment.system.pt');
    })->name('pt.payment.index');

    Route::get('/product-payment', function() {
        return redirect()->route('payment.system.product');
    })->name('product.payment.index');
    
    // Member search API
    Route::get('/api/members/search', [MemberApiController::class, 'search']);
    Route::get('/api/members/check-duplicate', [MemberApiController::class, 'checkDuplicate']);
    Route::get('/api/members/{id}', [MemberApiController::class, 'show']);
    
    // Autocomplete API for cross-referencing
    Route::get('/api/customers/autocomplete', [CustomerController::class, 'autocomplete'])->name('api.customers.autocomplete');
    Route::get('/api/memberships/autocomplete', [MembershipController::class, 'autocomplete'])->name('api.memberships.autocomplete');
    Route::get('/api/clients/autocomplete', [ClientController::class, 'autocomplete'])->name('api.clients.autocomplete');
    
    // Membership Payment History Routes
    Route::delete('/membership-payment/bulk-delete', [PaymentHistoryController::class, 'bulkDeleteMembership'])->name('membership.payment.bulkDelete');
    Route::post('/membership-payment/{id}/refund', [PaymentHistoryController::class, 'refundMembership'])->name('membership.payment.refund');
    Route::delete('/membership-payment/{id}', [PaymentHistoryController::class, 'destroyMembership'])->name('membership.payment.destroy');

    // // Optional: Dedicated Refund Management Routes (if you want a separate refund dashboard)
    // Route::prefix('refunds')->name('refunds.')->group(function () {
    //     Route::get('/', [RefundController::class, 'index'])->name('index');
    //     Route::get('/statistics', [RefundController::class, 'getStatistics'])->name('statistics');
    //     Route::get('/export', [RefundController::class, 'export'])->name('export');
    //     Route::get('/{id}/details', [RefundController::class, 'getRefundDetails'])->name('details');
    //     Route::post('/{id}/cancel', [RefundController::class, 'cancelRefund'])->name('cancel');
    // });

    // Reports & Analytics Routes
    Route::prefix('reports-analytics')->name('reports.')->group(function () {
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
    Route::get('/ReportAndBilling', function() {
        return redirect()->route('reports.index');
    })->name('ReportAndBilling');

    // ==========================================
    // STAFF MANAGEMENT ROUTES
    // ==========================================
    Route::prefix('staff-management/staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::put('/{id}', [StaffController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('toggleStatus');
    });

    Route::prefix('staff-management/trainers')->name('trainers.')->group(function () {
        Route::get('/', [TrainerController::class, 'index'])->name('index');
        Route::post('/', [TrainerController::class, 'store'])->name('store');
        Route::put('/{id}', [TrainerController::class, 'update'])->name('update');
        Route::delete('/{id}', [TrainerController::class, 'destroy'])->name('destroy');
    });

    Route::get('/staff-management/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // ==========================================
    // GYM CONFIGURATION ROUTES
    // ==========================================
    Route::prefix('configuration')->name('configuration.')->group(function () {
        Route::get('/', [GymConfigurationController::class, 'index'])->name('index');
        Route::post('/plans', [GymConfigurationController::class, 'store'])->name('plans.store');
        Route::put('/plans/{id}', [GymConfigurationController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{id}', [GymConfigurationController::class, 'destroy'])->name('plans.destroy');
        Route::patch('/plans/toggle-status/{id}', [GymConfigurationController::class, 'toggleStatus'])->name('plans.toggleStatus');
        Route::post('/plans/reorder', [GymConfigurationController::class, 'reorder'])->name('plans.reorder');

        // Category Management
        Route::put('/categories/{name}', [GymConfigurationController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{name}', [GymConfigurationController::class, 'destroyCategory'])->name('categories.destroy');
    });

    // API: Active plans (for Payment page dynamic loading)
    Route::get('/api/gym-plans/active', [GymConfigurationController::class, 'activePlans'])->name('api.gym-plans.active');

    // ==========================================
    // NOTIFICATION BELL ROUTES
    // ==========================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/page', [NotificationController::class, 'page'])->name('page');
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Memberships CRUD (under /customers/memberships)
    Route::prefix('customers')->group(function () {
        Route::get('memberships/kpis', [MembershipController::class, 'getKpis'])->name('memberships.kpis');
        Route::delete('memberships/bulk-delete', [MembershipController::class, 'bulkDelete'])->name('memberships.bulk-delete');
        Route::post('memberships/{membership}/renew', [MembershipController::class, 'renew'])->name('memberships.renew');
        Route::resource('memberships', MembershipController::class);
        Route::get('/members/search', [MembershipController::class, 'search'])->name('members.search');

        // Clients CRUD (under /customers/clients)
        Route::get('clients/kpis', [ClientController::class, 'getKpis'])->name('clients.kpis');
        Route::delete('clients/bulk-delete', [ClientController::class, 'bulkDelete'])->name('clients.bulk-delete');
        Route::post('clients/{client}/renew', [ClientController::class, 'renew'])->name('clients.renew');
        Route::resource('clients', ClientController::class);
    });
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

