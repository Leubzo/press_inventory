<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    // Show all books
    public function index()
    {
        $books = Book::all();
        return view('books.index', compact('books'));
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


}
