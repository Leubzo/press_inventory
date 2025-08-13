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
        return view('books.create');
    }

    // Save new book to database
    public function store(Request $request)
    {
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

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Book added successfully.');
    }

    // Handle CSV import
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file, 'r');
        $headerSkipped = false;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (!$headerSkipped) {
                // Skip the first 3 header rows
                $headerSkipped = true;
                fgetcsv($handle); // skip second row
                fgetcsv($handle); // skip third row
                continue;
            }

            // Trim all data to avoid unwanted spaces
            $isbn = isset($data[0]) ? trim($data[0]) : null;
            $title = isset($data[1]) ? trim($data[1]) : null;
            $authors_editors = isset($data[2]) ? trim($data[2]) : null;
            $year = isset($data[3]) && $data[3] !== '' ? (int) $data[3] : null;
            $pages = isset($data[4]) && $data[4] !== '' ? (int) $data[4] : null;
            $price = isset($data[5]) && $data[5] !== '' ? (float) $data[5] : null;
            $category = isset($data[6]) ? trim($data[6]) : null;
            $other_category = isset($data[7]) ? trim($data[7]) : null;
            $stock = isset($data[8]) && $data[8] !== '' ? (int) $data[8] : null;

            // Check if book with the same ISBN already exists
            $existingBook = Book::where('isbn', $isbn)->first();

            if ($existingBook) {
                $existingBook->update([
                    'title' => $title,
                    'authors_editors' => $authors_editors,
                    'year' => $year,
                    'pages' => $pages,
                    'price' => $price,
                    'category' => $category,
                    'other_category' => $other_category,
                    'stock' => $stock,
                ]);
                continue;
            }

            // Create a new book
            Book::create([
                'isbn' => $isbn,
                'title' => $title,
                'authors_editors' => $authors_editors,
                'year' => $year,
                'pages' => $pages,
                'price' => $price,
                'category' => $category,
                'other_category' => $other_category,
                'stock' => $stock,
            ]);
        }

        fclose($handle);
        return redirect()->route('books.index')->with('success', 'Books imported successfully.');
    }

    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    // Update the Book after editing
    public function update(Request $request, Book $book)
    {
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

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    public function updateStock(Request $request, Book $book)
    {
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
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }

    public function reset()
    {
        DB::table('books')->truncate(); // Truncates all data
        return redirect()->route('books.index')->with('success', 'All books have been deleted. Table has been reset.');
    }
}