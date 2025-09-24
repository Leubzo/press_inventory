<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    // Show all books
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $sortField = $request->input('sort', 'year'); 
            $sortDirection = $request->input('direction', 'desc');
            
            // Fix empty string issue
            if (empty($sortField) || $sortField === '') {
                $sortField = 'year';
            }
            if (empty($sortDirection) || $sortDirection === '' || !in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $books = Book::query();

            if ($search) {
                $books = $books->where('isbn', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('authors_editors', 'like', "%{$search}%");
            }

            $books = $books->orderBy($sortField, $sortDirection)
                ->paginate(20) // show 20 books per page
                ->appends($request->except('page')); // keep filters/sort in pagination links

            // Handle AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                try {
                    Log::info('AJAX request detected for books search', ['search' => $search]);
                    
                    $html = view('books.partials.book-table', [
                        'books' => $books,
                        'sortField' => $sortField,
                        'sortDirection' => $sortDirection
                    ])->render();
                    
                    Log::info('AJAX view rendered successfully');
                    return response($html);
                    
                } catch (\Exception $e) {
                    Log::error('AJAX Error in book table partial: ' . $e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'error' => 'Unable to load search results', 
                        'message' => $e->getMessage(),
                        'line' => $e->getLine()
                    ], 500);
                }
            }

            return view('books.index', compact('books', 'search', 'sortField', 'sortDirection'));

        } catch (\Exception $e) {
            Log::error('Error in BookController@index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'search' => $search ?? 'none'
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Search error occurred', 
                    'message' => $e->getMessage(),
                    'line' => $e->getLine()
                ], 500);
            }
            
            return back()->with('error', 'An error occurred while loading books.');
        }
    }

    // Show form to create a book
    public function create()
    {
        if (!auth()->user()->canManageInventory()) {
            abort(403, 'Only storekeepers and administrators can add books.');
        }

        return view('books.create');
    }

    // Save new book to database
    public function store(Request $request)
    {
        if (!auth()->user()->canManageInventory()) {
            abort(403, 'Only storekeepers and administrators can add books.');
        }
        $validated = $request->validate([
            'isbn' => 'required|unique:books',
            'title' => 'required',
            'authors_editors' => 'required',
            'year' => 'nullable|integer',
            'pages' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string',
            'other_category' => 'nullable|string',
            'stock' => 'nullable|integer',
        ]);

        // Convert empty strings to null for consistency
        if (empty($validated['category'])) {
            $validated['category'] = null;
        }
        if (empty($validated['other_category'])) {
            $validated['other_category'] = null;
        }

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Book added successfully.');
    }



    // Update the Book after editing
    public function update(Request $request, Book $book)
    {
        if (!auth()->user()->canManageInventory()) {
            abort(403, 'Only storekeepers and administrators can edit books.');
        }
        $validated = $request->validate([
            'isbn' => 'required|unique:books,isbn,' . $book->id,
            'title' => 'required',
            'authors_editors' => 'required',
            'year' => 'nullable|integer',
            'pages' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string',
            'other_category' => 'nullable|string',
            'stock' => 'nullable|integer',
        ]);

        // Convert empty strings to null for consistency
        if (empty($validated['category'])) {
            $validated['category'] = null;
        }
        if (empty($validated['other_category'])) {
            $validated['other_category'] = null;
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    public function updateStock(Request $request, Book $book)
    {
        if (!auth()->user()->canManageInventory()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Only storekeepers and administrators can update stock.'], 403);
            }
            abort(403, 'Only storekeepers and administrators can update stock.');
        }
        $validated = $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        $book->update(['stock' => $validated['stock']]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Stock updated successfully']);
        }

        return redirect()->route('books.index')->with('success', 'Stock updated successfully.');
    }

    // Delete a book
    public function destroy(Book $book)
    {
        if (!auth()->user()->canManageInventory()) {
            abort(403, 'Only storekeepers and administrators can delete books.');
        }
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }


    // Show the mobile barcode scanning page
    public function scan()
    {
        return view('books.scan');
    }

    // Search for book by ISBN for scanning
    public function scanSearch(Request $request)
    {
        $isbn = $request->input('isbn');
        
        if (!$isbn) {
            return response()->json(['error' => 'ISBN is required'], 400);
        }

        $book = Book::where('isbn', $isbn)->first();

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'isbn' => $book->isbn,
                'title' => $book->title,
                'authors_editors' => $book->authors_editors,
                'current_stock' => $book->stock ?? 0,
                'price' => $book->price ?? 0,
                'category' => $book->category_display,
            ]
        ]);
    }
}