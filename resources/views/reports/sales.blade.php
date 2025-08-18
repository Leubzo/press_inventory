@extends('reports.layout')

@section('page-content')
<!-- Advanced Sales Filters -->
<div class="stats-card sales">
    <h5><i class="fas fa-filter me-2"></i>Sales Analytics Filters</h5>
    <form method="GET" action="{{ route('reports.sales') }}" id="salesFiltersForm">
        <div class="row g-3">
            <div class="col-md-2">
                <label for="sales_date_from" class="form-label fw-semibold">Date From</label>
                <input type="date" class="form-control" id="sales_date_from" name="date_from" 
                       value="{{ $data['filters']['date_from'] }}">
            </div>
            <div class="col-md-2">
                <label for="sales_date_to" class="form-label fw-semibold">Date To</label>
                <input type="date" class="form-control" id="sales_date_to" name="date_to" 
                       value="{{ $data['filters']['date_to'] }}">
            </div>
            <div class="col-md-2">
                <label for="sales_category" class="form-label fw-semibold">Category</label>
                <select class="form-select" id="sales_category" name="category">
                    <option value="">All Categories</option>
                    @foreach($data['filters']['available_categories'] as $cat)
                        <option value="{{ $cat }}" {{ $data['filters']['category'] == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="platform" class="form-label fw-semibold">Platform</label>
                <select class="form-select" id="platform" name="platform">
                    <option value="">All Platforms</option>
                    @foreach($data['filters']['available_platforms'] as $platform)
                        <option value="{{ $platform }}" {{ $data['filters']['platform'] == $platform ? 'selected' : '' }}>
                            {{ $platform }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear All
                    </a>
                    <div class="ms-auto">
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportSales('csv')">
                                    <i class="fas fa-file-csv me-2"></i>Export as CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportSales('pdf')">
                                    <i class="fas fa-file-pdf me-2"></i>Export as PDF
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Smart Book Search for Sales -->
<div class="stats-card sales search-card">
    <h5><i class="fas fa-search me-2"></i>Smart Book Search - Sales Analytics</h5>
    <div class="row">
        <div class="col-md-8">
            <div class="search-container">
                <input type="text" class="form-control" id="salesBookSearchInput" 
                       placeholder="Search by title, ISBN, or author to view sales data..." autocomplete="off">
                <div id="salesSearchSuggestions" class="search-suggestions"></div>
            </div>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-primary w-100" id="salesSearchBookBtn">
                <i class="fas fa-search me-1"></i>View Sales Data
            </button>
        </div>
    </div>
    <div id="salesBookSearchResults" class="mt-3" style="display: none;">
        <div class="alert alert-info">
            <div id="salesBookSearchContent"></div>
        </div>
    </div>
</div>

@if($data['metrics']['total_orders'] > 0)
    <!-- Sales Metrics Dashboard -->
    <div class="stats-card sales">
        <h5><i class="fas fa-chart-line me-2"></i>Sales Performance Overview</h5>
        <div class="metric-grid">
            <div class="metric-item">
                <div>
                    <div class="metric-label">Total Orders</div>
                    <div class="metric-value">{{ number_format($data['metrics']['total_orders']) }}</div>
                </div>
                <i class="fas fa-shopping-cart fa-2x text-primary opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Total Revenue</div>
                    <div class="metric-value text-success">RM {{ number_format($data['metrics']['total_revenue'], 2) }}</div>
                </div>
                <i class="fas fa-dollar-sign fa-2x text-success opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Books Sold</div>
                    <div class="metric-value">{{ number_format($data['metrics']['total_quantity']) }}</div>
                </div>
                <i class="fas fa-book fa-2x text-info opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Avg Order Value</div>
                    <div class="metric-value">RM {{ number_format($data['metrics']['avg_order_value'], 2) }}</div>
                </div>
                <i class="fas fa-calculator fa-2x text-warning opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Unique Books</div>
                    <div class="metric-value">{{ $data['metrics']['unique_books_sold'] }}</div>
                </div>
                <i class="fas fa-layer-group fa-2x text-secondary opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Platforms Used</div>
                    <div class="metric-value">{{ $data['metrics']['platforms_used'] }}</div>
                </div>
                <i class="fas fa-store fa-2x text-info opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Avg Books/Order</div>
                    <div class="metric-value">{{ number_format($data['metrics']['avg_books_per_order'], 1) }}</div>
                </div>
                <i class="fas fa-chart-bar fa-2x text-primary opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Fulfillment Rate</div>
                    <div class="metric-value text-success">{{ number_format($data['metrics']['fulfillment_rate'], 1) }}%</div>
                </div>
                <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Sales Trends Analysis -->
    @if($data['trends']['total_days'] > 1)
    <div class="stats-card sales">
        <h5><i class="fas fa-chart-area me-2"></i>Sales Trends Analysis</h5>
        <div class="row">
            <div class="col-md-8">
                <div class="chart-container">
                    <canvas id="salesTrendsChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-grid">
                    <div class="metric-item">
                        <div>
                            <div class="metric-label">Peak Day Revenue</div>
                            <div class="metric-value">RM {{ number_format($data['trends']['peak_day']['revenue'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="metric-item">
                        <div>
                            <div class="metric-label">Avg Daily Revenue</div>
                            <div class="metric-value">RM {{ number_format($data['trends']['avg_daily_revenue'], 2) }}</div>
                        </div>
                    </div>
                    <div class="metric-item">
                        <div>
                            <div class="metric-label">Active Sales Days</div>
                            <div class="metric-value">{{ $data['trends']['total_days'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Platform Performance Analysis -->
    @if($data['platforms']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-store me-2"></i>Platform Performance Analysis</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="platformPerformanceChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Orders</th>
                                    <th>Revenue (RM)</th>
                                    <th>Market Share</th>
                                    <th>Efficiency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['platforms'] as $platform)
                                    <tr>
                                        <td>
                                            <strong>{{ $platform['platform'] }}</strong>
                                        </td>
                                        <td>{{ number_format($platform['total_orders']) }}</td>
                                        <td class="fw-bold">{{ number_format($platform['total_revenue'], 2) }}</td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $platform['market_share'] }}%"></div>
                                            </div>
                                            <small>{{ number_format($platform['market_share'], 1) }}%</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $platform['efficiency_score'] > $data['metrics']['avg_order_value'] ? 'bg-success' : 'bg-warning' }}">
                                                RM {{ number_format($platform['efficiency_score'], 0) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Best Selling Books -->
    @if($data['best_sellers']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-trophy me-2"></i>Best Selling Books</h5>
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-container">
                        <canvas id="bestSellersChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="table-container">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['best_sellers']->take(8) as $index => $bookSale)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($bookSale['book']->title, 20) }}</strong><br>
                                            <small class="text-muted">{{ $bookSale['book']->category ?: 'Uncategorized' }}</small>
                                        </td>
                                        <td class="fw-bold">{{ number_format($bookSale['total_quantity']) }}</td>
                                        <td class="fw-bold">{{ number_format($bookSale['total_revenue'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Detailed Best Sellers Table -->
    <div class="stats-card sales">
        <h5><i class="fas fa-list me-2"></i>Detailed Sales Performance</h5>
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Category</th>
                        <th>Quantity Sold</th>
                        <th>Orders</th>
                        <th>Total Revenue (RM)</th>
                        <th>Avg Sale Price (RM)</th>
                        <th>Velocity Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['best_sellers'] as $index => $bookSale)
                        <tr>
                            <td>
                                <strong>{{ $bookSale['book']->title }}</strong><br>
                                <small class="text-muted">{{ $bookSale['book']->isbn }}</small>
                            </td>
                            <td>{{ $bookSale['book']->category ?: 'Uncategorized' }}</td>
                            <td class="fw-bold">{{ number_format($bookSale['total_quantity']) }}</td>
                            <td>{{ $bookSale['orders_count'] }}</td>
                            <td class="fw-bold">{{ number_format($bookSale['total_revenue'], 2) }}</td>
                            <td>{{ number_format($bookSale['avg_sale_price'], 2) }}</td>
                            <td>
                                <div class="progress" style="height: 6px;">
                                    @php
                                        $maxVelocity = $data['best_sellers']->max('velocity');
                                        $velocityPercent = $maxVelocity > 0 ? ($bookSale['velocity'] / $maxVelocity) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $velocityPercent }}%"></div>
                                </div>
                                <small>{{ $bookSale['velocity'] }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    @if($data['recent_orders']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-clock me-2"></i>Recent Fulfilled Orders</h5>
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Platform</th>
                            <th>Items</th>
                            <th>Total Value (RM)</th>
                            <th>Fulfilled Date</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['recent_orders'] as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $order->platform }}</span>
                                </td>
                                <td>{{ $order->orderItems->count() }} items</td>
                                <td class="fw-bold">{{ number_format($order->total_value, 2) }}</td>
                                <td>{{ $order->fulfillment_date->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($order->total_value > $data['metrics']['avg_order_value'])
                                        <span class="badge bg-success">Above Average</span>
                                    @else
                                        <span class="badge bg-secondary">Standard</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@else
    <!-- No Sales Data State -->
    <div class="stats-card sales">
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">No Sales Data Available</h4>
            <p class="text-muted mb-4">No fulfilled orders found for the selected period and filters.<br>Orders must be fulfilled to appear in sales analytics.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('orders.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create New Order
                </a>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-filter me-1"></i>Adjust Filters
                </a>
            </div>
        </div>
    </div>
@endif

@push('page-scripts')
<script>
console.log('Sales data:', @json($data));

// Chart color scheme
const salesColors = {
    primary: '#fa709a',
    secondary: '#fee140',
    success: '#4facfe',
    warning: '#f77062',
    danger: '#fe5196',
    info: '#00f2fe'
};

@if($data['metrics']['total_orders'] > 0)

// Sales Trends Chart
@if($data['trends']['total_days'] > 1)
const trendsCtx = document.getElementById('salesTrendsChart').getContext('2d');
const trendsData = @json($data['trends']['daily_sales']);

if (trendsData && Object.keys(trendsData).length > 0) {
    const dates = Object.keys(trendsData).sort();
    const revenues = dates.map(date => trendsData[date].revenue);
    const quantities = dates.map(date => trendsData[date].quantity);
    
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-MY', { month: 'short', day: 'numeric' })),
            datasets: [
                {
                    label: 'Daily Revenue (RM)',
                    data: revenues,
                    borderColor: salesColors.primary,
                    backgroundColor: salesColors.primary + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Books Sold',
                    data: quantities,
                    borderColor: salesColors.info,
                    backgroundColor: salesColors.info + '20',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue (RM)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Books Sold'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return 'Revenue: RM ' + context.parsed.y.toLocaleString();
                            } else {
                                return 'Books Sold: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        }
    });
}
@endif

// Platform Performance Chart
@if($data['platforms']->count() > 0)
const platformCtx = document.getElementById('platformPerformanceChart').getContext('2d');
const platformData = @json($data['platforms']->values());

if (platformData && platformData.length > 0) {
    new Chart(platformCtx, {
        type: 'doughnut',
        data: {
            labels: platformData.map(p => p.platform),
            datasets: [{
                data: platformData.map(p => p.total_revenue),
                backgroundColor: [
                    salesColors.primary,
                    salesColors.secondary,
                    salesColors.success,
                    salesColors.warning,
                    salesColors.info
                ],
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': RM ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}
@endif

// Best Sellers Chart
@if($data['best_sellers']->count() > 0)
const bestSellersCtx = document.getElementById('bestSellersChart').getContext('2d');
const bestSellersData = @json($data['charts']['best_sellers_chart']->values());

if (bestSellersData && bestSellersData.length > 0) {
    new Chart(bestSellersCtx, {
        type: 'bar',
        data: {
            labels: bestSellersData.map(book => {
                return book.book.title.length > 20 ? book.book.title.substring(0, 20) + '...' : book.book.title;
            }),
            datasets: [{
                label: 'Quantity Sold',
                data: bestSellersData.map(book => book.total_quantity),
                backgroundColor: salesColors.secondary,
                borderColor: salesColors.primary,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity Sold'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Sold: ' + context.parsed.x + ' units';
                        }
                    }
                }
            }
        }
    });
}
@endif

@endif

// Smart Search Functionality for Sales
const salesSearchInput = document.getElementById('salesBookSearchInput');
const salesSearchSuggestions = document.getElementById('salesSearchSuggestions');

const debouncedSalesSearch = ReportsUtils.debounce(function(query) {
    if (query.length < 2) {
        salesSearchSuggestions.style.display = 'none';
        return;
    }
    
    fetch(`{{ route('reports.autocomplete') }}?query=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                salesSearchSuggestions.innerHTML = data.map(book => `
                    <div class="search-suggestion" onclick="selectSalesBook(${book.id}, '${book.display}')">
                        <strong>${book.title}</strong><br>
                        <small class="text-muted">${book.isbn} • ${book.category} • Stock: ${book.stock}</small>
                    </div>
                `).join('');
                salesSearchSuggestions.style.display = 'block';
            } else {
                salesSearchSuggestions.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Sales search error:', error);
            salesSearchSuggestions.style.display = 'none';
        });
}, 300);

salesSearchInput.addEventListener('input', function() {
    debouncedSalesSearch(this.value);
});

salesSearchInput.addEventListener('blur', function() {
    setTimeout(() => {
        salesSearchSuggestions.style.display = 'none';
    }, 200);
});

function selectSalesBook(bookId, display) {
    salesSearchInput.value = display;
    salesSearchSuggestions.style.display = 'none';
    showSalesBookDetails(bookId);
}

function showSalesBookDetails(bookId) {
    const currentFilters = new URLSearchParams(window.location.search);
    
    ReportsUtils.showLoading('salesBookSearchContent');
    document.getElementById('salesBookSearchResults').style.display = 'block';
    
    fetch(`{{ route('reports.book-details', ':id') }}`.replace(':id', bookId) + '?' + currentFilters.toString(), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const book = data.book;
            const salesSummary = data.sales_summary;
            
            let content = `
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary">Book Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Title:</strong></td><td>${book.title}</td></tr>
                            <tr><td><strong>ISBN:</strong></td><td>${book.isbn}</td></tr>
                            <tr><td><strong>Category:</strong></td><td>${book.category}</td></tr>
                            <tr><td><strong>Current Stock:</strong></td><td><span class="badge bg-${book.current_stock == 0 ? 'danger' : book.current_stock <= 10 ? 'warning' : 'success'}">${book.current_stock}</span></td></tr>
                            <tr><td><strong>Unit Price:</strong></td><td>RM ${parseFloat(book.current_price).toFixed(2)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-8">
                        <h6 class="text-success">Sales Performance (Current Period)</h6>
            `;
            
            if (salesSummary.has_sales) {
                content += `
                        <div class="row">
                            <div class="col-md-3">
                                <div class="metric-item">
                                    <div class="metric-label">Total Sold</div>
                                    <div class="metric-value text-success">${salesSummary.total_quantity}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-item">
                                    <div class="metric-label">Revenue</div>
                                    <div class="metric-value text-success">RM ${parseFloat(salesSummary.total_revenue).toFixed(2)}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-item">
                                    <div class="metric-label">Orders</div>
                                    <div class="metric-value">${salesSummary.orders_count}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-item">
                                    <div class="metric-label">Avg Price</div>
                                    <div class="metric-value">RM ${parseFloat(salesSummary.avg_sale_price).toFixed(2)}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6 class="text-info">Sales Analysis</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Sales Velocity:</strong> ${salesSummary.velocity} units in period</p>
                                    <p><strong>Last Sale:</strong> ${new Date(salesSummary.last_sale_date).toLocaleDateString()}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Performance:</strong> 
                                        <span class="badge ${salesSummary.total_quantity > 5 ? 'bg-success' : salesSummary.total_quantity > 0 ? 'bg-warning' : 'bg-danger'}">
                                            ${salesSummary.total_quantity > 5 ? 'High Performer' : salesSummary.total_quantity > 0 ? 'Moderate Sales' : 'No Sales'}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                `;
            } else {
                content += `
                        <div class="alert alert-warning">
                            <strong>No Sales Data</strong><br>
                            This book has no recorded sales in the current filtered period.
                        </div>
                `;
            }
            
            content += `
                    </div>
                </div>
            `;
            
            document.getElementById('salesBookSearchContent').innerHTML = content;
        })
        .catch(error => {
            console.error('Error loading sales book details:', error);
            document.getElementById('salesBookSearchContent').innerHTML = `<div class="alert alert-danger">Error loading book sales details: ${error.message}</div>`;
        });
}

// Sales search button functionality
document.getElementById('salesSearchBookBtn').addEventListener('click', function() {
    const query = salesSearchInput.value.trim();
    if (!query) {
        alert('Please enter a search term');
        return;
    }
    
    fetch(`{{ route('reports.autocomplete') }}?query=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                showSalesBookDetails(data[0].id);
            } else {
                alert('No books found matching your search.');
            }
        })
        .catch(error => {
            console.error('Sales search error:', error);
            alert('Search error occurred. Please try again.');
        });
});

// Allow Enter key to trigger sales search
salesSearchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('salesSearchBookBtn').click();
    }
});

// Export functionality
function exportSales(format) {
    const currentFilters = new URLSearchParams(window.location.search);
    currentFilters.set('format', format);
    
    window.location.href = `{{ route('reports.export.sales') }}?${currentFilters.toString()}`;
}

// Date range presets
function setDateRange(preset) {
    const today = new Date();
    const dateFrom = document.getElementById('sales_date_from');
    const dateTo = document.getElementById('sales_date_to');
    
    dateTo.value = today.toISOString().split('T')[0];
    
    switch(preset) {
        case 'today':
            dateFrom.value = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            dateFrom.value = yesterday.toISOString().split('T')[0];
            dateTo.value = yesterday.toISOString().split('T')[0];
            break;
        case 'last_7_days':
            const week = new Date(today);
            week.setDate(week.getDate() - 7);
            dateFrom.value = week.toISOString().split('T')[0];
            break;
        case 'last_30_days':
            const month = new Date(today);
            month.setDate(month.getDate() - 30);
            dateFrom.value = month.toISOString().split('T')[0];
            break;
        case 'six_months':
            const sixMonths = new Date(today);
            sixMonths.setMonth(sixMonths.getMonth() - 6);
            dateFrom.value = sixMonths.toISOString().split('T')[0];
            break;
        case 'one_year':
            const oneYear = new Date(today);
            oneYear.setFullYear(oneYear.getFullYear() - 1);
            dateFrom.value = oneYear.toISOString().split('T')[0];
            break;
    }
}

// Add preset buttons to the form
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('sales_date_from');
    const presetContainer = document.createElement('div');
    presetContainer.className = 'mt-2';
    presetContainer.innerHTML = `
        <small class="text-muted d-block mb-1">Quick Presets:</small>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_7_days')">7d</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_30_days')">30d</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('six_months')">6 Months</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('one_year')">1 Year</button>
        </div>
    `;
    
    dateFromInput.parentNode.appendChild(presetContainer);
});
</script>
@endpush
@endsection