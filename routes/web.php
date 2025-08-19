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

    // Barcode scanning routes (must be before resource routes)
    Route::get('/books/scan', [BookController::class, 'scan'])->name('books.scan');
    Route::get('/books/scan-search', [BookController::class, 'scanSearch'])->name('books.scan.search');
    
    // All book management routes (protected) - excluding edit since we use modal
    Route::resource('books', BookController::class)->except(['edit']);
    Route::post('/books/import', [BookController::class, 'import'])->name('books.import');
    Route::patch('/books/{book}/stock', [BookController::class, 'updateStock'])->name('books.updateStock');
    
    // Audit logs route
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    
    // Reports routes (separate pages for each report type)
    Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventory', [App\Http\Controllers\ReportsController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/sales', [App\Http\Controllers\ReportsController::class, 'sales'])->name('reports.sales');
    
    // Enhanced reports API routes
    Route::get('/reports/autocomplete', [App\Http\Controllers\ReportsController::class, 'autocomplete'])->name('reports.autocomplete');
    Route::get('/reports/book-details/{id}', [App\Http\Controllers\ReportsController::class, 'bookDetails'])->name('reports.book-details');
    Route::get('/reports/trends', [App\Http\Controllers\ReportsController::class, 'trendsData'])->name('reports.trends');
    
    // Export routes with filter support
    Route::get('/reports/export/inventory', [App\Http\Controllers\ReportsController::class, 'exportInventory'])->name('reports.export.inventory');
    Route::get('/reports/export/sales', [App\Http\Controllers\ReportsController::class, 'exportSales'])->name('reports.export.sales');
    
    // Book search route (for order creation)
    Route::get('/sales/search-books', [App\Http\Controllers\SalesController::class, 'searchBooks'])->name('sales.search-books');
    
    // Order management pages (separate routes for each tab)
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.index');
    Route::get('/orders/create', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.create');
    Route::get('/orders/pending', [App\Http\Controllers\OrderController::class, 'pending'])->name('orders.pending');
    Route::get('/orders/fulfillment', [App\Http\Controllers\OrderController::class, 'fulfillment'])->name('orders.fulfillment');
    Route::get('/orders/history', [App\Http\Controllers\OrderController::class, 'history'])->name('orders.history');
    
    // Order workflow routes
    Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/approve', [App\Http\Controllers\OrderController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/fulfill', [App\Http\Controllers\OrderController::class, 'fulfill'])->name('orders.fulfill');
    Route::get('/orders/status/{status}', [App\Http\Controllers\OrderController::class, 'getByStatus'])->name('orders.by-status');
    Route::get('/orders-pending-counts', [App\Http\Controllers\OrderController::class, 'getPendingCounts'])->name('orders.pending-counts');
});

// Authentication routes (provided by Breeze)
require __DIR__ . '/auth.php';
