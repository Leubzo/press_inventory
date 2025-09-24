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
        return $this->exportInventoryCsv($data);
    }

    public function exportSales(Request $request)
    {
        $data = $this->getSalesData($request);
        return $this->exportSalesCsv($data);
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
        $now = Carbon::now($timezone);

        // Set default to 1 week if no dates provided
        $defaultFrom = $now->copy()->subWeek()->toDateString();
        $defaultTo = $now->toDateString();

        $dateFrom = $request->get('date_from', $defaultFrom);
        $dateTo = $request->get('date_to', $defaultTo);

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'category' => $request->get('category'),
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

        // NEW: Get inventory changes from AuditLog
        $inventoryChanges = $this->getInventoryChanges($filters);

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
            'changes' => $inventoryChanges,
            'charts' => [
                'stock_distribution' => $stockDistribution,
                'category_values' => $categoryAnalysis->take(8)
            ]
        ];
    }

    private function getInventoryChanges($filters)
    {
        // Get audit logs for books in the date range
        $auditQuery = AuditLog::where('table_name', 'books')
            ->whereBetween('created_at', [
                Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
            ])
            ->with('book');

        $auditLogs = $auditQuery->get();

        // Separate into different types of changes
        $booksAdded = $auditLogs->where('action', 'created');
        $booksDeleted = $auditLogs->where('action', 'deleted');
        $stockChanges = $auditLogs->where('action', 'updated')
            ->filter(function($log) {
                return isset($log->old_values['stock']) && isset($log->new_values['stock']) &&
                       $log->old_values['stock'] != $log->new_values['stock'];
            });

        // Calculate summaries
        $totalStockIncrease = $stockChanges->sum(function($log) {
            return ($log->new_values['stock'] ?? 0) - ($log->old_values['stock'] ?? 0);
        });

        return [
            'summary' => [
                'books_added' => $booksAdded->count(),
                'books_deleted' => $booksDeleted->count(),
                'stock_changes' => $stockChanges->count(),
                'total_stock_change' => $totalStockIncrease
            ],
            'books_added' => $booksAdded->take(10)->map(function($log) {
                return [
                    'title' => $log->new_values['title'] ?? ($log->book->title ?? 'Unknown'),
                    'isbn' => $log->new_values['isbn'] ?? ($log->book->isbn ?? 'N/A'),
                    'initial_stock' => $log->new_values['stock'] ?? 0,
                    'date' => $log->created_at->format('M d, Y H:i')
                ];
            }),
            'books_deleted' => $booksDeleted->take(10)->map(function($log) {
                return [
                    'title' => $log->old_values['title'] ?? 'Unknown',
                    'isbn' => $log->old_values['isbn'] ?? 'N/A',
                    'final_stock' => $log->old_values['stock'] ?? 0,
                    'date' => $log->created_at->format('M d, Y H:i')
                ];
            }),
            'stock_changes' => $stockChanges->take(10)->map(function($log) {
                $oldStock = $log->old_values['stock'] ?? 0;
                $newStock = $log->new_values['stock'] ?? 0;
                $change = $newStock - $oldStock;

                return [
                    'title' => $log->new_values['title'] ?? ($log->old_values['title'] ?? ($log->book->title ?? 'Unknown')),
                    'isbn' => $log->new_values['isbn'] ?? ($log->old_values['isbn'] ?? ($log->book->isbn ?? 'N/A')),
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'change' => $change,
                    'change_type' => $change > 0 ? 'increase' : 'decrease',
                    'date' => $log->created_at->format('M d, Y H:i')
                ];
            })->sortByDesc('change')
        ];
    }

    private function getSalesChanges($filters)
    {
        // Get orders created, approved, and fulfilled in the date range
        $ordersCreated = Order::with(['requester', 'orderItems.book'])
            ->whereBetween('created_at', [
                Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
            ])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $ordersApproved = Order::with(['approver', 'orderItems.book'])
            ->whereNotNull('approval_date')
            ->whereBetween('approval_date', [
                Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
            ])
            ->orderBy('approval_date', 'desc')
            ->take(20)
            ->get();

        $ordersFulfilled = Order::with(['fulfiller', 'orderItems.book'])
            ->where('status', 'fulfilled')
            ->whereNotNull('fulfillment_date')
            ->whereBetween('fulfillment_date', [
                Carbon::parse($filters['date_from'], $filters['timezone'])->startOfDay(),
                Carbon::parse($filters['date_to'], $filters['timezone'])->endOfDay()
            ])
            ->orderBy('fulfillment_date', 'desc')
            ->take(20)
            ->get();

        return [
            'summary' => [
                'orders_created' => $ordersCreated->count(),
                'orders_approved' => $ordersApproved->count(),
                'orders_fulfilled' => $ordersFulfilled->count(),
                'total_revenue' => $ordersFulfilled->sum(function($order) {
                    return $order->orderItems->sum(function($item) {
                        return $item->unit_price * ($item->quantity_fulfilled ?? 0);
                    });
                })
            ],
            'orders_created' => $ordersCreated->map(function($order) {
                return [
                    'order_number' => $order->order_number,
                    'requester_name' => $order->requester->name ?? 'Unknown',
                    'items_count' => $order->orderItems->count(),
                    'status' => $order->status,
                    'date' => $order->created_at->format('M d, Y H:i')
                ];
            }),
            'orders_fulfilled' => $ordersFulfilled->map(function($order) {
                $totalValue = $order->orderItems->sum(function($item) {
                    return $item->unit_price * ($item->quantity_fulfilled ?? 0);
                });

                return [
                    'order_number' => $order->order_number,
                    'fulfiller_name' => $order->fulfiller->name ?? 'Unknown',
                    'items_count' => $order->orderItems->count(),
                    'total_value' => $totalValue,
                    'date' => $order->fulfillment_date->format('M d, Y H:i')
                ];
            })
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

        if ($filters['category']) {
            $ordersQuery->whereHas('orderItems.book', function($query) use ($filters) {
                $query->where('category', $filters['category']);
            });
        }

        $orders = $ordersQuery->get();

        $salesMetrics = $this->calculateSimpleSalesMetrics($orders);
        $bestSellers = $this->calculateBestSellers($orders);

        // Chart data
        $salesTrend = $this->calculateSalesTrend($orders, $filters);
        $categorySales = $this->calculateCategorySales($orders);

        // Get sales changes and paginate
        $salesChanges = $this->getSalesChanges($filters);

        // Paginate best sellers (10 per page)
        $bestSellersPage = $request->get('best_sellers_page', 1);
        $perPage = 10;
        $bestSellersPaginated = $this->paginateCollection($bestSellers, $perPage, $bestSellersPage, 'best_sellers_page');

        return [
            'filters' => array_merge($filters, [
                'available_categories' => Book::distinct('category')->whereNotNull('category')->pluck('category')->sort()
            ]),
            'metrics' => $salesMetrics,
            'best_sellers' => $bestSellersPaginated,
            'sales_changes' => $salesChanges,
            'charts' => [
                'sales_trend' => $salesTrend,
                'category_sales' => $categorySales
            ]
        ];
    }

    private function calculateSimpleSalesMetrics($orders)
    {
        $totalRevenue = 0;
        $totalQuantity = 0;
        $totalItems = 0;

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $fulfilledQty = $item->quantity_fulfilled ?? 0;
                $totalRevenue += $item->unit_price * $fulfilledQty;
                $totalQuantity += $fulfilledQty;
                $totalItems++;
            }
        }

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $totalRevenue,
            'total_quantity' => $totalQuantity,
            'total_items' => $totalItems,
            'avg_order_value' => $orders->count() > 0 ? $totalRevenue / $orders->count() : 0,
            'avg_items_per_order' => $orders->count() > 0 ? $totalItems / $orders->count() : 0
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
            fputcsv($file, ['Unique Categories', $data['metrics']['unique_categories']]);
            fputcsv($file, ['Out of Stock', $data['metrics']['out_of_stock']]);
            fputcsv($file, ['Low Stock', $data['metrics']['low_stock']]);
            fputcsv($file, ['Average Value per Book (RM)', number_format($data['metrics']['avg_value_per_book'], 2)]);
            fputcsv($file, ['Average Stock per Book', number_format($data['metrics']['avg_stock_per_book'], 1)]);
            fputcsv($file, []);

            // Add stock distribution
            fputcsv($file, ['=== STOCK DISTRIBUTION ===']);
            fputcsv($file, ['Out of Stock (0)', $data['stock_distribution']['out_of_stock']]);
            fputcsv($file, ['Low Stock (1-10)', $data['stock_distribution']['low_stock']]);
            fputcsv($file, ['Medium Stock (11-50)', $data['stock_distribution']['medium_stock']]);
            fputcsv($file, ['High Stock (50+)', $data['stock_distribution']['high_stock']]);
            fputcsv($file, []);
            
            // Add inventory changes section
            fputcsv($file, ['=== INVENTORY CHANGES ===']);
            fputcsv($file, ['Books Added', $data['changes']['summary']['books_added']]);
            fputcsv($file, ['Books Deleted', $data['changes']['summary']['books_deleted']]);
            fputcsv($file, ['Stock Changes', $data['changes']['summary']['stock_changes']]);
            fputcsv($file, ['Total Stock Change', $data['changes']['summary']['total_stock_change']]);
            fputcsv($file, []);

            if (!$data['changes']['books_added']->isEmpty()) {
                fputcsv($file, ['=== BOOKS ADDED ===']);
                fputcsv($file, ['Title', 'ISBN', 'Initial Stock', 'Date Added']);
                foreach ($data['changes']['books_added'] as $book) {
                    fputcsv($file, [
                        $book['title'],
                        $book['isbn'],
                        $book['initial_stock'],
                        $book['date']
                    ]);
                }
                fputcsv($file, []);
            }

            if (!$data['changes']['stock_changes']->isEmpty()) {
                fputcsv($file, ['=== STOCK CHANGES ===']);
                fputcsv($file, ['Title', 'ISBN', 'Old Stock', 'New Stock', 'Change', 'Date']);
                foreach ($data['changes']['stock_changes'] as $change) {
                    fputcsv($file, [
                        $change['title'],
                        $change['isbn'],
                        $change['old_stock'],
                        $change['new_stock'],
                        ($change['change'] > 0 ? '+' : '') . $change['change'],
                        $change['date']
                    ]);
                }
                fputcsv($file, []);
            }

            fputcsv($file, ['=== CATEGORY ANALYSIS ===']);
            fputcsv($file, ['Category', 'Books', 'Stock', 'Total Value (RM)', 'Avg Price (RM)', 'Out of Stock', 'Low Stock', 'Performance %']);
            foreach ($data['categories'] as $category) {
                fputcsv($file, [
                    $category['name'],
                    $category['books_count'],
                    number_format($category['total_stock']),
                    number_format($category['total_value'], 2),
                    number_format($category['avg_price'], 2),
                    $category['out_of_stock_count'],
                    $category['low_stock_count'],
                    number_format($category['total_value'] / max($data['categories']->max('total_value'), 1) * 100, 1)
                ]);
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
            
            fputcsv($file, []);
            
            fputcsv($file, ['=== SALES METRICS ===']);
            fputcsv($file, ['Total Orders', number_format($data['metrics']['total_orders'])]);
            fputcsv($file, ['Total Revenue (RM)', number_format($data['metrics']['total_revenue'], 2)]);
            fputcsv($file, ['Total Books Sold', number_format($data['metrics']['total_quantity'])]);
            fputcsv($file, ['Average Order Value (RM)', number_format($data['metrics']['avg_order_value'], 2)]);
            fputcsv($file, ['Total Items', number_format($data['metrics']['total_items'])]);
            fputcsv($file, ['Average Items per Order', number_format($data['metrics']['avg_items_per_order'], 1)]);
            fputcsv($file, []);

            // Add sales activities section
            fputcsv($file, ['=== SALES ACTIVITIES ===']);
            fputcsv($file, ['Orders Created', $data['sales_changes']['summary']['orders_created']]);
            fputcsv($file, ['Orders Approved', $data['sales_changes']['summary']['orders_approved']]);
            fputcsv($file, ['Orders Fulfilled', $data['sales_changes']['summary']['orders_fulfilled']]);
            fputcsv($file, ['Total Revenue (Period)', 'RM ' . number_format($data['sales_changes']['summary']['total_revenue'], 2)]);
            fputcsv($file, []);

            // Add sales trend data
            if (isset($data['charts']['sales_trend']) && $data['charts']['sales_trend']->count() > 0) {
                $trendData = $data['charts']['sales_trend'];
                $dataType = $trendData->first()['type'] ?? 'daily';

                fputcsv($file, ['=== SALES TREND (' . strtoupper($dataType) . ') ===']);
                fputcsv($file, ['Date', 'Revenue (RM)', 'Orders']);
                foreach ($trendData as $trend) {
                    fputcsv($file, [
                        $trend['date'],
                        number_format($trend['revenue'], 2),
                        $trend['orders']
                    ]);
                }
                fputcsv($file, []);
            }

            // Add category sales performance
            if (isset($data['charts']['category_sales']) && $data['charts']['category_sales']->count() > 0) {
                fputcsv($file, ['=== CATEGORY SALES PERFORMANCE ===']);
                fputcsv($file, ['Category', 'Revenue (RM)', 'Quantity Sold', 'Orders']);
                foreach ($data['charts']['category_sales'] as $category) {
                    fputcsv($file, [
                        $category['category'],
                        number_format($category['revenue'], 2),
                        number_format($category['quantity']),
                        $category['orders']
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
                fputcsv($file, []);
            }
            
            fclose($file);
        }, 200, $headers);
    }

    /**
     * Paginate a collection manually
     */
    private function paginateCollection($collection, $perPage, $currentPage, $pageName = 'page')
    {
        $currentPageResults = $collection->forPage($currentPage, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageResults->values(),
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->except($pageName)
            ]
        );
    }

    private function calculateSalesTrend($orders, $filters)
    {
        $startDate = Carbon::parse($filters['date_from']);
        $endDate = Carbon::parse($filters['date_to']);
        $daysDiff = $startDate->diffInDays($endDate);

        // Determine aggregation level based on date range
        if ($daysDiff <= 14) {
            // Daily data for 2 weeks or less
            return $this->getDailySalesData($orders, $startDate, $endDate);
        } elseif ($daysDiff <= 90) {
            // Weekly data for 3 months or less
            return $this->getWeeklySalesData($orders, $startDate, $endDate);
        } else {
            // Monthly data for longer periods
            return $this->getMonthlySalesData($orders, $startDate, $endDate);
        }
    }

    private function getDailySalesData($orders, $startDate, $endDate)
    {
        $salesByDate = [];

        foreach ($orders as $order) {
            $date = $order->fulfillment_date->format('Y-m-d');
            $revenue = 0;

            foreach ($order->orderItems as $item) {
                $revenue += $item->unit_price * ($item->quantity_fulfilled ?? 0);
            }

            if (!isset($salesByDate[$date])) {
                $salesByDate[$date] = ['date' => $date, 'revenue' => 0, 'orders' => 0, 'type' => 'daily'];
            }

            $salesByDate[$date]['revenue'] += $revenue;
            $salesByDate[$date]['orders']++;
        }

        // Fill in missing dates with zero values
        $result = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $result[] = $salesByDate[$dateStr] ?? ['date' => $dateStr, 'revenue' => 0, 'orders' => 0, 'type' => 'daily'];
        }

        return collect($result);
    }

    private function getWeeklySalesData($orders, $startDate, $endDate)
    {
        $salesByWeek = [];

        foreach ($orders as $order) {
            $weekStart = $order->fulfillment_date->startOfWeek()->format('Y-m-d');
            $revenue = 0;

            foreach ($order->orderItems as $item) {
                $revenue += $item->unit_price * ($item->quantity_fulfilled ?? 0);
            }

            if (!isset($salesByWeek[$weekStart])) {
                $salesByWeek[$weekStart] = ['date' => $weekStart, 'revenue' => 0, 'orders' => 0, 'type' => 'weekly'];
            }

            $salesByWeek[$weekStart]['revenue'] += $revenue;
            $salesByWeek[$weekStart]['orders']++;
        }

        // Fill in missing weeks
        $result = [];
        $current = $startDate->copy()->startOfWeek();
        while ($current->lte($endDate)) {
            $weekStr = $current->format('Y-m-d');
            $result[] = $salesByWeek[$weekStr] ?? ['date' => $weekStr, 'revenue' => 0, 'orders' => 0, 'type' => 'weekly'];
            $current->addWeek();
        }

        return collect($result);
    }

    private function getMonthlySalesData($orders, $startDate, $endDate)
    {
        $salesByMonth = [];

        foreach ($orders as $order) {
            $monthStart = $order->fulfillment_date->startOfMonth()->format('Y-m-d');
            $revenue = 0;

            foreach ($order->orderItems as $item) {
                $revenue += $item->unit_price * ($item->quantity_fulfilled ?? 0);
            }

            if (!isset($salesByMonth[$monthStart])) {
                $salesByMonth[$monthStart] = ['date' => $monthStart, 'revenue' => 0, 'orders' => 0, 'type' => 'monthly'];
            }

            $salesByMonth[$monthStart]['revenue'] += $revenue;
            $salesByMonth[$monthStart]['orders']++;
        }

        // Fill in missing months
        $result = [];
        $current = $startDate->copy()->startOfMonth();
        while ($current->lte($endDate)) {
            $monthStr = $current->format('Y-m-d');
            $result[] = $salesByMonth[$monthStr] ?? ['date' => $monthStr, 'revenue' => 0, 'orders' => 0, 'type' => 'monthly'];
            $current->addMonth();
        }

        return collect($result);
    }

    private function calculateCategorySales($orders)
    {
        $categorySales = [];

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $category = $item->book->category ?? 'Uncategorized';
                $revenue = $item->unit_price * ($item->quantity_fulfilled ?? 0);
                $quantity = $item->quantity_fulfilled ?? 0;

                if (!isset($categorySales[$category])) {
                    $categorySales[$category] = [
                        'category' => $category,
                        'revenue' => 0,
                        'quantity' => 0,
                        'orders' => 0
                    ];
                }

                $categorySales[$category]['revenue'] += $revenue;
                $categorySales[$category]['quantity'] += $quantity;
                $categorySales[$category]['orders']++;
            }
        }

        return collect($categorySales)->sortByDesc('revenue')->values();
    }
}