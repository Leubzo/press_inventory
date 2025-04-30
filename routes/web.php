<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

// Home (optional)
Route::get('/', function () {
    return view('welcome');
});

// Resource routes for all CRUD actions
Route::resource('books', BookController::class);

// Custom actions
Route::post('/books/import', [BookController::class, 'import'])->name('books.import');
Route::post('/books/reset', [BookController::class, 'reset'])->name('books.reset');
Route::patch('/books/{book}/stock', [BookController::class, 'updateStock'])->name('books.updateStock');
