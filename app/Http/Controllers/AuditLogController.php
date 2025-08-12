<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\Book;

class AuditLogController extends Controller
{
    // Show all audit logs
    public function index(Request $request)
    {
        $query = AuditLog::orderBy('created_at', 'desc');
        
        // Filter by book if specified
        if ($request->book_id) {
            $query->where('record_id', $request->book_id);
        }
        
        $auditLogs = $query->with('book')->paginate(20);
        $books = Book::orderBy('title')->get();
        
        return view('audit-logs.index', compact('auditLogs', 'books'));
    }
}