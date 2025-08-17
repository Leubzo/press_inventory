@extends('reports.layout')

@section('page-content')
<!-- Filters -->
<div class="stats-card sales">
    <h5><i class="fas fa-filter me-2"></i>Filters</h5>
    <form method="GET" action="{{ route('reports.sales') }}" id="salesFiltersForm">
        <div class="row">
            <div class="col-md-2">
                <label for="sales_date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="sales_date_from" name="date_from" 
                       value="{{ $reports['filters']['date_from'] }}">
            </div>
            <div class="col-md-2">
                <label for="sales_date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="sales_date_to" name="date_to" 
                       value="{{ $reports['filters']['date_to'] }}">
            </div>
            <div class="col-md-2">
                <label for="sales_category" class="form-label">Category</label>
                <select class="form-control" id="sales_category" name="category">
                    <option value="">All Categories</option>
                    @foreach($reports['filters']['available_categories'] as $cat)
                        <option value="{{ $cat }}" {{ $reports['filters']['category'] == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="platform" class="form-label">Platform</label>
                <select class="form-control" id="platform" name="platform">
                    <option value="">All Platforms</option>
                    @foreach($reports['filters']['available_platforms'] ?? [] as $platform)
                        <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>
                            {{ $platform }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@if(isset($reports['sales']) && $reports['sales']['total_orders'] > 0)
    <!-- Sales Summary -->
    <div class="stats-card sales">
        <h5><i class="fas fa-chart-line me-2"></i>Sales Summary</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="metric-item">
                    <span class="metric-label">Total Orders</span>
                    <span class="metric-value">{{ number_format($reports['sales']['total_orders']) }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Total Revenue</span>
                    <span class="metric-value">RM {{ number_format($reports['sales']['total_revenue'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-item">
                    <span class="metric-label">Books Sold</span>
                    <span class="metric-value">{{ number_format($reports['sales']['total_quantity']) }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Avg Order Value</span>
                    <span class="metric-value">RM {{ number_format($reports['sales']['avg_order_value'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-item">
                    <span class="metric-label">Unique Books Sold</span>
                    <span class="metric-value">{{ $reports['sales']['unique_books_sold'] }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Platforms Used</span>
                    <span class="metric-value">{{ $reports['sales']['platforms_used'] }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-item">
                    <span class="metric-label">Avg Books/Order</span>
                    <span class="metric-value">{{ number_format($reports['sales']['avg_books_per_order'], 1) }}</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Fulfillment Rate</span>
                    <span class="metric-value">{{ number_format($reports['sales']['fulfillment_rate'], 1) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Performance -->
    @if($reports['sales']['sales_by_platform']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-store me-2"></i>Platform Performance</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Books Sold</th>
                            <th>Avg Order Value</th>
                            <th>Market Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['sales']['sales_by_platform'] as $platform)
                            <tr>
                                <td>{{ $platform['platform'] }}</td>
                                <td>{{ number_format($platform['total_orders']) }}</td>
                                <td>RM {{ number_format($platform['total_revenue'], 2) }}</td>
                                <td>{{ number_format($platform['total_quantity']) }}</td>
                                <td>RM {{ number_format($platform['avg_order_value'], 2) }}</td>
                                <td>{{ number_format($platform['market_share'], 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Best Selling Books -->
    @if($reports['sales']['top_selling_books']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-trophy me-2"></i>Best Selling Books</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Book Title</th>
                            <th>Category</th>
                            <th>Quantity Sold</th>
                            <th>Orders</th>
                            <th>Total Revenue</th>
                            <th>Avg Sale Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['sales']['top_selling_books'] as $index => $bookSale)
                            <tr>
                                <td>
                                    @if($index == 0)
                                        <i class="fas fa-crown text-warning"></i>
                                    @elseif($index == 1)
                                        <i class="fas fa-medal text-secondary"></i>
                                    @elseif($index == 2)
                                        <i class="fas fa-award text-warning"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>{{ $bookSale['book']->title }}</td>
                                <td>{{ $bookSale['book']->category ?: 'Uncategorized' }}</td>
                                <td>{{ number_format($bookSale['total_quantity']) }}</td>
                                <td>{{ $bookSale['orders_count'] }}</td>
                                <td>RM {{ number_format($bookSale['total_revenue'], 2) }}</td>
                                <td>RM {{ number_format($bookSale['avg_sale_price'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Recent Orders -->
    @if($reports['sales']['recent_orders']->count() > 0)
        <div class="stats-card sales">
            <h5><i class="fas fa-clock me-2"></i>Recent Fulfilled Orders</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Platform</th>
                            <th>Items</th>
                            <th>Total Value</th>
                            <th>Fulfilled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports['sales']['recent_orders'] as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->platform }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td>RM {{ number_format($order->total_value, 2) }}</td>
                                <td>{{ $order->fulfillment_date->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@else
    <!-- No Sales Data -->
    <div class="stats-card sales">
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Sales Data Available</h5>
            <p class="text-muted">No fulfilled orders found for the selected period. Orders must be fulfilled to appear in sales analytics.</p>
            <a href="{{ route('orders.index') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create New Order
            </a>
        </div>
    </div>
@endif
@endsection