<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InventorySupplyController;

//Inventory Supply Routes
Route::get('/inventory', [InventorySupplyController::class, 'index'])->name('inventory.index');
Route::get('/inventory/create', [InventorySupplyController::class, 'create'])->name('inventory.create');
Route::post('/inventory', [InventorySupplyController::class, 'store'])->name('inventory.store');
Route::get('/inventory/{id}/edit', [InventorySupplyController::class, 'edit'])->name('inventory.edit');
Route::put('/inventory/{id}', [InventorySupplyController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{id}', [InventorySupplyController::class, 'destroy'])->name('inventory.destroy');

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

    Route::get('/PaymentAndBilling', function () {
    return view('PaymentAndBillings.PaymentAndBilling');
    })->name('PaymentAndBilling');

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