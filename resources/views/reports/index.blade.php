@extends('layouts.base')

@push('styles')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - UUM Press Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #f77062 0%, #fe5196 100%);
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .main-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }

        .stats-card.summary {
            border-left-color: #667eea;
        }

        .stats-card.inventory {
            border-left-color: #4facfe;
        }

        .stats-card.activity {
            border-left-color: #fa709a;
        }

        .stats-card.categories {
            border-left-color: #f093fb;
        }

        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .metric-item:last-child {
            border-bottom: none;
        }

        .metric-label {
            color: #64748b;
            font-weight: 500;
        }

        .metric-value {
            font-weight: 600;
            color: #1e293b;
        }

        .metric-value.danger {
            color: #dc2626;
        }

        .metric-value.warning {
            color: #d97706;
        }

        .metric-value.success {
            color: #059669;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            height: 400px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .btn-custom {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary.btn-custom {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary.btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success.btn-custom {
            background: var(--success-gradient);
            border: none;
        }

        .btn-success.btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        canvas {
            max-height: 300px !important;
            max-width: 100% !important;
        }

        .platform-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .platform-shopee { background-color: #ff5722; color: white; }
        .platform-lazada { background-color: #1565c0; color: white; }
        .platform-tiktokshop { background-color: #000; color: white; }
        .platform-facebook { background-color: #4267b2; color: white; }
        .platform-instagram { background-color: #e4405f; color: white; }
        .platform-offline { background-color: #6c757d; color: white; }
        .platform-other { background-color: #17a2b8; color: white; }

        /* Tab Navigation Styles */
        .custom-tabs {
            background: white;
            border-radius: 10px 10px 0 0;
            padding: 0.5rem 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .custom-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .custom-tabs .nav-link:hover {
            background: #f8f9fa;
            color: #667eea;
            transform: translateY(-2px);
        }

        .custom-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .custom-tabs .nav-link .badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        @media (max-width: 768px) {
            .metric-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            
            .chart-container {
                height: 350px;
            }
            
            .chart-wrapper {
                height: 250px;
            }
        }
    </style>
@endpush

@section('content')
<!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
                    </h1>
                    <p class="text-muted mb-0">Comprehensive inventory insights and analytics</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-success btn-custom">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Report Filters</h5>
            <form method="GET" action="{{ route('reports.index') }}" class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ $reports['filters']['date_from'] }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ $reports['filters']['date_to'] }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($reports['filters']['available_categories'] as $cat)
                            <option value="{{ $cat }}" {{ $reports['filters']['category'] == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-custom w-100">
                        <i class="fas fa-sync me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card summary">
                    <h6 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Summary Statistics</h6>
                    <div class="metric-item">
                        <span class="metric-label">Total Books</span>
                        <span class="metric-value">{{ number_format($reports['summary']['total_books']) }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Total Stock</span>
                        <span class="metric-value">{{ number_format($reports['summary']['total_stock']) }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Total Value</span>
                        <span class="metric-value success">RM {{ number_format($reports['summary']['total_value'], 2) }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Categories</span>
                        <span class="metric-value">{{ $reports['summary']['unique_categories'] }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card inventory">
                    <h6 class="mb-3"><i class="fas fa-boxes me-2"></i>Stock Status</h6>
                    <div class="metric-item">
                        <span class="metric-label">Out of Stock</span>
                        <span class="metric-value danger">{{ $reports['summary']['out_of_stock'] }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Low Stock (≤10)</span>
                        <span class="metric-value warning">{{ $reports['summary']['low_stock'] }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Medium Stock</span>
                        <span class="metric-value">{{ $reports['inventory']['stock_distribution']['medium_stock'] }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">High Stock (>50)</span>
                        <span class="metric-value success">{{ $reports['inventory']['stock_distribution']['high_stock'] }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card activity">
                    <h6 class="mb-3"><i class="fas fa-activity me-2"></i>Activity Summary</h6>
                    <div class="metric-item">
                        <span class="metric-label">Total Activities</span>
                        <span class="metric-value">{{ $reports['activity']['total_activities'] }}</span>
                    </div>
                    @if(isset($reports['activity']['activity_by_action']['created']))
                    <div class="metric-item">
                        <span class="metric-label">Books Created</span>
                        <span class="metric-value success">{{ $reports['activity']['activity_by_action']['created'] }}</span>
                    </div>
                    @endif
                    @if(isset($reports['activity']['activity_by_action']['updated']))
                    <div class="metric-item">
                        <span class="metric-label">Books Updated</span>
                        <span class="metric-value">{{ $reports['activity']['activity_by_action']['updated'] }}</span>
                    </div>
                    @endif
                    @if(isset($reports['activity']['activity_by_action']['deleted']))
                    <div class="metric-item">
                        <span class="metric-label">Books Deleted</span>
                        <span class="metric-value danger">{{ $reports['activity']['activity_by_action']['deleted'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card categories">
                    <h6 class="mb-3"><i class="fas fa-tags me-2"></i>Top Category</h6>
                    @if($reports['categories']->count() > 0)
                        @php $topCategory = $reports['categories']->first(); @endphp
                        <div class="metric-item">
                            <span class="metric-label">Category</span>
                            <span class="metric-value">{{ $topCategory['name'] }}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Books</span>
                            <span class="metric-value">{{ $topCategory['books_count'] }}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Stock</span>
                            <span class="metric-value">{{ number_format($topCategory['total_stock']) }}</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Value</span>
                            <span class="metric-value success">RM {{ number_format($topCategory['total_value'], 2) }}</span>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No categories available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="chart-title">Stock Distribution</h5>
                    <div class="chart-wrapper">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="chart-title">Category Values</h5>
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Analytics Section -->
        @if(isset($reports['sales']) && $reports['sales']['total_sales'] > 0)
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card" style="border-left-color: #28a745;">
                    <h6 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Sales Summary</h6>
                    <div class="metric-item">
                        <span class="metric-label">Total Sales</span>
                        <span class="metric-value success">{{ $reports['sales']['total_sales'] }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Total Revenue</span>
                        <span class="metric-value success">RM {{ number_format($reports['sales']['total_revenue'], 2) }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Books Sold</span>
                        <span class="metric-value">{{ $reports['sales']['total_quantity'] }}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Avg Sale Value</span>
                        <span class="metric-value">RM {{ number_format($reports['sales']['avg_sale_value'], 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="chart-container">
                    <h5 class="chart-title">Sales by Platform</h5>
                    <div class="chart-wrapper">
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Books -->
        @if($reports['sales']['top_selling_books']->count() > 0)
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-trophy me-2"></i>Top Selling Books</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Book Title</th>
                            <th>Category</th>
                            <th>Quantity Sold</th>
                            <th>Sales Count</th>
                            <th>Total Revenue</th>
                            <th>Avg Sale Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['sales']['top_selling_books'] as $bookSale)
                        <tr>
                            <td><strong>{{ $bookSale['book']->title }}</strong></td>
                            <td>{{ $bookSale['book']->category ?: 'Uncategorized' }}</td>
                            <td><span class="badge bg-primary">{{ $bookSale['total_quantity'] }}</span></td>
                            <td>{{ $bookSale['sales_count'] }}</td>
                            <td><strong class="text-success">RM {{ number_format($bookSale['total_revenue'], 2) }}</strong></td>
                            <td>RM {{ number_format($bookSale['avg_sale_price'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Platform Performance -->
        @if($reports['sales']['sales_by_platform']->count() > 0)
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-store me-2"></i>Platform Performance</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Platform</th>
                            <th>Total Sales</th>
                            <th>Revenue</th>
                            <th>Books Sold</th>
                            <th>Avg Sale Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['sales']['sales_by_platform'] as $platform)
                        <tr>
                            <td>
                                @php
                                    $platformClass = 'platform-' . strtolower(str_replace(' ', '', $platform['platform']));
                                @endphp
                                <span class="platform-badge {{ $platformClass }}">{{ $platform['platform'] }}</span>
                            </td>
                            <td><strong>{{ $platform['total_sales'] }}</strong></td>
                            <td><strong class="text-success">RM {{ number_format($platform['total_revenue'], 2) }}</strong></td>
                            <td>{{ $platform['total_quantity'] }}</td>
                            <td>RM {{ number_format($platform['avg_sale_value'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @else
        <div class="table-container">
            <div class="text-center py-4">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Sales Data</h5>
                <p class="text-muted">No sales recorded for the selected period and filters.</p>
                <a href="{{ route('sales.index') }}" class="btn btn-primary">Start Recording Sales</a>
            </div>
        </div>
        @endif

        <!-- Category Analysis Table -->
        @if($reports['categories']->count() > 0)
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-table me-2"></i>Category Analysis</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Books</th>
                            <th>Total Stock</th>
                            <th>Avg Price</th>
                            <th>Total Value</th>
                            <th>Out of Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['categories'] as $category)
                        <tr>
                            <td><strong>{{ $category['name'] }}</strong></td>
                            <td>{{ $category['books_count'] }}</td>
                            <td>{{ number_format($category['total_stock']) }}</td>
                            <td>RM {{ number_format($category['avg_price'], 2) }}</td>
                            <td><strong>RM {{ number_format($category['total_value'], 2) }}</strong></td>
                            <td>
                                @if($category['out_of_stock_count'] > 0)
                                    <span class="badge bg-danger">{{ $category['out_of_stock_count'] }}</span>
                                @else
                                    <span class="badge bg-success">0</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Top Value Books -->
        @if($reports['inventory']['top_value_books']->count() > 0)
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-star me-2"></i>Top Value Books</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['inventory']['top_value_books'] as $book)
                        <tr>
                            <td><strong>{{ $book->title }}</strong></td>
                            <td>{{ $book->category ?: 'Uncategorized' }}</td>
                            <td>{{ $book->stock }}</td>
                            <td>RM {{ number_format($book->price, 2) }}</td>
                            <td><strong>RM {{ number_format($book->price * $book->stock, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

@push('scripts')
<script>
        // Stock Distribution Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: ['Out of Stock', 'Low Stock (≤10)', 'Medium Stock (11-50)', 'High Stock (>50)'],
                datasets: [{
                    data: [
                        {{ $reports['inventory']['stock_distribution']['out_of_stock'] }},
                        {{ $reports['inventory']['stock_distribution']['low_stock'] }},
                        {{ $reports['inventory']['stock_distribution']['medium_stock'] }},
                        {{ $reports['inventory']['stock_distribution']['high_stock'] }}
                    ],
                    backgroundColor: [
                        '#dc2626',
                        '#d97706',
                        '#059669',
                        '#2563eb'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Category Values Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($reports['categories']->take(10) as $category)
                        '{{ addslashes($category['name']) }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Total Value (RM)',
                    data: [
                        @foreach($reports['categories']->take(10) as $category)
                            {{ $category['total_value'] }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'RM ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Platform Sales Chart (only if sales data exists)
        @if(isset($reports['sales']) && $reports['sales']['total_sales'] > 0 && $reports['sales']['sales_by_platform']->count() > 0)
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach($reports['sales']['sales_by_platform'] as $platform)
                        '{{ addslashes($platform['platform']) }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Revenue (RM)',
                    data: [
                        @foreach($reports['sales']['sales_by_platform'] as $platform)
                            {{ $platform['total_revenue'] }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#ff5722', // Shopee orange
                        '#1565c0', // Lazada blue
                        '#000000', // TikTok black
                        '#4267b2', // Facebook blue
                        '#e4405f', // Instagram pink
                        '#6c757d', // Offline gray
                        '#17a2b8', // Other teal
                        '#ffc107', // Additional yellow
                        '#28a745', // Additional green
                        '#dc3545'  // Additional red
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': RM ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>
@endpush
@endsection