@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - UUM Press Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #f77062 0%, #fe5196 100%);
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .main-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .audit-log-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid;
        }

        .audit-log-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }

        .audit-log-card.created {
            border-left-color: #28a745;
        }

        .audit-log-card.updated {
            border-left-color: #007bff;
        }

        .audit-log-card.deleted {
            border-left-color: #dc3545;
        }

        .action-badge {
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-badge.created {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            color: #0a5f3e;
        }

        .action-badge.updated {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #0056b3;
        }

        .action-badge.deleted {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #721c24;
        }

        .change-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid #e9ecef;
        }

        .change-field {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .change-values {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .old-value {
            background: #fff5f5;
            color: #dc3545;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #f8d7da;
        }

        .new-value {
            background: #f0fff4;
            color: #28a745;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #d4edda;
        }

        .arrow-icon {
            color: #6c757d;
        }

        .meta-info {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-info {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .book-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .book-isbn {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #718096;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #a0aec0;
        }

        .btn-custom {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary.btn-custom {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary.btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        .pagination .page-link {
            border: none;
            color: #667eea;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            color: white;
        }

        @media (max-width: 768px) {
            .change-values {
                flex-direction: column;
                align-items: flex-start;
            }

            .meta-info {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('books.index') }}">
                <i class="fas fa-books me-2"></i>
                UUM Press Book Inventory
            </a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('books.index') }}" class="btn btn-light btn-sm me-2">
                    <i class="fas fa-book me-1"></i> Books
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
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

    <div class="container main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-history me-2"></i>Audit Logs
                    </h1>
                    <p class="text-muted mb-0">Track all changes made to the inventory system</p>
                </div>
                <div>
                    <a href="{{ route('books.index') }}" class="btn btn-primary btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>Back to Books
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="book_id" class="form-label">Filter by Book</label>
                    <select name="book_id" id="book_id" class="form-select">
                        <option value="">All Books</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                                {{ $book->title }} ({{ $book->isbn }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="action" class="form-label">Action Type</label>
                    <select name="action" id="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-custom w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Audit Logs -->
        @if($auditLogs->count() > 0)
            @foreach($auditLogs as $log)
                <div class="audit-log-card {{ $log->action }}">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="action-badge {{ $log->action }}">
                            @if($log->action == 'created')
                                <i class="fas fa-plus-circle me-1"></i>
                            @elseif($log->action == 'updated')
                                <i class="fas fa-edit me-1"></i>
                            @else
                                <i class="fas fa-trash me-1"></i>
                            @endif
                            {{ ucfirst($log->action) }}
                        </span>
                        <span class="text-muted">
                            <i class="far fa-clock me-1"></i>
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($log->book)
                        <div class="book-info">
                            <div class="book-title">{{ $log->book->title }}</div>
                            <div class="book-isbn">ISBN: {{ $log->book->isbn }}</div>
                        </div>
                    @endif

                    @if($log->action == 'updated' && $log->getReadableChanges())
                        <h6 class="mb-3">Changes Made:</h6>
                        @foreach($log->getReadableChanges() as $change)
                            <div class="change-item">
                                <div class="change-field">
                                    <i class="fas fa-tag me-1"></i>{{ $change['field'] }}
                                </div>
                                <div class="change-values">
                                    <span class="old-value">
                                        <i class="fas fa-times-circle me-1"></i>
                                        {{ $change['old'] ?? 'Empty' }}
                                    </span>
                                    <span class="arrow-icon">
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
                                    <span class="new-value">
                                        <i class="fas fa-check-circle me-1"></i>
                                        {{ $change['new'] ?? 'Empty' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @elseif($log->action == 'created')
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-sparkles me-2"></i>
                            New book was added to the inventory
                        </div>
                    @elseif($log->action == 'deleted')
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Book was removed from the inventory
                        </div>
                    @endif

                    <div class="meta-info">
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $log->user_identifier ?? 'System' }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-desktop"></i>
                            <span>{{ ucfirst($log->user_source) }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $log->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            {{ $auditLogs->links() }}
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Audit Logs Found</h3>
                <p>There are no audit logs matching your criteria.</p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection