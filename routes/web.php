<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Auth;

// Home/Login route - welcome page or redirect to books if authenticated
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('books.index');
    }
    return view('welcome');
})->name('login'); // Using 'login' name for authentication compatibility

// Protected routes (require login)
Route::middleware(['auth'])->group(function () {

    // Redirect after login to books index
    Route::get('/dashboard', function () {
        return redirect()->route('books.index');
    })->name('dashboard');

    // All book management routes (protected) - excluding edit since we use modal
    Route::resource('books', BookController::class)->except(['edit']);
    Route::post('/books/import', [BookController::class, 'import'])->name('books.import');
    Route::post('/books/reset', [BookController::class, 'reset'])->name('books.reset');
    Route::patch('/books/{book}/stock', [BookController::class, 'updateStock'])->name('books.updateStock');
    
    // Audit logs route
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    
    // Reports routes
    Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('reports.export');
    
    // Sales routes (specific routes first, then resource routes)
    Route::get('/sales/search-books', [App\Http\Controllers\SalesController::class, 'searchBooks'])->name('sales.search-books');
    Route::resource('sales', App\Http\Controllers\SalesController::class)->except(['create', 'edit']);
});

// Authentication routes (provided by Breeze)
require __DIR__ . '/auth.php';
