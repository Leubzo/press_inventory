@extends('reports.layout')

@section('page-content')
<!-- Advanced Filters Section -->
<div class="stats-card inventory">
    <h5><i class="fas fa-filter me-2"></i>Advanced Filters</h5>
    <form method="GET" action="{{ route('reports.inventory') }}" id="inventoryFiltersForm">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label fw-semibold">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ $data['filters']['date_from'] }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label fw-semibold">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ $data['filters']['date_to'] }}">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label fw-semibold">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    @foreach($data['filters']['available_categories'] as $cat)
                        <option value="{{ $cat }}" {{ $data['filters']['category'] == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="stock_level" class="form-label fw-semibold">Stock Level</label>
                <select class="form-select" id="stock_level" name="stock_level">
                    <option value="">All Stock Levels</option>
                    @foreach($data['filters']['available_stock_levels'] as $key => $label)
                        <option value="{{ $key }}" {{ $data['filters']['stock_level'] == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear All
                    </a>
                    <div class="ms-auto">
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportInventory('csv')">
                                    <i class="fas fa-file-csv me-2"></i>Export as CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportInventory('pdf')">
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

<!-- Key Metrics Dashboard -->
<div class="stats-card inventory">
    <h5><i class="fas fa-chart-pie me-2"></i>Inventory Overview</h5>
    <div class="metric-grid">
        <div class="metric-item">
            <div>
                <div class="metric-label">Total Books</div>
                <div class="metric-value">{{ number_format($data['metrics']['total_books']) }}</div>
            </div>
            <i class="fas fa-book fa-2x text-primary opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Total Stock</div>
                <div class="metric-value">{{ number_format($data['metrics']['total_stock']) }}</div>
            </div>
            <i class="fas fa-boxes fa-2x text-info opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Total Value</div>
                <div class="metric-value">RM {{ number_format($data['metrics']['total_value'], 2) }}</div>
            </div>
            <i class="fas fa-dollar-sign fa-2x text-success opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Categories</div>
                <div class="metric-value">{{ $data['metrics']['unique_categories'] }}</div>
            </div>
            <i class="fas fa-tags fa-2x text-secondary opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Out of Stock</div>
                <div class="metric-value text-danger">{{ $data['metrics']['out_of_stock'] }}</div>
            </div>
            <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Low Stock</div>
                <div class="metric-value text-warning">{{ $data['metrics']['low_stock'] }}</div>
            </div>
            <i class="fas fa-exclamation-circle fa-2x text-warning opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Avg Value/Book</div>
                <div class="metric-value">RM {{ number_format($data['metrics']['avg_value_per_book'], 2) }}</div>
            </div>
            <i class="fas fa-calculator fa-2x text-info opacity-50"></i>
        </div>
        <div class="metric-item">
            <div>
                <div class="metric-label">Avg Stock/Book</div>
                <div class="metric-value">{{ number_format($data['metrics']['avg_stock_per_book'], 1) }}</div>
            </div>
            <i class="fas fa-chart-bar fa-2x text-primary opacity-50"></i>
        </div>
    </div>
</div>


<!-- Smart Book Search -->
<div class="stats-card inventory search-card">
    <h5><i class="fas fa-search me-2"></i>Smart Book Search</h5>
    <div class="row">
        <div class="col-md-8">
            <div class="search-container">
                <input type="text" class="form-control" id="bookSearchInput" 
                       placeholder="Search by title, ISBN, or author..." autocomplete="off">
                <div id="searchSuggestions" class="search-suggestions"></div>
            </div>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-primary w-100" id="searchBookBtn">
                <i class="fas fa-search me-1"></i>Search Book
            </button>
        </div>
    </div>
    <div id="bookSearchResults" class="mt-3" style="display: none;">
        <div class="alert alert-info">
            <div id="bookSearchContent"></div>
        </div>
    </div>
</div>

<!-- Interactive Charts Section -->
<div class="row">
    <div class="col-md-6">
        <div class="stats-card inventory">
            <h5><i class="fas fa-chart-pie me-2"></i>Stock Distribution</h5>
            <div class="chart-container">
                <canvas id="stockDistributionChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card inventory">
            <h5><i class="fas fa-chart-bar me-2"></i>Value by Category</h5>
            <div class="chart-container">
                <canvas id="categoryValueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Value Books -->
<div class="stats-card inventory">
    <h5><i class="fas fa-crown me-2"></i>Top Value Books</h5>
    <div class="row">
        <div class="col-md-8">
            <div class="chart-container">
                <canvas id="topValueBooksChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="table-container">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Value (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['value_analysis']['top_value_books']->take(8) as $index => $book)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($book->title, 20) }}</strong><br>
                                    <small class="text-muted">Stock: {{ $book->stock }}</small>
                                </td>
                                <td class="fw-bold">{{ number_format($book->price * $book->stock, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Category Analysis -->
<div class="stats-card inventory">
    <h5><i class="fas fa-tags me-2"></i>Category Performance Analysis</h5>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Books</th>
                    <th>Total Stock</th>
                    <th>Avg Price (RM)</th>
                    <th>Total Value (RM)</th>
                    <th>Alerts</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['categories'] as $category)
                    <tr>
                        <td>
                            <strong>{{ $category['name'] }}</strong>
                            <br><small class="text-muted">{{ number_format($category['avg_stock_per_book'], 1) }} avg stock/book</small>
                        </td>
                        <td>{{ number_format($category['books_count']) }}</td>
                        <td>{{ number_format($category['total_stock']) }}</td>
                        <td>{{ number_format($category['avg_price'], 2) }}</td>
                        <td class="fw-bold">{{ number_format($category['total_value'], 2) }}</td>
                        <td>
                            @if($category['out_of_stock_count'] > 0)
                                <span class="badge bg-danger">{{ $category['out_of_stock_count'] }} out</span>
                            @endif
                            @if($category['low_stock_count'] > 0)
                                <span class="badge bg-warning">{{ $category['low_stock_count'] }} low</span>
                            @endif
                            @if($category['out_of_stock_count'] == 0 && $category['low_stock_count'] == 0)
                                <span class="badge bg-success">Good</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $performance = $category['total_value'] / max($data['categories']->max('total_value'), 1) * 100;
                            @endphp
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ $performance }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format($performance, 1) }}%</small>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Dead Stock Analysis -->
@if($data['dead_stock']['count'] > 0)
<div class="stats-card inventory">
    <h5><i class="fas fa-skull me-2"></i>Dead Stock Analysis 
        <span class="badge bg-warning">{{ $data['dead_stock']['count'] }} items</span>
    </h5>
    <div class="alert alert-warning">
        <strong>Total Dead Stock Value:</strong> RM {{ number_format($data['dead_stock']['total_value'], 2) }}
        <br><small>Books with no sales in the last 90 days</small>
    </div>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Unit Price (RM)</th>
                    <th>Total Value (RM)</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['dead_stock']['books'] as $book)
                    <tr>
                        <td>
                            <strong>{{ $book->title }}</strong><br>
                            <small class="text-muted">{{ $book->isbn }}</small>
                        </td>
                        <td>{{ $book->category ?: 'Uncategorized' }}</td>
                        <td>{{ $book->stock }}</td>
                        <td>{{ number_format($book->price, 2) }}</td>
                        <td class="fw-bold">{{ number_format($book->price * $book->stock, 2) }}</td>
                        <td>
                            <span class="badge bg-secondary">Consider promotion</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Book Detail Modal -->
<div class="modal fade" id="bookDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Details & Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookDetailContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script>
console.log('Inventory data:', @json($data));

// Chart color scheme
const colors = {
    primary: '#667eea',
    secondary: '#764ba2',
    success: '#4facfe',
    warning: '#fee140',
    danger: '#f77062',
    info: '#00f2fe'
};

// Stock Distribution Chart
const stockDistCtx = document.getElementById('stockDistributionChart').getContext('2d');
new Chart(stockDistCtx, {
    type: 'doughnut',
    data: {
        labels: ['Out of Stock', 'Low Stock (1-10)', 'Medium Stock (11-50)', 'High Stock (50+)'],
        datasets: [{
            data: [
                {{ $data['stock_distribution']['out_of_stock'] }},
                {{ $data['stock_distribution']['low_stock'] }},
                {{ $data['stock_distribution']['medium_stock'] }},
                {{ $data['stock_distribution']['high_stock'] }}
            ],
            backgroundColor: [colors.danger, colors.warning, colors.info, colors.success],
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
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '60%'
    }
});

// Category Value Chart
const categoryCtx = document.getElementById('categoryValueChart').getContext('2d');
const categoryData = @json($data['charts']['category_values']->values());

if (categoryData && categoryData.length > 0) {
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.map(cat => cat.name),
            datasets: [{
                label: 'Total Value (RM)',
                data: categoryData.map(cat => cat.total_value),
                backgroundColor: colors.primary,
                borderColor: colors.secondary,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
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
                            return 'Value: RM ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Top Value Books Chart
const topBooksCtx = document.getElementById('topValueBooksChart').getContext('2d');
const topBooksData = @json($data['charts']['top_value_books']->values());

if (topBooksData && topBooksData.length > 0) {
    new Chart(topBooksCtx, {
        type: 'bar',
        data: {
            labels: topBooksData.map(book => {
                return book.title.length > 25 ? book.title.substring(0, 25) + '...' : book.title;
            }),
            datasets: [{
                label: 'Total Value (RM)',
                data: topBooksData.map(book => book.price * book.stock),
                backgroundColor: colors.warning,
                borderColor: colors.danger,
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
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
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
                            return 'Value: RM ' + context.parsed.x.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Smart Search Functionality
let searchTimeout;
const searchInput = document.getElementById('bookSearchInput');
const searchSuggestions = document.getElementById('searchSuggestions');

const debouncedSearch = ReportsUtils.debounce(function(query) {
    if (query.length < 2) {
        searchSuggestions.style.display = 'none';
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
                searchSuggestions.innerHTML = data.map(book => `
                    <div class="search-suggestion" onclick="selectBook(${book.id}, '${book.display}')">
                        <strong>${book.title}</strong><br>
                        <small class="text-muted">${book.isbn} • ${book.category} • Stock: ${book.stock}</small>
                    </div>
                `).join('');
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            searchSuggestions.style.display = 'none';
        });
}, 300);

searchInput.addEventListener('input', function() {
    debouncedSearch(this.value);
});

searchInput.addEventListener('blur', function() {
    setTimeout(() => {
        searchSuggestions.style.display = 'none';
    }, 200);
});

function selectBook(bookId, display) {
    searchInput.value = display;
    searchSuggestions.style.display = 'none';
    showBookDetails(bookId);
}

function showBookDetails(bookId) {
    const currentFilters = new URLSearchParams(window.location.search);
    
    ReportsUtils.showLoading('bookDetailContent');
    
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
            const stockHistory = data.stock_history;
            const salesSummary = data.sales_summary;
            const recommendations = data.recommendations;
            
            document.getElementById('bookDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Book Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Title:</strong></td><td>${book.title}</td></tr>
                            <tr><td><strong>ISBN:</strong></td><td>${book.isbn}</td></tr>
                            <tr><td><strong>Category:</strong></td><td>${book.category}</td></tr>
                            <tr><td><strong>Authors/Editors:</strong></td><td>${book.authors_editors || 'N/A'}</td></tr>
                            <tr><td><strong>Year:</strong></td><td>${book.year || 'N/A'}</td></tr>
                            <tr><td><strong>Created:</strong></td><td>${book.created_at}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success">Current Status</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Current Stock:</strong></td><td><span class="badge bg-${book.current_stock == 0 ? 'danger' : book.current_stock <= 10 ? 'warning' : 'success'}">${book.current_stock}</span></td></tr>
                            <tr><td><strong>Status:</strong></td><td>${book.stock_status}</td></tr>
                            <tr><td><strong>Unit Price:</strong></td><td>RM ${parseFloat(book.current_price).toFixed(2)}</td></tr>
                            <tr><td><strong>Total Value:</strong></td><td><strong>RM ${parseFloat(book.total_value).toFixed(2)}</strong></td></tr>
                        </table>
                        
                        ${recommendations.recommended ? `
                            <div class="alert alert-warning">
                                <strong>Reorder Recommended</strong><br>
                                ${recommendations.reason}<br>
                                <small>Suggested quantity: ${recommendations.suggested_quantity || 'N/A'}</small>
                            </div>
                        ` : `
                            <div class="alert alert-success">
                                <strong>Stock Status: Good</strong><br>
                                ${recommendations.reason}
                            </div>
                        `}
                    </div>
                </div>
                
                ${salesSummary.has_sales ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-info">Sales Summary (Current Period)</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="metric-item">
                                        <div class="metric-label">Total Sold</div>
                                        <div class="metric-value">${salesSummary.total_quantity}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-item">
                                        <div class="metric-label">Revenue</div>
                                        <div class="metric-value">RM ${parseFloat(salesSummary.total_revenue).toFixed(2)}</div>
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
                        </div>
                    </div>
                ` : '<div class="alert alert-info mt-3">No sales recorded in the current period.</div>'}
                
                ${stockHistory.length > 0 ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-secondary">Recent Stock Changes</h6>
                            <div class="table-container">
                                <table class="table table-sm">
                                    <thead>
                                        <tr><th>Date</th><th>Action</th><th>Changes</th></tr>
                                    </thead>
                                    <tbody>
                                        ${stockHistory.map(log => `
                                            <tr>
                                                <td>${log.date}</td>
                                                <td><span class="badge bg-secondary">${log.action}</span></td>
                                                <td>${log.changes ? JSON.stringify(log.changes) : 'N/A'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                ` : ''}
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('bookDetailModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error loading book details:', error);
            document.getElementById('bookDetailContent').innerHTML = `<div class="alert alert-danger">Error loading book details: ${error.message}</div>`;
        });
}

// Export functionality
function exportInventory(format) {
    const currentFilters = new URLSearchParams(window.location.search);
    currentFilters.set('format', format);
    
    window.location.href = `{{ route('reports.export.inventory') }}?${currentFilters.toString()}`;
}

// Search button functionality
document.getElementById('searchBookBtn').addEventListener('click', function() {
    const query = searchInput.value.trim();
    if (!query) {
        alert('Please enter a search term');
        return;
    }
    
    // Try to find exact match first
    fetch(`{{ route('reports.autocomplete') }}?query=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                showBookDetails(data[0].id);
            } else {
                alert('No books found matching your search.');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            alert('Search error occurred. Please try again.');
        });
});

// Allow Enter key to trigger search
searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchBookBtn').click();
    }
});
</script>
@endpush
@endsection