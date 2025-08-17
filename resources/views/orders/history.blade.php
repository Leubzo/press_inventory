@extends('orders.layout')

@section('page-content')
<h5><i class="fas fa-history me-2"></i>Order History</h5>
<p class="text-muted">View all completed, rejected, and fulfilled orders</p>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Search and Filter Section -->
<form method="GET" action="{{ route('orders.history') }}" class="mb-4">
    <div class="row">
        <div class="col-md-3">
            <label for="search" class="form-label">Search Orders</label>
            <input type="text" id="search" name="search" class="form-control" 
                   placeholder="Order number, requester..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="date_from" class="form-label">From Date</label>
            <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label for="date_to" class="form-label">To Date</label>
            <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-gradient">
                    <i class="fas fa-search me-1"></i>Search
                </button>
                <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </div>
    </div>
</form>

@if($orders->count() === 0)
    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
        <div class="text-center py-4">
            <i class="fas fa-search fa-2x mb-2 text-muted"></i>
            <p class="text-muted">No orders found matching your criteria</p>
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-history fa-2x mb-2 text-muted"></i>
            <p class="text-muted">No order history available</p>
        </div>
    @endif
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0 text-muted">Found {{ $orders->count() }} order(s)</p>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Requester</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Purpose</th>
                    <th>Total Value</th>
                    <th>Completed Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr class="expandable-row" onclick="showHistoryOrderDetails({{ $order->id }})">
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->requester->name }}</td>
                    <td>{{ $order->created_at->format('M j, Y') }}</td>
                    <td>
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $order->items_count }} items</span>
                    </td>
                    <td>
                        @if($order->purpose)
                            {{ Str::limit($order->purpose, 30) }}
                        @else
                            <span class="text-muted">No purpose</span>
                        @endif
                    </td>
                    <td>RM {{ number_format($order->orderItems->sum(function($item) { return $item->quantity_requested * $item->unit_price; }), 2) }}</td>
                    <td>
                        @if($order->status === 'fulfilled' && $order->fulfillment_date)
                            {{ $order->fulfillment_date->format('M j, Y') }}
                        @elseif($order->status === 'rejected' && $order->approval_date)
                            {{ $order->approval_date->format('M j, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td onclick="event.stopPropagation()">
                        <button class="btn btn-outline-primary btn-sm" onclick="showHistoryOrderDetails({{ $order->id }})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Order Details Modal -->
<div class="modal fade" id="historyOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyOrderContent">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printOrder()" id="printOrderBtn">
                    <i class="fas fa-print me-2"></i>Print Order
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrder = null;

document.addEventListener('DOMContentLoaded', function() {
    // Set default date range if no filters are applied
    if (!window.location.search) {
        const today = new Date();
        const lastMonth = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        const dateToInput = document.getElementById('date_to');
        const dateFromInput = document.getElementById('date_from');
        
        if (dateToInput && !dateToInput.value) {
            dateToInput.value = today.toISOString().split('T')[0];
        }
        if (dateFromInput && !dateFromInput.value) {
            dateFromInput.value = lastMonth.toISOString().split('T')[0];
        }
    }
});

function showHistoryOrderDetails(orderId) {
    fetch(`/orders/${orderId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(order => {
        currentOrder = order;
        displayHistoryOrderDetails(order);
        new bootstrap.Modal(document.getElementById('historyOrderModal')).show();
    })
    .catch(error => {
        console.error('Error loading order details:', error);
        alert('Error loading order details');
    });
}

function displayHistoryOrderDetails(order) {
    const content = document.getElementById('historyOrderContent');
    const totalValue = order.order_items.reduce((total, item) => total + (item.quantity_requested * item.unit_price), 0);
    
    content.innerHTML = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Order Information</h6>
                <p><strong>Order Number:</strong> ${order.order_number}</p>
                <p><strong>Requester:</strong> ${order.requester.name}</p>
                <p><strong>Date Created:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                ${order.approver ? `<p><strong>Processed By:</strong> ${order.approver.name}</p>` : ''}
                ${order.fulfiller ? `<p><strong>Fulfilled By:</strong> ${order.fulfiller.name}</p>` : ''}
            </div>
            <div class="col-md-6">
                <h6>Order Summary</h6>
                <p><strong>Number of Items:</strong> ${order.items_count}</p>
                <p><strong>Total Value:</strong> RM ${totalValue.toFixed(2)}</p>
                ${order.approval_date ? `<p><strong>Approval Date:</strong> ${new Date(order.approval_date).toLocaleString()}</p>` : ''}
                ${order.fulfillment_date ? `<p><strong>Fulfillment Date:</strong> ${new Date(order.fulfillment_date).toLocaleString()}</p>` : ''}
                ${order.purpose ? `<p><strong>Purpose:</strong> ${order.purpose}</p>` : ''}
                ${order.notes ? `<p><strong>Notes:</strong> ${order.notes}</p>` : ''}
            </div>
        </div>
        
        <h6>Order Items</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>ISBN</th>
                        <th>Qty Requested</th>
                        ${order.status === 'fulfilled' ? '<th>Qty Fulfilled</th>' : ''}
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.order_items.map(item => `
                        <tr>
                            <td>${item.item_number}</td>
                            <td>
                                <strong>${item.book.title}</strong><br>
                                <small class="text-muted">${item.book.authors_editors}</small>
                            </td>
                            <td>${item.book.isbn}</td>
                            <td>${item.quantity_requested}</td>
                            ${order.status === 'fulfilled' ? `
                                <td>
                                    ${item.quantity_fulfilled}
                                    ${item.quantity_fulfilled < item.quantity_requested ? 
                                        '<br><small class="text-warning">Partial fulfillment</small>' : 
                                        '<br><small class="text-success">Complete</small>'
                                    }
                                </td>
                            ` : ''}
                            <td>RM ${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>RM ${(item.quantity_requested * item.unit_price).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="${order.status === 'fulfilled' ? '6' : '5'}">Total Order Value</th>
                        <th>RM ${totalValue.toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

function printOrder() {
    if (!currentOrder) return;
    
    // Create a print-friendly version of the order
    const printWindow = window.open('', '_blank');
    const totalValue = currentOrder.order_items.reduce((total, item) => total + (item.quantity_requested * item.unit_price), 0);
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Order ${currentOrder.order_number}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .order-info { margin-bottom: 20px; }
                .order-info div { margin-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total-row { font-weight: bold; background-color: #f9f9f9; }
                .status-${currentOrder.status} { 
                    padding: 4px 8px; 
                    border-radius: 4px; 
                    background: #f0f0f0; 
                    font-weight: bold; 
                }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>UUM Press Inventory System</h1>
                <h2>Stock Application Form</h2>
                <h3>Order #${currentOrder.order_number}</h3>
            </div>
            
            <div class="order-info">
                <div><strong>Requester:</strong> ${currentOrder.requester.name}</div>
                <div><strong>Date Created:</strong> ${new Date(currentOrder.created_at).toLocaleString()}</div>
                <div><strong>Status:</strong> <span class="status-${currentOrder.status}">${currentOrder.status.charAt(0).toUpperCase() + currentOrder.status.slice(1)}</span></div>
                <div><strong>Number of Items:</strong> ${currentOrder.items_count}</div>
                ${currentOrder.purpose ? `<div><strong>Purpose:</strong> ${currentOrder.purpose}</div>` : ''}
                ${currentOrder.approver ? `<div><strong>Approved By:</strong> ${currentOrder.approver.name}</div>` : ''}
                ${currentOrder.fulfiller ? `<div><strong>Fulfilled By:</strong> ${currentOrder.fulfiller.name}</div>` : ''}
                ${currentOrder.notes ? `<div><strong>Notes:</strong> ${currentOrder.notes}</div>` : ''}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Authors</th>
                        <th>ISBN</th>
                        <th>Qty Requested</th>
                        ${currentOrder.status === 'fulfilled' ? '<th>Qty Fulfilled</th>' : ''}
                        <th>Unit Price (RM)</th>
                        <th>Total (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    ${currentOrder.order_items.map(item => `
                        <tr>
                            <td>${item.item_number}</td>
                            <td>${item.book.title}</td>
                            <td>${item.book.authors_editors}</td>
                            <td>${item.book.isbn}</td>
                            <td>${item.quantity_requested}</td>
                            ${currentOrder.status === 'fulfilled' ? `<td>${item.quantity_fulfilled}</td>` : ''}
                            <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>${(item.quantity_requested * item.unit_price).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="${currentOrder.status === 'fulfilled' ? '7' : '6'}"><strong>Total Order Value</strong></td>
                        <td><strong>RM ${totalValue.toFixed(2)}</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 50px;">
                <p><strong>Generated on:</strong> ${new Date().toLocaleString()}</p>
                <p style="font-size: 0.9em; color: #666;">This is a computer-generated document.</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection