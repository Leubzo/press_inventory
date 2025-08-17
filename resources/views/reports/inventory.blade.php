@extends('reports.layout')

@section('page-content')
<!-- Filters -->
<div class="stats-card inventory">
    <h5><i class="fas fa-filter me-2"></i>Filters</h5>
    <form method="GET" action="{{ route('reports.inventory') }}" id="filtersForm">
        <div class="row">
            <div class="col-md-3">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ $reports['filters']['date_from'] }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ $reports['filters']['date_to'] }}">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="">All Categories</option>
                    @foreach($reports['filters']['available_categories'] as $cat)
                        <option value="{{ $cat }}" {{ $reports['filters']['category'] == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Summary Statistics -->
<div class="stats-card inventory">
    <h5><i class="fas fa-chart-pie me-2"></i>Inventory Summary</h5>
    <div class="row">
        <div class="col-md-6">
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
                <span class="metric-value">RM {{ number_format($reports['summary']['total_value'], 2) }}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="metric-item">
                <span class="metric-label">Unique Categories</span>
                <span class="metric-value">{{ $reports['summary']['unique_categories'] }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Out of Stock</span>
                <span class="metric-value text-danger">{{ $reports['summary']['out_of_stock'] }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Low Stock (≤10)</span>
                <span class="metric-value text-warning">{{ $reports['summary']['low_stock'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Stock Distribution -->
<div class="stats-card inventory">
    <h5><i class="fas fa-layer-group me-2"></i>Stock Distribution</h5>
    <div class="row">
        @foreach($reports['inventory']['stock_distribution'] as $level => $count)
            <div class="col-md-3">
                <div class="text-center p-3">
                    <h4 class="mb-1">{{ $count }}</h4>
                    <small class="text-muted">{{ ucwords(str_replace('_', ' ', $level)) }}</small>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Category Analysis -->
<div class="stats-card inventory">
    <h5><i class="fas fa-tags me-2"></i>Category Analysis</h5>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Books</th>
                    <th>Stock</th>
                    <th>Avg Price</th>
                    <th>Total Value</th>
                    <th>Out of Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports['categories'] as $category)
                    <tr>
                        <td>{{ $category['name'] }}</td>
                        <td>{{ $category['books_count'] }}</td>
                        <td>{{ number_format($category['total_stock']) }}</td>
                        <td>RM {{ number_format($category['avg_price'], 2) }}</td>
                        <td>RM {{ number_format($category['total_value'], 2) }}</td>
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

<!-- Top Value Books -->
<div class="stats-card inventory">
    <h5><i class="fas fa-crown me-2"></i>Top Value Books</h5>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
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
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->category ?: 'Uncategorized' }}</td>
                        <td>{{ $book->stock }}</td>
                        <td>RM {{ number_format($book->price, 2) }}</td>
                        <td>RM {{ number_format($book->price * $book->stock, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection