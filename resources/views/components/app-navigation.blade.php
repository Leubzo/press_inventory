@props(['currentRoute' => ''])

<!-- Navigation -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <x-application-logo style="height: 48px; width: 48px; border-radius: 8px;" class="me-2" />
            UUM Press Inventory System
        </a>
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i>
                    Welcome, {{ auth()->user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(auth()->user()->isAdmin())
                        <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="fas fa-users-cog me-2"></i>User Management</a></li>
                    @else
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                    @endif
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Tab Navigation -->
<div class="container-fluid mt-3">
    <ul class="nav nav-tabs custom-tabs">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}" href="{{ route('books.index') }}">
                <i class="fas fa-book me-2"></i>Inventory
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                <i class="fas fa-history me-2"></i>Audit Logs
                @php
                $recentLogsCount = \App\Models\AuditLog::where('created_at', '>=', now()->subHours(24))->count();
                @endphp
                @if($recentLogsCount > 0)
                <span class="badge bg-danger ms-1">{{ $recentLogsCount }} new</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('orders.*') || request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                <i class="fas fa-shopping-cart me-2"></i>Orders
            </a>
        </li>
    </ul>
</div>