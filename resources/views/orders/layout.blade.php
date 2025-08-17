@extends('layouts.base')

@section('content')
<!-- Order Management Navigation -->
<div class="card-container">
        <ul class="nav nav-pills custom-tabs" id="orderTabs">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.create') || request()->routeIs('orders.index') ? 'active' : '' }}" href="{{ route('orders.create') }}">
                    <i class="fas fa-plus me-2"></i>Create Order
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.pending') ? 'active' : '' }}" href="{{ route('orders.pending') }}">
                    <i class="fas fa-clock me-2"></i>Pending Approval
                    <span id="pending-approval-badge" class="badge-notification" style="display: none;">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.fulfillment') ? 'active' : '' }}" href="{{ route('orders.fulfillment') }}">
                    <i class="fas fa-truck me-2"></i>Awaiting Fulfillment
                    <span id="awaiting-fulfillment-badge" class="badge-notification" style="display: none;">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.history') ? 'active' : '' }}" href="{{ route('orders.history') }}">
                    <i class="fas fa-history me-2"></i>Order History
                </a>
            </li>
        </ul>

        <!-- Page Content -->
        @yield('page-content')
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update notification badges
    updatePendingCounts();
    
    // Refresh badges every 30 seconds
    setInterval(updatePendingCounts, 30000);
    
    function updatePendingCounts() {
        fetch('{{ route("orders.pending-counts") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const pendingBadge = document.getElementById('pending-approval-badge');
            const fulfillmentBadge = document.getElementById('awaiting-fulfillment-badge');
            
            if (data.pending_approval > 0) {
                pendingBadge.textContent = data.pending_approval;
                pendingBadge.style.display = 'inline';
            } else {
                pendingBadge.style.display = 'none';
            }
            
            if (data.awaiting_fulfillment > 0) {
                fulfillmentBadge.textContent = data.awaiting_fulfillment;
                fulfillmentBadge.style.display = 'inline';
            } else {
                fulfillmentBadge.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error updating pending counts:', error);
        });
    }
});
</script>
@endpush