<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $platform = $request->input('platform');
        $sortField = $request->input('sort', 'sale_date'); 
        $sortDirection = $request->input('direction', 'desc');
        
        // Fix empty string issue
        if (empty($sortField) || $sortField === '') {
            $sortField = 'sale_date';
        }
        if (empty($sortDirection) || $sortDirection === '' || !in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $sales = Sale::with('book')
            ->when($search, function ($query, $search) {
                return $query->whereHas('book', function ($q) use ($search) {
                    $q->where('isbn', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                })->orWhere('order_number', 'like', "%{$search}%");
            })
            ->when($platform, function ($query, $platform) {
                return $query->where('platform', $platform);
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(20)
            ->appends($request->except('page'));

        // Get summary statistics
        $summaryStats = [
            'total_sales' => Sale::count(),
            'total_revenue' => Sale::sum('total_price'),
            'total_quantity' => Sale::sum('quantity'),
            'platforms' => Sale::distinct('platform')->pluck('platform'),
            'recent_sales' => Sale::with('book')->latest()->take(5)->get()
        ];

        // Get books that need stock updates (sales > current stock)
        $booksNeedingUpdate = collect();
        $booksWithSales = Book::whereHas('sales')->with('sales')->get();
        
        foreach ($booksWithSales as $book) {
            $totalSold = $book->sales->sum('quantity');
            if ($totalSold > $book->stock) {
                $book->total_sold = $totalSold;
                $booksNeedingUpdate->push($book);
            }
        }

        // Handle AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            try {
                Log::info('AJAX request detected for sales search', ['search' => $search, 'platform' => $platform]);
                
                $html = view('sales.partials.sales-table', [
                    'sales' => $sales,
                    'sortField' => $sortField,
                    'sortDirection' => $sortDirection
                ])->render();
                
                Log::info('AJAX view rendered successfully');
                return response($html);
            } catch (\Exception $e) {
                Log::error('AJAX request failed', ['error' => $e->getMessage()]);
                return response('Error loading sales data', 500);
            }
        }

        return view('sales.index', compact('sales', 'summaryStats', 'booksNeedingUpdate', 'sortField', 'sortDirection'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'platform' => 'required|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'buyer_info' => 'nullable|string',
            'sale_date' => 'required|date'
        ]);

        try {
            $sale = Sale::create([
                'book_id' => $request->book_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'platform' => $request->platform,
                'order_number' => $request->order_number,
                'buyer_info' => $request->buyer_info,
                'sale_date' => $request->sale_date
            ]);

            // Check if this sale creates a stock discrepancy
            $book = Book::find($request->book_id);
            $totalSold = $book->sales()->sum('quantity');
            
            $message = 'Sale recorded successfully!';
            if ($totalSold > $book->stock) {
                $message .= ' ⚠️ Warning: Total sales (' . $totalSold . ') exceed current stock (' . $book->stock . '). Stock update needed.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sale' => $sale->load('book')
                ]);
            }

            return redirect()->route('sales.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to create sale', ['error' => $e->getMessage()]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to record sale: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', 'Failed to record sale: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        return response()->json($sale->load('book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'platform' => 'required|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'buyer_info' => 'nullable|string',
            'sale_date' => 'required|date'
        ]);

        try {
            $sale->update([
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'platform' => $request->platform,
                'order_number' => $request->order_number,
                'buyer_info' => $request->buyer_info,
                'sale_date' => $request->sale_date
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale updated successfully!',
                    'sale' => $sale->load('book')
                ]);
            }

            return redirect()->route('sales.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update sale', ['error' => $e->getMessage()]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update sale: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', 'Failed to update sale: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        try {
            $sale->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete sale', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sale: ' . $e->getMessage()
            ], 400);
        }
    }

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
            ->with('sales')
            ->take(10)
            ->get()
            ->map(function ($book) {
                $totalSold = $book->sales->sum('quantity');
                return [
                    'id' => $book->id,
                    'isbn' => $book->isbn,
                    'title' => $book->title,
                    'authors_editors' => $book->authors_editors,
                    'price' => $book->price,
                    'stock' => $book->stock,
                    'total_sold' => $totalSold,
                    'needs_update' => $totalSold > $book->stock
                ];
            });

        return response()->json($books);
    }
}
