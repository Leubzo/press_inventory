<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\AuditLog;
use Carbon\Carbon;
use DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $reports = $this->generateReports($request);
        
        return view('reports.index', compact('reports'));
    }
    
    private function generateReports(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());
        $category = $request->get('category');
        
        $books = Book::query();
        
        if ($category) {
            $books->where('category', $category);
        }
        
        $booksData = $books->get();
        
        $auditLogs = AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        
        if ($category) {
            $auditLogs->whereHas('book', function($query) use ($category) {
                $query->where('category', $category);
            });
        }
        
        $auditData = $auditLogs->get();
        
        return [
            'summary' => $this->getSummaryStats($booksData),
            'inventory' => $this->getInventoryStats($booksData),
            'activity' => $this->getActivityStats($auditData, $dateFrom, $dateTo),
            'categories' => $this->getCategoryStats($booksData),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'category' => $category,
                'available_categories' => Book::distinct('category')->whereNotNull('category')->pluck('category')->sort()
            ]
        ];
    }
    
    private function getSummaryStats($books)
    {
        return [
            'total_books' => $books->count(),
            'total_stock' => $books->sum('stock'),
            'total_value' => $books->sum(function($book) {
                return $book->price * $book->stock;
            }),
            'unique_categories' => $books->pluck('category')->filter()->unique()->count(),
            'out_of_stock' => $books->where('stock', 0)->count(),
            'low_stock' => $books->where('stock', '>', 0)->where('stock', '<=', 10)->count()
        ];
    }
    
    private function getInventoryStats($books)
    {
        return [
            'stock_distribution' => [
                'out_of_stock' => $books->where('stock', 0)->count(),
                'low_stock' => $books->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
                'medium_stock' => $books->where('stock', '>', 10)->where('stock', '<=', 50)->count(),
                'high_stock' => $books->where('stock', '>', 50)->count()
            ],
            'value_by_category' => $books->groupBy('category')->map(function($categoryBooks) {
                return [
                    'books_count' => $categoryBooks->count(),
                    'total_stock' => $categoryBooks->sum('stock'),
                    'total_value' => $categoryBooks->sum(function($book) {
                        return $book->price * $book->stock;
                    })
                ];
            }),
            'top_value_books' => $books->sortByDesc(function($book) {
                return $book->price * $book->stock;
            })->take(10),
            'recent_additions' => $books->sortByDesc('created_at')->take(5)
        ];
    }
    
    private function getActivityStats($auditLogs, $dateFrom, $dateTo)
    {
        $activityByDate = $auditLogs->groupBy(function($log) {
            return $log->created_at->toDateString();
        })->map(function($dayLogs) {
            return [
                'total' => $dayLogs->count(),
                'created' => $dayLogs->where('action', 'created')->count(),
                'updated' => $dayLogs->where('action', 'updated')->count(),
                'deleted' => $dayLogs->where('action', 'deleted')->count()
            ];
        });
        
        $activityByAction = $auditLogs->groupBy('action')->map->count();
        
        return [
            'total_activities' => $auditLogs->count(),
            'activity_by_date' => $activityByDate,
            'activity_by_action' => $activityByAction,
            'most_active_days' => $activityByDate->sortByDesc('total')->take(5),
            'recent_activities' => $auditLogs->sortByDesc('created_at')->take(10)
        ];
    }
    
    private function getCategoryStats($books)
    {
        return $books->groupBy('category')->map(function($categoryBooks, $category) {
            return [
                'name' => $category ?: 'Uncategorized',
                'books_count' => $categoryBooks->count(),
                'total_stock' => $categoryBooks->sum('stock'),
                'avg_price' => $categoryBooks->avg('price'),
                'total_value' => $categoryBooks->sum(function($book) {
                    return $book->price * $book->stock;
                }),
                'out_of_stock_count' => $categoryBooks->where('stock', 0)->count()
            ];
        })->sortByDesc('total_value');
    }
    
    public function export(Request $request)
    {
        $reports = $this->generateReports($request);
        $format = $request->get('format', 'csv');
        
        if ($format === 'csv') {
            return $this->exportCsv($reports);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($reports);
        }
        
        return redirect()->back()->with('error', 'Invalid export format');
    }
    
    private function exportCsv($reports)
    {
        $filename = 'inventory_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->stream(function() use ($reports) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['Inventory Summary Report']);
            fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            fputcsv($file, ['Summary Statistics']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Books', $reports['summary']['total_books']]);
            fputcsv($file, ['Total Stock', $reports['summary']['total_stock']]);
            fputcsv($file, ['Total Value (RM)', number_format($reports['summary']['total_value'], 2)]);
            fputcsv($file, ['Unique Categories', $reports['summary']['unique_categories']]);
            fputcsv($file, ['Out of Stock', $reports['summary']['out_of_stock']]);
            fputcsv($file, ['Low Stock', $reports['summary']['low_stock']]);
            fputcsv($file, []);
            
            fputcsv($file, ['Category Analysis']);
            fputcsv($file, ['Category', 'Books Count', 'Total Stock', 'Average Price', 'Total Value', 'Out of Stock']);
            foreach ($reports['categories'] as $category) {
                fputcsv($file, [
                    $category['name'],
                    $category['books_count'],
                    $category['total_stock'],
                    number_format($category['avg_price'], 2),
                    number_format($category['total_value'], 2),
                    $category['out_of_stock_count']
                ]);
            }
            
            fclose($file);
        }, 200, $headers);
    }
    
    private function exportPdf($reports)
    {
        // This would require a PDF library like DomPDF
        // For now, return an error message
        return redirect()->back()->with('error', 'PDF export not implemented yet');
    }
}