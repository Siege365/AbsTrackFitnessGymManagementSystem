<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

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
