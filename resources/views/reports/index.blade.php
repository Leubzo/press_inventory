@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
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
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('books.index') }}">
                <i class="fas fa-books me-2"></i>
                UUM Press Book Inventory
            </a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('books.index') }}" class="btn btn-light btn-sm me-2">
                    <i class="fas fa-book me-1"></i> Books
                </a>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-light btn-sm me-2">
                    <i class="fas fa-history me-1"></i> Audit Logs
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-container">
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
                    <a href="{{ route('books.index') }}" class="btn btn-primary btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>Back to Books
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>
@endsection