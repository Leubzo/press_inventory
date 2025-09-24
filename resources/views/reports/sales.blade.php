@extends('reports.layout')

@section('page-content')
<!-- Time Period Filters -->
<div class="stats-card sales">
    <h5><i class="fas fa-calendar me-2"></i>Time Period</h5>
    <form method="GET" action="{{ route('reports.sales') }}" id="salesFiltersForm">
        <!-- Date Range -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="date_from" class="form-label fw-semibold">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $data['filters']['date_from'] }}">
            </div>
            <div class="col-md-6">
                <label for="date_to" class="form-label fw-semibold">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $data['filters']['date_to'] }}">
            </div>
        </div>

        <!-- Quick Date Presets -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Quick Select:</label>
                <div class="btn-group flex-wrap" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setSalesDateRange('today')">Today</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setSalesDateRange('7_days')">7 Days</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setSalesDateRange('1_month')">1 Month</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setSalesDateRange('3_months')">3 Months</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setSalesDateRange('6_months')">6 Months</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setSalesDateRange('1_year')">1 Year</button>
                </div>
            </div>
        </div>

        <!-- Additional Filters -->
        <div class="row g-3">
            <div class="col-md-4">
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
            <div class="col-md-8">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear All
                    </a>
                    <a href="{{ route('reports.export.sales', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-file-csv me-1"></i>Export CSV
                    </a>
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
                    <div class="metric-label">Total Items</div>
                    <div class="metric-value">{{ number_format($data['metrics']['total_items']) }}</div>
                </div>
                <i class="fas fa-layer-group fa-2x text-secondary opacity-50"></i>
            </div>
            <div class="metric-item">
                <div>
                    <div class="metric-label">Avg Items/Order</div>
                    <div class="metric-value">{{ number_format($data['metrics']['avg_items_per_order'], 1) }}</div>
                </div>
                <i class="fas fa-chart-bar fa-2x text-primary opacity-50"></i>
            </div>
        </div>
    </div>



    <!-- Best Selling Books -->
    @if($data['best_sellers']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-trophy me-2"></i>Best Selling Books</h5>
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Book Title</th>
                            <th>Category</th>
                            <th>Quantity Sold</th>
                            <th>Orders</th>
                            <th>Revenue (RM)</th>
                            <th>Avg Sale Price (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startRank = ($data['best_sellers']->currentPage() - 1) * $data['best_sellers']->perPage() + 1;
                        @endphp
                        @foreach($data['best_sellers'] as $index => $bookSale)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($startRank + $index <= 3)
                                            <i class="fas fa-medal {{ $startRank + $index == 1 ? 'text-warning' : ($startRank + $index == 2 ? 'text-secondary' : 'text-warning') }} me-2"></i>
                                        @endif
                                        <span class="fw-bold">{{ $startRank + $index }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $bookSale['book']->title }}</strong><br>
                                        <small class="text-muted">ISBN: {{ $bookSale['book']->isbn }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $bookSale['book']->category ?: 'Uncategorized' }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ number_format($bookSale['total_quantity']) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format($bookSale['orders_count']) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($bookSale['total_revenue'], 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ number_format($bookSale['avg_sale_price'], 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $data['best_sellers']->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="stats-card sales">
            <div class="text-center py-4">
                <i class="fas fa-chart-line text-muted fa-3x mb-3"></i>
                <h5 class="text-muted">No sales data available</h5>
                <p class="text-muted">No sales found for the selected period.</p>
            </div>
        </div>
    @endif

    <!-- Sales Activities -->
    <div class="stats-card sales">
        <h5><i class="fas fa-chart-line me-2"></i>Sales Activities</h5>
        <div class="text-muted small mb-3">
            <i class="fas fa-calendar me-1"></i>Filtering: {{ $data['filters']['date_from'] }} to {{ $data['filters']['date_to'] }}
        </div>

        <!-- Summary Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-item">
                    <div>
                        <div class="metric-label">Orders Created</div>
                        <div class="metric-value text-info">{{ $data['sales_changes']['summary']['orders_created'] }}</div>
                    </div>
                    <i class="fas fa-plus-circle fa-2x text-info opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-item">
                    <div>
                        <div class="metric-label">Orders Approved</div>
                        <div class="metric-value text-warning">{{ $data['sales_changes']['summary']['orders_approved'] }}</div>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-warning opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-item">
                    <div>
                        <div class="metric-label">Orders Fulfilled</div>
                        <div class="metric-value text-success">{{ $data['sales_changes']['summary']['orders_fulfilled'] }}</div>
                    </div>
                    <i class="fas fa-shipping-fast fa-2x text-success opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-item">
                    <div>
                        <div class="metric-label">Total Revenue</div>
                        <div class="metric-value text-success">RM {{ number_format($data['sales_changes']['summary']['total_revenue'], 2) }}</div>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>

        @if(!$data['sales_changes']['orders_created']->isEmpty() || !$data['sales_changes']['orders_fulfilled']->isEmpty())
        <div class="row">
            @if(!$data['sales_changes']['orders_created']->isEmpty())
            <div class="col-md-6">
                <h6><i class="fas fa-plus me-2 text-info"></i>Recently Created Orders</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Requester</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['sales_changes']['orders_created']->take(5) as $order)
                            <tr>
                                <td class="font-monospace">{{ $order['order_number'] }}</td>
                                <td>{{ \Str::limit($order['requester_name'], 20) }}</td>
                                <td>{{ $order['items_count'] }}</td>
                                <td>
                                    <span class="badge bg-{{ $order['status'] == 'pending' ? 'warning' : ($order['status'] == 'approved' ? 'info' : 'success') }}">
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $order['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(!$data['sales_changes']['orders_fulfilled']->isEmpty())
            <div class="col-md-6">
                <h6><i class="fas fa-check me-2 text-success"></i>Recently Fulfilled Orders</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Fulfiller</th>
                                <th>Items</th>
                                <th>Value (RM)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['sales_changes']['orders_fulfilled']->take(5) as $order)
                            <tr>
                                <td class="font-monospace">{{ $order['order_number'] }}</td>
                                <td>{{ \Str::limit($order['fulfiller_name'], 20) }}</td>
                                <td>{{ $order['items_count'] }}</td>
                                <td class="fw-bold text-success">{{ number_format($order['total_value'], 2) }}</td>
                                <td class="text-muted small">{{ $order['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Sales Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="stats-card sales">
                <h5><i class="fas fa-chart-line me-2"></i>Sales Trend</h5>
                <div class="chart-container">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stats-card sales">
                <h5><i class="fas fa-chart-pie me-2"></i>Sales by Category</h5>
                <div class="chart-container">
                    <canvas id="categorySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

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

// Sales Trend Chart
const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
const salesTrendData = @json($data['charts']['sales_trend']->values());

// Determine chart configuration based on data type
const dataType = salesTrendData.length > 0 ? salesTrendData[0].type : 'daily';
const dataCount = salesTrendData.length;

// Configure chart based on aggregation type
const chartConfig = {
    type: dataCount > 30 ? 'bar' : 'line',
    data: {
        labels: salesTrendData.map(item => {
            const date = new Date(item.date);
            switch(item.type) {
                case 'daily':
                    return date.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' });
                case 'weekly':
                    const endWeek = new Date(date);
                    endWeek.setDate(endWeek.getDate() + 6);
                    return date.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' }) + ' - ' +
                           endWeek.toLocaleDateString('en-GB', { day: 'numeric' });
                case 'monthly':
                    return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
                default:
                    return date.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' });
            }
        }),
        datasets: [{
            label: dataType === 'daily' ? 'Daily Revenue (RM)' :
                   dataType === 'weekly' ? 'Weekly Revenue (RM)' : 'Monthly Revenue (RM)',
            data: salesTrendData.map(item => item.revenue),
            borderColor: salesColors.primary,
            backgroundColor: dataCount > 30 ? salesColors.primary : salesColors.primary + '20',
            borderWidth: 3,
            fill: dataCount <= 30,
            tension: dataType === 'daily' ? 0.4 : 0.2,
            pointBackgroundColor: salesColors.primary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: dataCount > 50 ? 0 : dataCount > 30 ? 2 : 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'RM ' + value.toLocaleString();
                    }
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    maxTicksLimit: dataCount > 30 ? 8 : dataCount > 15 ? 12 : undefined,
                    maxRotation: dataType === 'weekly' ? 45 : 0
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'start',
                labels: {
                    boxWidth: 12,
                    padding: 10
                }
            },
            tooltip: {
                callbacks: {
                    title: function(context) {
                        const item = salesTrendData[context[0].dataIndex];
                        const date = new Date(item.date);
                        switch(item.type) {
                            case 'daily':
                                return date.toLocaleDateString('en-GB', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                });
                            case 'weekly':
                                const endWeek = new Date(date);
                                endWeek.setDate(endWeek.getDate() + 6);
                                return 'Week: ' + date.toLocaleDateString('en-GB') + ' - ' + endWeek.toLocaleDateString('en-GB');
                            case 'monthly':
                                return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
                        }
                    },
                    label: function(context) {
                        const item = salesTrendData[context.dataIndex];
                        return [
                            'Revenue: RM ' + context.parsed.y.toLocaleString(),
                            'Orders: ' + item.orders
                        ];
                    }
                }
            }
        }
    }
};

new Chart(salesTrendCtx, chartConfig);

// Category Sales Chart
const categorySalesCtx = document.getElementById('categorySalesChart').getContext('2d');
const categorySalesData = @json($data['charts']['category_sales']->take(6)->values());

if (categorySalesData && categorySalesData.length > 0) {
    new Chart(categorySalesCtx, {
        type: 'doughnut',
        data: {
            labels: categorySalesData.map(cat => cat.category),
            datasets: [{
                data: categorySalesData.map(cat => cat.revenue),
                backgroundColor: [
                    salesColors.primary,
                    salesColors.secondary,
                    salesColors.success,
                    salesColors.warning,
                    salesColors.danger,
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

// Date Range Preset Functions for Sales
function setSalesDateRange(rangeType) {
    const today = new Date();
    const fromInput = document.getElementById('date_from');
    const toInput = document.getElementById('date_to');

    let fromDate = new Date();
    let toDate = new Date(today);

    switch(rangeType) {
        case 'today':
            fromDate = new Date(today);
            break;
        case '7_days':
            fromDate.setDate(today.getDate() - 7);
            break;
        case '1_month':
            fromDate.setMonth(today.getMonth() - 1);
            break;
        case '3_months':
            fromDate.setMonth(today.getMonth() - 3);
            break;
        case '6_months':
            fromDate.setMonth(today.getMonth() - 6);
            break;
        case '1_year':
            fromDate.setFullYear(today.getFullYear() - 1);
            break;
    }

    // Format dates as YYYY-MM-DD for date inputs
    fromInput.value = fromDate.toISOString().split('T')[0];
    toInput.value = toDate.toISOString().split('T')[0];
}

</script>
@endpush
@endsection