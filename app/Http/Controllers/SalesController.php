<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class SalesController extends Controller
{

    /**
     * Search for books (AJAX)
     */
    public function searchBooks(Request $request)
    {
        $search = $request->input('search', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $books = Book::where('isbn', 'like', "%{$search}%")
            ->orWhere('title', 'like', "%{$search}%")
            ->orWhere('authors_editors', 'like', "%{$search}%")
            ->take(10)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'isbn' => $book->isbn,
                    'title' => $book->title,
                    'authors_editors' => $book->authors_editors,
                    'price' => $book->price,
                    'selling_price' => $book->price, // Add selling_price alias for compatibility
                    'stock' => $book->stock
                ];
            });

        return response()->json($books);
    }
}
