<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\Book;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs with filtering options
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('book')->orderBy('created_at', 'desc');
        
        // Filter by book if specified
        if ($request->filled('book_id')) {
            $query->where('record_id', $request->book_id);
        }
        
        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
        
        // Get paginated results
        $auditLogs = $query->paginate(20)->withQueryString();
        
        // Get all books for the filter dropdown
        $books = Book::orderBy('title')->get();
        
        return view('audit-logs.index', compact('auditLogs', 'books'));
    }
    
    /**
     * Show audit logs for a specific book
     */
    public function showBookLogs($bookId)
    {
        $book = Book::findOrFail($bookId);
        
        $auditLogs = AuditLog::where('table_name', 'books')
            ->where('record_id', $bookId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $books = Book::orderBy('title')->get();
        
        return view('audit-logs.index', compact('auditLogs', 'books', 'book'));
    }
}