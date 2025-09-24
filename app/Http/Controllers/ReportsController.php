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
        return redirect()->route('reports.inventory');
    }
    
    public function inventory(Request $request)
    {
        $data = $this->getInventoryData($request);
        return view('reports.inventory', compact('data'));
    }
    
    public function sales(Request $request)
    {
        $data = $this->getSalesData($request);
        return view('reports.sales', compact('data'));
    }
    
    public function autocomplete(Request $request)
    {
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $books = Book::where('title', 'LIKE', '%' . $query . '%')
                    ->orWhere('isbn', 'LIKE', '%' . $query . '%')
                    ->orWhere('authors_editors', 'LIKE', '%' . $query . '%')
                    ->select('id', 'title', 'isbn', 'category', 'stock')
                    ->limit(10)
                    ->get();
        
        return response()->json($books->map(function($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'category' => $book->category ?: 'Uncategorized',
                'stock' => $book->stock,
                'display' => $book->title . ' (' . $book->isbn . ')'
            ];
        }));
    }
    
    public function bookDetails($id, Request $request)
    {
        $book = Book::findOrFail($id);
        
        $filters = $this->getFilters($request);
        
        $stockHistory = $this->getStockHistory($book, $filters);
        $salesHistory = $this->getSalesHistoryForBook($book, $filters);
        
        return response()->json([
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'category' => $book->category ?: 'Uncategorized',
                'authors_editors' => $book->authors_editors,
                'year' => $book->year,
                'current_stock' => $book->stock,
                'current_price' => $book->price,
                'total_value' => $book->price * $book->stock,
                'stock_status' => $this->getStockStatus($book->stock),
                'created_at' => $book->created_at->format('M d, Y'),
                'updated_at' => $book->updated_at->format('M d, Y')
            ],
            'stock_history' => $stockHistory,
            'sales_summary' => $salesHistory,
            'recommendations' => $this->getReorderRecommendation($book, $salesHistory)
        ]);
    }
    
    public function exportInventory(Request $request)
    {
        $data = $this->getInventoryData($request);
        $format = $request->get('format', 'csv');
        
        if ($format === 'csv') {
            return $this->exportInventoryCsv($data);
        } elseif ($format === 'pdf') {
            return $this->exportInventoryPdf($data);
        }
        
        return redirect()->back()->with('error', 'Invalid export format');
    }
    
    public function exportSales(Request $request)
    {
        $data = $this->getSalesData($request);
        $format = $request->get('format', 'csv');
        
        if ($format === 'csv') {
            return $this->exportSalesCsv($data);
        } elseif ($format === 'pdf') {
            return $this->exportSalesPdf($data);
        }
        
        return redirect()->back()->with('error', 'Invalid export format');
    }
    
    public function trendsData(Request $request)
    {
        $filters = $this->getFilters($request);
        $type = $request->get('type', 'inventory');
        
        // For now, return basic trend data based on existing methods
        if ($type === 'inventory') {
            $data = $this->getInventoryData($request);
            return response()->json([
                'type' => 'inventory',
                'metrics' => $data['metrics'],
                'categories' => $data['categories']->take(5),
                'message' => 'Basic inventory trends'
            ]);
        } elseif ($type === 'sales') {
            $data = $this->getSalesData($request);
            return response()->json([
                'type' => 'sales', 
                'metrics' => $data['metrics'],
                'trends' => $data['trends'],
                'message' => 'Basic sales trends'
            ]);
        }
        
        return response()->json(['error' => 'Invalid trend type'], 400);
    }
    
    private function getFilters(Request $request)
    {
        $timezone = 'Asia/Kuala_Lumpur';
        $defaultFrom = Carbon::now($timezone)->subDays(30)->toDateString();
        $defaultTo = Carbon::now($timezone)->toDateString();
        
        return [
            'date_from' => $request->get('date_from', $defaultFrom),
            'date_to' => $request->get('date_to', $defaultTo),
            'category' => $request->get('category'),
            'platform' => $request->get('platform'),
            'stock_level' => $request->get('stock_level'),
            'timezone' => $timezone
        ];
    }
    
    private function getInventoryData(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $booksQuery = Book::query();
        
        if ($filters['category']) {
            $booksQuery->where('category', $filters['category']);
        }
        
        if ($filters['stock_level']) {
            switch ($filters['stock_level']) {
                case 'out_of_stock':
                    $booksQuery->where('stock', 0);
                    break;
                case 'low_stock':
                    $booksQuery->where('stock', '>', 0)->where('stock', '<=', 10);
                    break;
                case 'medium_stock':
                    $booksQuery->where('stock', '>', 10)->where('stock', '<=', 50);
                    break;
                case 'high_stock':
                    $booksQuery->where('stock', '>', 50);
                    break;
            }
        }
        
        $books = $booksQuery->get();
        
        $inventoryMetrics = $this->calculateInventoryMetrics($books);
        $stockDistribution = $this->calculateStockDistribution($books);
        $categoryAnalysis = $this->calculateCategoryAnalysis($books);
        $valueAnalysis = $this->calculateValueAnalysis($books);
        $alertsData = $this->calculateAlerts($books);
        $deadStock = $this->calculateDeadStock($books, $filters);
        
        return [
            'filters' => array_merge($filters, [
                'available_categories' => Book::distinct('category')->whereNotNull('category')->pluck('category')->sort(),
                'available_stock_levels' => [
                    'out_of_stock' => 'Out of Stock',
                    'low_stock' => 'Low Stock (1-10)',
                    'medium_stock' => 'Medium Stock (11-50)',
                    'high_stock' => 'High Stock (50+)'
                ]
            ]),
            'metrics' => $inventoryMetrics,
            'stock_distribution' => $stockDistribution,
            'categories' => $categoryAnalysis,
            'value_analysis' => $valueAnalysis,
            'alerts' => $alertsData,
            'dead_stock' => $deadStock,
            'charts' => [
                'stock_distribution' => $stockDistribution,
                'category_values' => $categoryAnalysis->take(8),
                'top_value_books' => $valueAnalysis['top_value_books']->take(10)
            ]
        ];
    }
    
    private function getSalesData(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $ordersQuery = Order::with(['orderItems.book'])
            ->where('status', 'fulfilled')
            ->whereBetween('fulfillment_date', [
                Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
            ]);
            
        if ($filters['platform']) {
            $ordersQuery->where('platform', $filters['platform']);
        }
        
        if ($filters['category']) {
            $ordersQuery->whereHas('orderItems.book', function($query) use ($filters) {
                $query->where('category', $filters['category']);
            });
        }
        
        $orders = $ordersQuery->get();
        
        $salesMetrics = $this->calculateSalesMetrics($orders);
        $platformAnalysis = $this->calculatePlatformAnalysis($orders);
        $bestSellers = $this->calculateBestSellers($orders);
        $salesTrends = $this->calculateSalesTrends($orders, $filters);
        $recentOrders = $orders->sortByDesc('fulfillment_date')->take(10);
        
        return [
            'filters' => array_merge($filters, [
                'available_categories' => Book::distinct('category')->whereNotNull('category')->pluck('category')->sort(),
                'available_platforms' => Order::distinct('platform')->whereNotNull('platform')->pluck('platform')->sort()
            ]),
            'metrics' => $salesMetrics,
            'platforms' => $platformAnalysis,
            'best_sellers' => $bestSellers,
            'trends' => $salesTrends,
            'recent_orders' => $recentOrders,
            'charts' => [
                'platform_performance' => $platformAnalysis,
                'best_sellers_chart' => $bestSellers->take(8),
                'sales_trends' => $salesTrends['daily_sales']
            ]
        ];
    }
    
    private function calculateInventoryMetrics($books)
    {
        $totalValue = $books->sum(function($book) {
            return $book->price * $book->stock;
        });
        
        return [
            'total_books' => $books->count(),
            'total_stock' => $books->sum('stock'),
            'total_value' => $totalValue,
            'unique_categories' => $books->pluck('category')->filter()->unique()->count(),
            'out_of_stock' => $books->where('stock', 0)->count(),
            'low_stock' => $books->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'avg_value_per_book' => $books->count() > 0 ? $totalValue / $books->count() : 0,
            'avg_stock_per_book' => $books->count() > 0 ? $books->sum('stock') / $books->count() : 0
        ];
    }
    
    private function calculateStockDistribution($books)
    {
        return [
            'out_of_stock' => $books->where('stock', 0)->count(),
            'low_stock' => $books->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'medium_stock' => $books->where('stock', '>', 10)->where('stock', '<=', 50)->count(),
            'high_stock' => $books->where('stock', '>', 50)->count()
        ];
    }
    
    private function calculateCategoryAnalysis($books)
    {
        return $books->groupBy('category')->map(function($categoryBooks, $category) {
            $totalValue = $categoryBooks->sum(function($book) {
                return $book->price * $book->stock;
            });
            
            return [
                'name' => $category ?: 'Uncategorized',
                'books_count' => $categoryBooks->count(),
                'total_stock' => $categoryBooks->sum('stock'),
                'avg_price' => $categoryBooks->avg('price'),
                'total_value' => $totalValue,
                'out_of_stock_count' => $categoryBooks->where('stock', 0)->count(),
                'low_stock_count' => $categoryBooks->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
                'avg_stock_per_book' => $categoryBooks->count() > 0 ? $categoryBooks->sum('stock') / $categoryBooks->count() : 0
            ];
        })->sortByDesc('total_value');
    }
    
    private function calculateValueAnalysis($books)
    {
        $topValueBooks = $books->sortByDesc(function($book) {
            return $book->price * $book->stock;
        })->take(15);
        
        $highValueBooks = $books->filter(function($book) {
            return ($book->price * $book->stock) > 1000;
        });
        
        return [
            'top_value_books' => $topValueBooks,
            'high_value_count' => $highValueBooks->count(),
            'high_value_total' => $highValueBooks->sum(function($book) {
                return $book->price * $book->stock;
            })
        ];
    }
    
    private function calculateAlerts($books)
    {
        $outOfStock = $books->where('stock', 0);
        $lowStock = $books->where('stock', '>', 0)->where('stock', '<=', 10);
        $highValueLowStock = $books->filter(function($book) {
            return $book->stock <= 10 && $book->stock > 0 && $book->price > 50;
        });
        
        return [
            'out_of_stock' => $outOfStock->take(10),
            'low_stock' => $lowStock->take(10),
            'high_value_low_stock' => $highValueLowStock->take(10),
            'total_alerts' => $outOfStock->count() + $lowStock->count()
        ];
    }
    
    private function calculateDeadStock($books, $filters)
    {
        $deadStockThreshold = Carbon::parse($filters['date_to'])->subDays(90);
        
        $deadStockBooks = $books->filter(function($book) use ($deadStockThreshold) {
            $recentSales = OrderItem::whereHas('order', function($query) use ($deadStockThreshold) {
                $query->where('status', 'fulfilled')
                      ->where('fulfillment_date', '>=', $deadStockThreshold);
            })->where('book_id', $book->id)->exists();
            
            return !$recentSales && $book->stock > 0;
        });
        
        return [
            'count' => $deadStockBooks->count(),
            'books' => $deadStockBooks->take(10),
            'total_value' => $deadStockBooks->sum(function($book) {
                return $book->price * $book->stock;
            })
        ];
    }
    
    private function calculateSalesMetrics($orders)
    {
        if ($orders->isEmpty()) {
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_quantity' => 0,
                'avg_order_value' => 0,
                'unique_books_sold' => 0,
                'platforms_used' => 0,
                'avg_books_per_order' => 0,
                'fulfillment_rate' => 100
            ];
        }
        
        $totalRevenue = 0;
        $totalQuantity = 0;
        $uniqueBooks = collect();
        
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalRevenue += $item->quantity_fulfilled * $item->unit_price;
                $totalQuantity += $item->quantity_fulfilled;
                $uniqueBooks->push($item->book_id);
            }
        }
        
        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $totalRevenue,
            'total_quantity' => $totalQuantity,
            'avg_order_value' => $orders->count() > 0 ? $totalRevenue / $orders->count() : 0,
            'unique_books_sold' => $uniqueBooks->unique()->count(),
            'platforms_used' => $orders->pluck('platform')->unique()->count(),
            'avg_books_per_order' => $orders->count() > 0 ? $totalQuantity / $orders->count() : 0,
            'fulfillment_rate' => 100
        ];
    }
    
    private function calculatePlatformAnalysis($orders)
    {
        $totalRevenue = 0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalRevenue += $item->quantity_fulfilled * $item->unit_price;
            }
        }
        
        return $orders->groupBy('platform')->map(function($platformOrders, $platform) use ($totalRevenue) {
            $platformRevenue = 0;
            $platformQuantity = 0;
            
            foreach ($platformOrders as $order) {
                foreach ($order->orderItems as $item) {
                    $platformRevenue += $item->quantity_fulfilled * $item->unit_price;
                    $platformQuantity += $item->quantity_fulfilled;
                }
            }
            
            return [
                'platform' => $platform ?: 'Not Specified',
                'total_orders' => $platformOrders->count(),
                'total_revenue' => $platformRevenue,
                'total_quantity' => $platformQuantity,
                'avg_order_value' => $platformOrders->count() > 0 ? $platformRevenue / $platformOrders->count() : 0,
                'market_share' => $totalRevenue > 0 ? ($platformRevenue / $totalRevenue) * 100 : 0,
                'efficiency_score' => $platformOrders->count() > 0 ? $platformRevenue / $platformOrders->count() : 0
            ];
        })->sortByDesc('total_revenue');
    }
    
    private function calculateBestSellers($orders)
    {
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
        
        return collect($bookSales)->map(function($bookData) {
            $bookData['avg_sale_price'] = $bookData['price_count'] > 0 ? $bookData['total_unit_price'] / $bookData['price_count'] : 0;
            $bookData['velocity'] = $bookData['total_quantity']; // Simple velocity metric
            unset($bookData['total_unit_price'], $bookData['price_count']);
            return $bookData;
        })->sortByDesc('total_quantity')->take(15);
    }
    
    private function calculateSalesTrends($orders, $filters)
    {
        $dailySales = $orders->groupBy(function($order) {
            return $order->fulfillment_date->format('Y-m-d');
        })->map(function($dayOrders) {
            $dailyRevenue = 0;
            $dailyQuantity = 0;
            
            foreach ($dayOrders as $order) {
                foreach ($order->orderItems as $item) {
                    $dailyRevenue += $item->quantity_fulfilled * $item->unit_price;
                    $dailyQuantity += $item->quantity_fulfilled;
                }
            }
            
            return [
                'orders' => $dayOrders->count(),
                'revenue' => $dailyRevenue,
                'quantity' => $dailyQuantity
            ];
        })->sortKeys();
        
        return [
            'daily_sales' => $dailySales,
            'peak_day' => $dailySales->sortByDesc('revenue')->first(),
            'avg_daily_revenue' => $dailySales->avg('revenue'),
            'total_days' => $dailySales->count()
        ];
    }
    
    private function getStockHistory($book, $filters)
    {
        $auditLogs = AuditLog::where('record_id', $book->id)
            ->where('table_name', 'books')
            ->whereBetween('created_at', [
                Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
            ])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
        
        return $auditLogs->map(function($log) {
            return [
                'date' => $log->created_at->format('M d, Y H:i'),
                'action' => $log->action,
                'changes' => $log->getReadableChanges()
            ];
        });
    }
    
    private function getSalesHistoryForBook($book, $filters)
    {
        $salesData = OrderItem::whereHas('order', function($query) use ($filters) {
                $query->where('status', 'fulfilled')
                      ->whereBetween('fulfillment_date', [
                          Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                          Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
                      ]);
            })
            ->where('book_id', $book->id)
            ->with(['order' => function($query) {
                $query->select('id', 'platform', 'fulfillment_date');
            }])
            ->get();
        
        if ($salesData->isEmpty()) {
            return [
                'has_sales' => false,
                'message' => 'No sales in selected period'
            ];
        }
        
        $totalQuantity = $salesData->sum('quantity_fulfilled');
        $totalRevenue = $salesData->sum(function($item) {
            return $item->quantity_fulfilled * $item->unit_price;
        });
        
        return [
            'has_sales' => true,
            'total_quantity' => $totalQuantity,
            'total_revenue' => $totalRevenue,
            'orders_count' => $salesData->groupBy('order_id')->count(),
            'avg_sale_price' => $salesData->avg('unit_price'),
            'last_sale_date' => $salesData->max(function($item) {
                return $item->order->fulfillment_date;
            }),
            'velocity' => $totalQuantity // Simple velocity
        ];
    }
    
    private function getReorderRecommendation($book, $salesHistory)
    {
        if (!$salesHistory['has_sales'] || $book->stock > 20) {
            return [
                'recommended' => false,
                'reason' => $book->stock > 20 ? 'Sufficient stock available' : 'No recent sales data'
            ];
        }
        
        $velocity = $salesHistory['velocity'] ?? 0;
        $daysOfStock = $velocity > 0 ? $book->stock / ($velocity / 30) : 999; // Assume 30-day period
        
        if ($daysOfStock < 30 && $book->stock < 20) {
            return [
                'recommended' => true,
                'reason' => "Low stock with good sales velocity ($daysOfStock days remaining)",
                'suggested_quantity' => max(20, $velocity * 2) // 2 months of stock
            ];
        }
        
        return [
            'recommended' => false,
            'reason' => 'Stock levels appear adequate'
        ];
    }
    
    private function getStockStatus($stock)
    {
        if ($stock == 0) return 'Out of Stock';
        if ($stock <= 10) return 'Low Stock';
        if ($stock <= 50) return 'Medium Stock';
        return 'High Stock';
    }
    
    private function exportInventoryCsv($data)
    {
        $filename = 'inventory_report_' . Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->stream(function() use ($data) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['UUM Press Inventory Report']);
            fputcsv($file, ['Generated: ' . Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s') . ' MYT']);
            fputcsv($file, ['Period: ' . $data['filters']['date_from'] . ' to ' . $data['filters']['date_to']]);
            
            if ($data['filters']['category']) {
                fputcsv($file, ['Category: ' . $data['filters']['category']]);
            }
            if ($data['filters']['stock_level']) {
                fputcsv($file, ['Stock Level: ' . $data['filters']['available_stock_levels'][$data['filters']['stock_level']]]);
            }
            
            fputcsv($file, []);
            
            fputcsv($file, ['=== INVENTORY METRICS ===']);
            fputcsv($file, ['Total Books', number_format($data['metrics']['total_books'])]);
            fputcsv($file, ['Total Stock', number_format($data['metrics']['total_stock'])]);
            fputcsv($file, ['Total Value (RM)', number_format($data['metrics']['total_value'], 2)]);
            fputcsv($file, ['Out of Stock', $data['metrics']['out_of_stock']]);
            fputcsv($file, ['Low Stock', $data['metrics']['low_stock']]);
            fputcsv($file, []);
            
            fputcsv($file, ['=== CATEGORY ANALYSIS ===']);
            fputcsv($file, ['Category', 'Books', 'Stock', 'Avg Price', 'Total Value', 'Out of Stock']);
            foreach ($data['categories'] as $category) {
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
            
            if ($data['alerts']['total_alerts'] > 0) {
                fputcsv($file, ['=== STOCK ALERTS ===']);
                fputcsv($file, ['Book Title', 'ISBN', 'Category', 'Current Stock', 'Status']);
                
                foreach ($data['alerts']['out_of_stock'] as $book) {
                    fputcsv($file, [$book->title, $book->isbn, $book->category ?: 'Uncategorized', $book->stock, 'OUT OF STOCK']);
                }
                
                foreach ($data['alerts']['low_stock'] as $book) {
                    fputcsv($file, [$book->title, $book->isbn, $book->category ?: 'Uncategorized', $book->stock, 'LOW STOCK']);
                }
                
                fputcsv($file, []);
            }
            
            fclose($file);
        }, 200, $headers);
    }
    
    private function exportSalesCsv($data)
    {
        $filename = 'sales_report_' . Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->stream(function() use ($data) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['UUM Press Sales Report']);
            fputcsv($file, ['Generated: ' . Carbon::now('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s') . ' MYT']);
            fputcsv($file, ['Period: ' . $data['filters']['date_from'] . ' to ' . $data['filters']['date_to']]);
            
            if ($data['filters']['category']) {
                fputcsv($file, ['Category: ' . $data['filters']['category']]);
            }
            if ($data['filters']['platform']) {
                fputcsv($file, ['Platform: ' . $data['filters']['platform']]);
            }
            
            fputcsv($file, []);
            
            fputcsv($file, ['=== SALES METRICS ===']);
            fputcsv($file, ['Total Orders', number_format($data['metrics']['total_orders'])]);
            fputcsv($file, ['Total Revenue (RM)', number_format($data['metrics']['total_revenue'], 2)]);
            fputcsv($file, ['Total Books Sold', number_format($data['metrics']['total_quantity'])]);
            fputcsv($file, ['Average Order Value (RM)', number_format($data['metrics']['avg_order_value'], 2)]);
            fputcsv($file, []);
            
            if ($data['platforms']->count() > 0) {
                fputcsv($file, ['=== PLATFORM PERFORMANCE ===']);
                fputcsv($file, ['Platform', 'Orders', 'Revenue (RM)', 'Books Sold', 'Market Share %']);
                foreach ($data['platforms'] as $platform) {
                    fputcsv($file, [
                        $platform['platform'],
                        number_format($platform['total_orders']),
                        number_format($platform['total_revenue'], 2),
                        number_format($platform['total_quantity']),
                        number_format($platform['market_share'], 1)
                    ]);
                }
                fputcsv($file, []);
            }
            
            if ($data['best_sellers']->count() > 0) {
                fputcsv($file, ['=== BEST SELLING BOOKS ===']);
                fputcsv($file, ['Book Title', 'Category', 'Quantity Sold', 'Revenue (RM)', 'Orders']);
                foreach ($data['best_sellers'] as $book) {
                    fputcsv($file, [
                        $book['book']->title,
                        $book['book']->category ?: 'Uncategorized',
                        number_format($book['total_quantity']),
                        number_format($book['total_revenue'], 2),
                        $book['orders_count']
                    ]);
                }
            }
            
            fclose($file);
        }, 200, $headers);
    }
    
    private function exportInventoryPdf($data)
    {
        return redirect()->back()->with('error', 'PDF export not implemented yet');
    }
    
    private function exportSalesPdf($data)
    {
        return redirect()->back()->with('error', 'PDF export not implemented yet');
    }
}