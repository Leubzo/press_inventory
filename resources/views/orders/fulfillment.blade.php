@extends('orders.layout')

@section('page-content')
<h5><i class="fas fa-truck me-2"></i>Awaiting Fulfillment</h5>
<p class="text-muted">Approved orders waiting for storekeeper fulfillment</p>

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

@if($orders->count() === 0)
    <div class="text-center py-4">
        <i class="fas fa-clipboard-check fa-2x mb-2 text-success"></i>
        <p class="text-muted">No orders awaiting fulfillment</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Requester</th>
                    <th>Approved By</th>
                    <th>Approval Date</th>
                    <th>Items</th>
                    <th>Purpose</th>
                    <th>Total Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr class="expandable-row" onclick="showFulfillmentOrderDetails({{ $order->id }})">
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->requester->name }}</td>
                    <td>{{ $order->approver ? $order->approver->name : 'N/A' }}</td>
                    <td>{{ $order->approval_date ? $order->approval_date->format('M j, Y') : 'N/A' }}</td>
                    <td>
                        <span class="badge bg-primary">{{ $order->items_count }} items</span>
                    </td>
                    <td>
                        @if($order->purpose)
                            {{ Str::limit($order->purpose, 50) }}
                        @else
                            <span class="text-muted">No purpose specified</span>
                        @endif
                    </td>
                    <td>RM {{ number_format($order->orderItems->sum(function($item) { return $item->quantity_requested * $item->unit_price; }), 2) }}</td>
                    <td onclick="event.stopPropagation()">
                        @if(auth()->user()->canFulfillOrders())
                            <button class="btn btn-success btn-sm" onclick="showFulfillmentModal({{ $order->id }})" title="Fulfill Order">
                                <i class="fas fa-truck"></i> Fulfill
                            </button>
                        @else
                            <span class="text-muted">View only</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Order Details Modal -->
