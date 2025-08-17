<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Redirect to inventory as default
        return redirect()->route('reports.inventory');
    }
    
    public function inventory(Request $request)
    {
        $reports = $this->generateReports($request);
        
        return view('reports.inventory', compact('reports'));
    }
    
    public function sales(Request $request)
    {
        $reports = $this->generateReports($request);
        
        return view('reports.sales', compact('reports'));
    }
    
    private function generateReports(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now('Asia/Kuala_Lumpur')->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now('Asia/Kuala_Lumpur')->toDateString());
        $category = $request->get('category');
        
        $books = Book::query();
        
        if ($category) {
            $books->where('category', $category);
        }
        
        $booksData = $books->get();
        
        $auditLogs = AuditLog::whereBetween('created_at', [
            Carbon::parse($dateFrom, 'Asia/Kuala_Lumpur')->startOfDay(),
            Carbon::parse($dateTo, 'Asia/Kuala_Lumpur')->endOfDay()
        ]);
        
        if ($category) {
            $auditLogs->whereHas('book', function($query) use ($category) {
                $query->where('category', $category);
            });
        }
        
        $auditData = $auditLogs->get();
        
        // Get order-based sales data for the same period (fulfilled orders only)
        $platform = $request->get('platform');
        $ordersData = Order::with(['orderItems.book'])
            ->where('status', 'fulfilled')
            ->whereBetween('fulfillment_date', [
                Carbon::parse($dateFrom, 'Asia/Kuala_Lumpur')->startOfDay(),
                Carbon::parse($dateTo, 'Asia/Kuala_Lumpur')->endOfDay()
            ]);
            
        if ($platform) {
            $ordersData->where('platform', $platform);
        }
            
        if ($category) {
            $ordersData->whereHas('orderItems.book', function($query) use ($category) {
                $query->where('category', $category);
            });
        }
        
        $ordersData = $ordersData->get();

        return [
            'summary' => $this->getSummaryStats($booksData),
            'inventory' => $this->getInventoryStats($booksData),
            'activity' => $this->getActivityStats($auditData, $dateFrom, $dateTo),
            'categories' => $this->getCategoryStats($booksData),
            'sales' => $this->getOrderBasedSalesStats($ordersData, $dateFrom, $dateTo),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'category' => $category,
                'platform' => $platform,
                'available_categories' => Book::distinct('category')->whereNotNull('category')->pluck('category')->sort(),
                'available_platforms' => Order::distinct('platform')->whereNotNull('platform')->pluck('platform')->sort()
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
            return $log->created_at->setTimezone('Asia/Kuala_Lumpur')->toDateString();
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
    
    private function getOrderBasedSalesStats($orders, $dateFrom, $dateTo)
    {
        if ($orders->isEmpty()) {
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_quantity' => 0,
                'avg_order_value' => 0,
                'unique_books_sold' => 0,
                'platforms_used' => 0,
                'sales_by_date' => collect(),
                'sales_by_platform' => collect(),
                'top_selling_books' => collect(),
                'recent_orders' => collect(),
                'avg_books_per_order' => 0,
                'fulfillment_rate' => 0
            ];
        }

        // Calculate totals
        $totalRevenue = 0;
        $totalQuantity = 0;
        $allBookIds = collect();
        
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalRevenue += $item->quantity_fulfilled * $item->unit_price;
                $totalQuantity += $item->quantity_fulfilled;
                $allBookIds->push($item->book_id);
            }
            $order->total_value = $order->orderItems->sum(function($item) {
                return $item->quantity_fulfilled * $item->unit_price;
            });
        }

        // Sales by platform
        $salesByPlatform = $orders->groupBy('platform')->map(function($platformOrders, $platform) {
            $totalRevenue = 0;
            $totalQuantity = 0;
            
            foreach ($platformOrders as $order) {
                foreach ($order->orderItems as $item) {
                    $totalRevenue += $item->quantity_fulfilled * $item->unit_price;
                    $totalQuantity += $item->quantity_fulfilled;
                }
            }
            
            return [
                'platform' => $platform,
                'total_orders' => $platformOrders->count(),
                'total_revenue' => $totalRevenue,
                'total_quantity' => $totalQuantity,
                'avg_order_value' => $platformOrders->count() > 0 ? $totalRevenue / $platformOrders->count() : 0,
                'market_share' => $totalRevenue > 0 ? ($totalRevenue / $totalRevenue) * 100 : 0
            ];
        })->sortByDesc('total_revenue');

        // Calculate market share properly
        if ($totalRevenue > 0) {
            $salesByPlatform = $salesByPlatform->map(function($platformData) use ($totalRevenue) {
                $platformData['market_share'] = ($platformData['total_revenue'] / $totalRevenue) * 100;
                return $platformData;
            });
        }

        // Top selling books
        $bookSales = [];
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $bookId = $item->book_id;
                if (!isset($bookSales[$bookId])) {
                    $bookSales[$bookId] = [
                        'book' => $item->book,
                        'total_quantity' => 0,
                        'total_revenue' => 0,
                        'orders_count' => 0,
                        'total_unit_price' => 0,
                        'price_count' => 0
                    ];
                }
                
                $bookSales[$bookId]['total_quantity'] += $item->quantity_fulfilled;
                $bookSales[$bookId]['total_revenue'] += $item->quantity_fulfilled * $item->unit_price;
                $bookSales[$bookId]['orders_count']++;
                $bookSales[$bookId]['total_unit_price'] += $item->unit_price;
                $bookSales[$bookId]['price_count']++;
            }
        }

        $topSellingBooks = collect($bookSales)->map(function($bookData) {
            $bookData['avg_sale_price'] = $bookData['price_count'] > 0 ? $bookData['total_unit_price'] / $bookData['price_count'] : 0;
            unset($bookData['total_unit_price'], $bookData['price_count']);
            return $bookData;
        })->sortByDesc('total_revenue')->take(10);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $totalRevenue,
            'total_quantity' => $totalQuantity,
            'avg_order_value' => $orders->count() > 0 ? $totalRevenue / $orders->count() : 0,
            'unique_books_sold' => $allBookIds->unique()->count(),
            'platforms_used' => $orders->pluck('platform')->unique()->count(),
            'sales_by_platform' => $salesByPlatform,
            'top_selling_books' => $topSellingBooks,
            'recent_orders' => $orders->sortByDesc('fulfillment_date')->take(10),
            'avg_books_per_order' => $orders->count() > 0 ? $totalQuantity / $orders->count() : 0,
            'fulfillment_rate' => 100 // All orders in this dataset are fulfilled
        ];
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
        $filename = 'comprehensive_report_' . Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->stream(function() use ($reports) {
            $file = fopen('php://output', 'w');
            
            // Report Header
            fputcsv($file, ['UUM Press Inventory System - Comprehensive Report']);
            fputcsv($file, ['Generated on: ' . Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s') . ' MYT']);
            fputcsv($file, ['Report Period: ' . $reports['filters']['date_from'] . ' to ' . $reports['filters']['date_to']]);
            if ($reports['filters']['category']) {
                fputcsv($file, ['Category Filter: ' . $reports['filters']['category']]);
            }
            fputcsv($file, []);
            
            // Summary Statistics
            fputcsv($file, ['=== INVENTORY SUMMARY ===']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Books', $reports['summary']['total_books']]);
            fputcsv($file, ['Total Stock', number_format($reports['summary']['total_stock'])]);
            fputcsv($file, ['Total Value (RM)', number_format($reports['summary']['total_value'], 2)]);
            fputcsv($file, ['Unique Categories', $reports['summary']['unique_categories']]);
            fputcsv($file, ['Out of Stock Books', $reports['summary']['out_of_stock']]);
            fputcsv($file, ['Low Stock Books (≤10)', $reports['summary']['low_stock']]);
            fputcsv($file, []);
            
            // Stock Distribution
            fputcsv($file, ['=== STOCK DISTRIBUTION ===']);
            fputcsv($file, ['Stock Level', 'Count']);
            fputcsv($file, ['Out of Stock (0)', $reports['inventory']['stock_distribution']['out_of_stock']]);
            fputcsv($file, ['Low Stock (1-10)', $reports['inventory']['stock_distribution']['low_stock']]);
            fputcsv($file, ['Medium Stock (11-50)', $reports['inventory']['stock_distribution']['medium_stock']]);
            fputcsv($file, ['High Stock (>50)', $reports['inventory']['stock_distribution']['high_stock']]);
            fputcsv($file, []);
            
            // Category Analysis
            fputcsv($file, ['=== CATEGORY ANALYSIS ===']);
            fputcsv($file, ['Category', 'Books Count', 'Total Stock', 'Average Price (RM)', 'Total Value (RM)', 'Out of Stock Count']);
            foreach ($reports['categories'] as $category) {
                fputcsv($file, [
                    $category['name'],
                    $category['books_count'],
                    number_format($category['total_stock']),
                    number_format($category['avg_price'], 2),
                    number_format($category['total_value'], 2),
                    $category['out_of_stock_count']
                ]);
            }
            fputcsv($file, []);
            
            // Top Value Books
            fputcsv($file, ['=== TOP VALUE BOOKS ===']);
            fputcsv($file, ['Title', 'Category', 'Stock', 'Unit Price (RM)', 'Total Value (RM)']);
            foreach ($reports['inventory']['top_value_books'] as $book) {
                fputcsv($file, [
                    $book->title,
                    $book->category ?: 'Uncategorized',
                    $book->stock,
                    number_format($book->price, 2),
                    number_format($book->price * $book->stock, 2)
                ]);
            }
            fputcsv($file, []);
            
            // Activity Summary
            fputcsv($file, ['=== ACTIVITY SUMMARY ===']);
            fputcsv($file, ['Metric', 'Count']);
            fputcsv($file, ['Total Activities', $reports['activity']['total_activities']]);
            if (isset($reports['activity']['activity_by_action']['created'])) {
                fputcsv($file, ['Books Created', $reports['activity']['activity_by_action']['created']]);
            }
            if (isset($reports['activity']['activity_by_action']['updated'])) {
                fputcsv($file, ['Books Updated', $reports['activity']['activity_by_action']['updated']]);
            }
            if (isset($reports['activity']['activity_by_action']['deleted'])) {
                fputcsv($file, ['Books Deleted', $reports['activity']['activity_by_action']['deleted']]);
            }
            fputcsv($file, []);
            
            // Sales Analytics (if available)
            if (isset($reports['sales']) && $reports['sales']['total_sales'] > 0) {
                fputcsv($file, ['=== SALES SUMMARY ===']);
                fputcsv($file, ['Metric', 'Value']);
                fputcsv($file, ['Total Sales', $reports['sales']['total_sales']]);
                fputcsv($file, ['Total Revenue (RM)', number_format($reports['sales']['total_revenue'], 2)]);
                fputcsv($file, ['Total Books Sold', $reports['sales']['total_quantity']]);
                fputcsv($file, ['Average Sale Value (RM)', number_format($reports['sales']['avg_sale_value'], 2)]);
                fputcsv($file, ['Unique Books Sold', $reports['sales']['unique_books_sold']]);
                fputcsv($file, ['Platforms Used', $reports['sales']['platforms_used']]);
                fputcsv($file, []);
                
                // Platform Performance
                if ($reports['sales']['sales_by_platform']->count() > 0) {
                    fputcsv($file, ['=== PLATFORM PERFORMANCE ===']);
                    fputcsv($file, ['Platform', 'Total Sales', 'Revenue (RM)', 'Books Sold', 'Avg Sale Value (RM)']);
                    foreach ($reports['sales']['sales_by_platform'] as $platform) {
                        fputcsv($file, [
                            $platform['platform'],
                            $platform['total_sales'],
                            number_format($platform['total_revenue'], 2),
                            $platform['total_quantity'],
                            number_format($platform['avg_sale_value'], 2)
                        ]);
                    }
                    fputcsv($file, []);
                }
                
                // Top Selling Books
                if ($reports['sales']['top_selling_books']->count() > 0) {
                    fputcsv($file, ['=== TOP SELLING BOOKS ===']);
                    fputcsv($file, ['Book Title', 'Category', 'Quantity Sold', 'Sales Count', 'Total Revenue (RM)', 'Avg Sale Price (RM)']);
                    foreach ($reports['sales']['top_selling_books'] as $bookSale) {
                        fputcsv($file, [
                            $bookSale['book']->title,
                            $bookSale['book']->category ?: 'Uncategorized',
                            $bookSale['total_quantity'],
                            $bookSale['sales_count'],
                            number_format($bookSale['total_revenue'], 2),
                            number_format($bookSale['avg_sale_price'], 2)
                        ]);
                    }
                    fputcsv($file, []);
                }
            }
            
            // Footer
            fputcsv($file, ['=== END OF REPORT ===']);
            fputcsv($file, ['Report generated by UUM Press Inventory System']);
            
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