<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Auth;

// Smart home route - login page or redirect to books
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('books.index');
    }
    return view('welcome');
})->name('home');

// Protected routes (require login)
Route::middleware(['auth', 'verified'])->group(function () {

    // Redirect after login to books index
    Route::get('/dashboard', function () {
        return redirect()->route('books.index');
    })->name('dashboard');

    // All book management routes (protected)
    Route::resource('books', BookController::class);
    Route::post('/books/import', [BookController::class, 'import'])->name('books.import');
    Route::post('/books/reset', [BookController::class, 'reset'])->name('books.reset');
    Route::patch('/books/{book}/stock', [BookController::class, 'updateStock'])->name('books.updateStock');
});

// Authentication routes (provided by Breeze)
require __DIR__ . '/auth.php';