<div class="modal fade" id="fulfillmentOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="fulfillmentOrderContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Fulfillment Modal -->
<div class="modal fade" id="fulfillmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fulfill Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="fulfillmentForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="fulfillmentOrderId">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Update the quantities that were actually fulfilled from the warehouse. Stock levels will be automatically updated.
                    </div>
                    
                    <div id="fulfillmentItems">
                        <!-- Items will be loaded here -->
                    </div>
                    
                    <div class="mb-3 mt-4">
                        <label for="fulfillmentNotes" class="form-label">Fulfillment Notes (Optional)</label>
                        <textarea id="fulfillmentNotes" name="notes" class="form-control" rows="3" placeholder="Add any comments about the fulfillment process..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Complete Fulfillment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showFulfillmentOrderDetails(orderId) {
    fetch(`/orders/${orderId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(order => {
        displayFulfillmentOrderDetails(order);
        new bootstrap.Modal(document.getElementById('fulfillmentOrderModal')).show();
    })
    .catch(error => {
        console.error('Error loading order details:', error);
        alert('Error loading order details');
    });
}

function displayFulfillmentOrderDetails(order) {
    const content = document.getElementById('fulfillmentOrderContent');
    const totalValue = order.order_items.reduce((total, item) => total + (item.quantity_requested * item.unit_price), 0);
    
    content.innerHTML = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Order Information</h6>
                <p><strong>Order Number:</strong> ${order.order_number}</p>
                <p><strong>Requester:</strong> ${order.requester.name}</p>
                <p><strong>Date Created:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                <p><strong>Approved By:</strong> ${order.approver ? order.approver.name : 'N/A'}</p>
                <p><strong>Approval Date:</strong> ${order.approval_date ? new Date(order.approval_date).toLocaleString() : 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Order Summary</h6>
                <p><strong>Number of Items:</strong> ${order.items_count}</p>
                <p><strong>Total Value:</strong> RM ${totalValue.toFixed(2)}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                ${order.purpose ? `<p><strong>Purpose:</strong> ${order.purpose}</p>` : ''}
                ${order.notes ? `<p><strong>Approval Notes:</strong> ${order.notes}</p>` : ''}
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
                        <th>Current Stock</th>
                        <th>Qty Requested</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.order_items.map(item => `
                        <tr ${item.book.stock < item.quantity_requested ? 'class="table-warning"' : ''}>
                            <td>${item.item_number}</td>
                            <td>
                                <strong>${item.book.title}</strong><br>
                                <small class="text-muted">${item.book.authors_editors}</small>
                            </td>
                            <td>${item.book.isbn}</td>
                            <td>
                                <strong>${item.book.stock}</strong>
                                ${item.book.stock < item.quantity_requested ? 
                                    '<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Insufficient stock</small>' : 
                                    ''
                                }
                            </td>
                            <td>${item.quantity_requested}</td>
                            <td>RM ${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>RM ${(item.quantity_requested * item.unit_price).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="6">Total Order Value</th>
                        <th>RM ${totalValue.toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

function showFulfillmentModal(orderId) {
    fetch(`/orders/${orderId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(order => {
        setupFulfillmentModal(order);
        new bootstrap.Modal(document.getElementById('fulfillmentModal')).show();
    })
    .catch(error => {
        console.error('Error loading order for fulfillment:', error);
        alert('Error loading order details');
    });
}

function setupFulfillmentModal(order) {
    document.getElementById('fulfillmentOrderId').value = order.id;
    document.getElementById('fulfillmentNotes').value = '';
    
    const itemsContainer = document.getElementById('fulfillmentItems');
    
    itemsContainer.innerHTML = `
        <h6>Fulfill Order Items</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Current Stock</th>
                        <th>Requested</th>
                        <th>Fulfill Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.order_items.map(item => `
                        <tr ${item.book.stock < item.quantity_requested ? 'class="table-warning"' : ''}>
                            <td>${item.item_number}</td>
                            <td>
                                <strong>${item.book.title}</strong><br>
                                <small class="text-muted">ISBN: ${item.book.isbn}</small>
                            </td>
                            <td>
                                <strong>${item.book.stock}</strong>
                                ${item.book.stock < item.quantity_requested ? 
                                    '<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Low stock</small>' : 
                                    ''
                                }
                            </td>
                            <td>${item.quantity_requested}</td>
                            <td>
                                <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                                <input type="number" 
                                       name="items[${item.id}][quantity_fulfilled]" 
                                       class="form-control fulfillment-quantity" 
                                       value="${Math.min(item.quantity_requested, item.book.stock)}"
                                       min="0" 
                                       max="${item.book.stock}"
                                       data-requested="${item.quantity_requested}"
                                       data-stock="${item.book.stock}">
                                <small class="text-muted">Max: ${item.book.stock}</small>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Stock levels will be automatically reduced by the fulfilled quantities. 
            Make sure the physical books are removed from inventory before confirming.
        </div>
    `;
}

// Handle fulfillment form submission
document.getElementById('fulfillmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const orderId = document.getElementById('fulfillmentOrderId').value;
    const notes = document.getElementById('fulfillmentNotes').value;
    
    // Collect fulfillment data
    const items = [];
    const fulfillmentInputs = document.querySelectorAll('.fulfillment-quantity');
    
    fulfillmentInputs.forEach(input => {
        const itemId = input.name.match(/items\[(\d+)\]/)[1];
        const quantityFulfilled = parseInt(input.value) || 0;
        
        items.push({
            id: itemId,
            quantity_fulfilled: quantityFulfilled
        });
    });
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('notes', notes);
    formData.append('items', JSON.stringify(items));
    
    fetch(`/orders/${orderId}/fulfill`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            
            // Hide modals safely
            const fulfillmentModal = bootstrap.Modal.getInstance(document.getElementById('fulfillmentModal'));
            if (fulfillmentModal) fulfillmentModal.hide();
            
            const orderModal = bootstrap.Modal.getInstance(document.getElementById('fulfillmentOrderModal'));
            if (orderModal) orderModal.hide();
            
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fulfilling the order.');
    });
});

// Check if user can fulfill orders
const auth_user_can_fulfill = {{ auth()->user()->canFulfillOrders() ? 'true' : 'false' }};
</script>
@endsection