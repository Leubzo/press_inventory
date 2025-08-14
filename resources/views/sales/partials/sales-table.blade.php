<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>
                    <a href="?sort=sale_date&direction={{ $sortField == 'sale_date' && $sortDirection == 'asc' ? 'desc' : 'asc' }}&{{ http_build_query(request()->except(['sort', 'direction', 'page'])) }}" 
                       class="text-decoration-none text-dark">
                        Sale Date 
                        @if($sortField == 'sale_date')
                            <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>Book Details</th>
                <th>
                    <a href="?sort=platform&direction={{ $sortField == 'platform' && $sortDirection == 'asc' ? 'desc' : 'asc' }}&{{ http_build_query(request()->except(['sort', 'direction', 'page'])) }}" 
                       class="text-decoration-none text-dark">
                        Platform 
                        @if($sortField == 'platform')
                            <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="?sort=quantity&direction={{ $sortField == 'quantity' && $sortDirection == 'asc' ? 'desc' : 'asc' }}&{{ http_build_query(request()->except(['sort', 'direction', 'page'])) }}" 
                       class="text-decoration-none text-dark">
                        Quantity 
                        @if($sortField == 'quantity')
                            <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="?sort=unit_price&direction={{ $sortField == 'unit_price' && $sortDirection == 'asc' ? 'desc' : 'asc' }}&{{ http_build_query(request()->except(['sort', 'direction', 'page'])) }}" 
                       class="text-decoration-none text-dark">
                        Unit Price 
                        @if($sortField == 'unit_price')
                            <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="?sort=total_price&direction={{ $sortField == 'total_price' && $sortDirection == 'asc' ? 'desc' : 'asc' }}&{{ http_build_query(request()->except(['sort', 'direction', 'page'])) }}" 
                       class="text-decoration-none text-dark">
                        Total 
                        @if($sortField == 'total_price')
                            <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>Order Info</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>
                    <strong>{{ $sale->sale_date->format('M d, Y') }}</strong><br>
                    <small class="text-muted">{{ $sale->created_at->format('H:i') }}</small>
                </td>
                <td>
                    <div>
                        <strong>{{ $sale->book->title }}</strong><br>
                        <small class="text-muted">
                            ISBN: {{ $sale->book->isbn }}<br>
                            Current Stock: {{ $sale->book->stock }}
                            @php
                                $totalSold = $sale->book->sales()->sum('quantity') ?? 0;
                            @endphp
                            | Sold: {{ $totalSold }}
                            @if($totalSold > $sale->book->stock)
                                <span class="text-danger">⚠️</span>
                            @endif
                        </small>
                    </div>
                </td>
                <td>
                    @php
                        $platformClass = 'platform-' . strtolower(str_replace(' ', '', $sale->platform));
                    @endphp
                    <span class="platform-badge {{ $platformClass }}">{{ $sale->platform }}</span>
                </td>
                <td>
                    <strong>{{ $sale->quantity }}</strong>
                </td>
                <td>
                    <strong>RM {{ number_format($sale->unit_price, 2) }}</strong>
                </td>
                <td>
                    <strong class="text-success">RM {{ number_format($sale->total_price, 2) }}</strong>
                </td>
                <td>
                    @if($sale->order_number)
                        <strong>{{ $sale->order_number }}</strong><br>
                    @endif
                    @if($sale->buyer_info)
                        <small class="text-muted">{{ Str::limit($sale->buyer_info, 50) }}</small>
                    @else
                        <small class="text-muted">No buyer info</small>
                    @endif
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editSale({{ $sale->id }})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSale({{ $sale->id }})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <h5>No sales found</h5>
                        <p>Start by recording your first sale above!</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($sales->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $sales->appends(request()->query())->links() }}
</div>
@endif

<!-- Sales Summary -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h5 class="text-primary">{{ $sales->total() }}</h5>
                        <small class="text-muted">Total Sales Records</small>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-success">RM {{ number_format($sales->sum('total_price'), 2) }}</h5>
                        <small class="text-muted">Page Revenue</small>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-info">{{ $sales->sum('quantity') }}</h5>
                        <small class="text-muted">Books Sold (Page)</small>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-warning">{{ $sales->pluck('platform')->unique()->count() }}</h5>
                        <small class="text-muted">Platforms (Page)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>