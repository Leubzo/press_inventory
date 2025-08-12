@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Inventory - UUM Press</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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

        /* Tab Navigation Styles */
        .custom-tabs {
            background: white;
            border-radius: 10px 10px 0 0;
            padding: 0.5rem 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .custom-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .custom-tabs .nav-link:hover {
            background: #f8f9fa;
            color: #667eea;
            transform: translateY(-2px);
        }

        .custom-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .custom-tabs .nav-link .badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-icon.books {
            background: var(--primary-gradient);
            color: white;
        }

        .stats-icon.stock {
            background: var(--success-gradient);
            color: white;
        }

        .stats-icon.categories {
            background: var(--warning-gradient);
            color: white;
        }

        .stats-icon.value {
            background: var(--secondary-gradient);
            color: white;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .stats-label {
            color: #718096;
            font-size: 0.9rem;
            margin: 0;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .header-section {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .btn-custom {
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary.btn-custom {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary.btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success.btn-custom {
            background: var(--success-gradient);
            color: white;
        }

        .btn-success.btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
        }

        .btn-danger.btn-custom {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-danger.btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(247, 112, 98, 0.4);
        }

        .search-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .search-input {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--primary-gradient);
            color: white;
        }

        .pagination {
            gap: 0.5rem;
        }

        .page-link {
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: #667eea;
            font-weight: 500;
        }

        .page-link:hover {
            background: #f3f4f6;
            color: #667eea;
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
            color: white;
        }

        .badge {
            padding: 0.35rem 0.65rem;
            font-weight: 500;
        }

        .stock-high {
            background-color: #d4edda;
            color: #155724;
        }

        .stock-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .stock-low {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .scanner-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .inline-stock-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .inline-stock-form input {
            width: 80px;
            padding: 0.25rem 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .search-section {
                padding: 1rem;
            }

            .inline-stock-form {
                flex-direction: column;
                gap: 0.25rem;
            }

            .inline-stock-form input {
                width: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-books me-2"></i>
                UUM Press Book Inventory
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        Welcome, {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
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
    <div class="container mt-3">
        <ul class="nav nav-tabs custom-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('books.index') }}">
                    <i class="fas fa-book me-2"></i>Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('audit-logs.index') }}">
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
                <a class="nav-link" href="#" onclick="alert('Reports feature coming soon!')">
                    <i class="fas fa-chart-bar me-2"></i>Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="alert('Settings feature coming soon!')">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>
        </ul>
    </div>

    <div class="container main-container">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon books">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="stats-number">{{ $books->total() }}</h3>
                    <p class="stats-label">Total Books</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon stock">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="stats-number">{{ \App\Models\Book::sum('stock') }}</h3>
                    <p class="stats-label">Total Stock</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon categories">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="stats-number">{{ \App\Models\Book::distinct('category')->count('category') }}</h3>
                    <p class="stats-label">Categories</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon value">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h3 class="stats-number">RM {{ number_format(\App\Models\Book::sum(\DB::raw('price * stock')), 2) }}</h3>
                    <p class="stats-label">Total Value</p>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="content-card">
            <!-- Header Section -->
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-0">
                            <i class="fas fa-books me-2"></i>Book Inventory Management
                        </h2>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-success btn-custom me-2" data-bs-toggle="modal" data-bs-target="#addBookModal">
                            <i class="fas fa-plus-circle me-1"></i>Add Book
                        </button>
                        <button type="button" class="btn btn-primary btn-custom me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i>Import
                        </button>
                        <form action="{{ route('books.reset') }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reset all books? This cannot be undone.');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-custom">
                                <i class="fas fa-trash me-1"></i>Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Search and Scanner Section -->
            <div class="search-section">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <form id="searchForm" action="{{ route('books.index') }}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
                                    class="form-control search-input"
                                    placeholder="Search by ISBN, Title, or Authors/Editors">
                                <button type="submit" class="btn btn-primary btn-custom">Search</button>
                                @if(!empty($search))
                                <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-custom">Clear</a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="scanner-section text-center">
                            <button class="btn btn-light btn-custom" id="startScanner">
                                <i class="fas fa-camera me-2"></i>Scan Barcode
                            </button>
                            <small class="d-block mt-1 opacity-75">Quick search with camera</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden scanner box -->
            <div id="scanner-container" style="width: 100%; display: none; margin: 10px;">
                <div id="reader" style="width: 100%;"></div>
                <button class="btn btn-danger mt-2" id="closeScanner">Close Scanner</button>
            </div>

            <!-- Books Table -->
            <div class="table-responsive" id="book-table">
                @include('books.partials.book-table', ['books' => $books])
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center p-3">
                {{ $books->links() }}
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Books from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <small class="text-muted">CSV should contain: ISBN, Title, Authors/Editors, Year, Pages, Price, Category, Stock</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('books.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="isbn" class="form-label">ISBN *</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="authors_editors" class="form-label">Authors/Editors *</label>
                            <input type="text" class="form-control" id="authors_editors" name="authors_editors" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" class="form-control" id="year" name="year" min="1900" max="2099">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pages" class="form-label">Pages</label>
                                <input type="number" class="form-control" id="pages" name="pages" min="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Price (RM)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Barcode Scanner
        let html5QrcodeScanner = null;

        document.getElementById('startScanner').addEventListener('click', function() {
            document.getElementById('scanner-container').style.display = 'block';
            this.style.display = 'none';

            html5QrcodeScanner = new Html5Qrcode("reader");

            html5QrcodeScanner.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                (decodedText, decodedResult) => {
                    document.getElementById('searchInput').value = decodedText;
                    document.getElementById('searchForm').submit();
                    stopScanner();
                },
                (errorMessage) => {
                    // Handle scan error silently
                }
            ).catch((err) => {
                console.error(`Unable to start scanning: ${err}`);
                alert('Unable to access camera. Please ensure you have granted camera permissions.');
                stopScanner();
            });
        });

        document.getElementById('closeScanner').addEventListener('click', stopScanner);

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    document.getElementById('scanner-container').style.display = 'none';
                    document.getElementById('startScanner').style.display = 'block';
                }).catch((err) => {
                    console.error(`Unable to stop scanning: ${err}`);
                });
            }
        }

    </script>
</body>

</html>
@endsection