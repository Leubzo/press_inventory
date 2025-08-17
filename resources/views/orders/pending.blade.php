@extends('orders.layout')

@section('page-content')
<h5><i class="fas fa-clock me-2"></i>Pending Approval</h5>
<p class="text-muted">Orders waiting for unit head approval</p>

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
        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
        <p class="text-muted">No orders pending approval</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Requester</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Purpose</th>
                    <th>Total Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr class="expandable-row" onclick="showOrderDetails({{ $order->id }})">
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->requester->name }}</td>
                    <td>{{ $order->created_at->format('M j, Y') }}</td>
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
                        @if(auth()->user()->canApproveOrders())
                            <button class="btn btn-success btn-sm me-1" onclick="showApprovalModal({{ $order->id }}, 'approve')" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="showApprovalModal({{ $order->id }}, 'reject')" title="Reject">
                                <i class="fas fa-times"></i>
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
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">Approve Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approvalForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="approvalOrderId">
                    <input type="hidden" id="approvalAction">
                    
                    <div class="mb-3">
                        <label for="approvalNotes" class="form-label">Notes (Optional)</label>
                        <textarea id="approvalNotes" name="notes" class="form-control" rows="3" placeholder="Add any comments about this decision..."></textarea>
                    </div>
                    
                    <div class="alert alert-info" id="approvalInfo">
                        <!-- Action confirmation message will be shown here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="approvalSubmitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    fetch(`/orders/${orderId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(order => {
        displayOrderDetails(order);
        new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
    })
    .catch(error => {
        console.error('Error loading order details:', error);
        alert('Error loading order details');
    });
}

function displayOrderDetails(order) {
    const content = document.getElementById('orderDetailsContent');
    const totalValue = order.order_items.reduce((total, item) => total + (item.quantity_requested * item.unit_price), 0);
    
    content.innerHTML = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Order Information</h6>
                <p><strong>Order Number:</strong> ${order.order_number}</p>
                <p><strong>Requester:</strong> ${order.requester.name}</p>
                <p><strong>Date Created:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
            </div>
            <div class="col-md-6">
                <h6>Order Summary</h6>
                <p><strong>Number of Items:</strong> ${order.items_count}</p>
                <p><strong>Total Value:</strong> RM ${totalValue.toFixed(2)}</p>
                ${order.purpose ? `<p><strong>Purpose:</strong> ${order.purpose}</p>` : ''}
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
                        <th>Qty</th>
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
                            <td>RM ${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>RM ${(item.quantity_requested * item.unit_price).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="5">Total Order Value</th>
                        <th>RM ${totalValue.toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

function showApprovalModal(orderId, action) {
    const modal = document.getElementById('approvalModal');
    const title = document.getElementById('approvalModalTitle');
    const info = document.getElementById('approvalInfo');
    const submitBtn = document.getElementById('approvalSubmitBtn');
    
    document.getElementById('approvalOrderId').value = orderId;
    document.getElementById('approvalAction').value = action;
    document.getElementById('approvalNotes').value = '';
    
    if (action === 'approve') {
        title.textContent = 'Approve Order';
        info.className = 'alert alert-success';
        info.innerHTML = '<i class="fas fa-check-circle me-2"></i>This order will be approved and sent to the storekeeper for fulfillment.';
        submitBtn.className = 'btn btn-success';
        submitBtn.textContent = 'Approve Order';
    } else {
        title.textContent = 'Reject Order';
        info.className = 'alert alert-danger';
        info.innerHTML = '<i class="fas fa-times-circle me-2"></i>This order will be rejected and returned to the requester.';
        submitBtn.className = 'btn btn-danger';
        submitBtn.textContent = 'Reject Order';
    }
    
    new bootstrap.Modal(modal).show();
}

// Handle approval form submission
document.getElementById('approvalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const orderId = document.getElementById('approvalOrderId').value;
    const action = document.getElementById('approvalAction').value;
    const notes = document.getElementById('approvalNotes').value;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('action', action);
    formData.append('notes', notes);
    
    fetch(`/orders/${orderId}/approve`, {
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
            const approvalModal = bootstrap.Modal.getInstance(document.getElementById('approvalModal'));
            if (approvalModal) approvalModal.hide();
            
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('orderDetailsModal'));
            if (detailsModal) detailsModal.hide();
            
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
        alert('An error occurred while processing the order.');
    });
});

// Check if user can approve orders
const auth_user_can_approve = {{ auth()->user()->canApproveOrders() ? 'true' : 'false' }};
</script>
@endsection