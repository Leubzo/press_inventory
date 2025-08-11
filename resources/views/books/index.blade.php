<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: white !important;
        }

        .main-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stats-icon.books { background: var(--primary-gradient); }
        .stats-icon.stock { background: var(--success-gradient); }
        .stats-icon.categories { background: var(--warning-gradient); }
        .stats-icon.value { background: var(--info-gradient); }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .stats-label {
            color: #718096;
            font-weight: 500;
            margin: 0;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }

        .card-header-custom {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem;
            border: none;
        }

        .card-header-custom h4 {
            margin: 0;
            font-weight: 600;
        }

        .search-section {
            background: #f7fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .search-input {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .table-custom {
            margin: 0;
        }

        .table-custom thead th {
            background-color: #f7fafc;
            border: none;
            font-weight: 600;
            color: #4a5568;
            padding: 1rem 0.75rem;
        }

        .table-custom tbody td {
            border: none;
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge-custom {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .stock-high { background-color: #d4edda; color: #155724; }
        .stock-medium { background-color: #fff3cd; color: #856404; }
        .stock-low { background-color: #f8d7da; color: #721c24; }

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
                Digital Book Inventory
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        Welcome, {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
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
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="stats-number">RM {{ number_format(\App\Models\Book::sum(\DB::raw('price * stock')), 0) }}</h3>
                    <p class="stats-label">Total Value</p>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Main Content -->
        <div class="content-card">
            <div class="card-header-custom">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4><i class="fas fa-list me-2"></i>Book Inventory</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-light btn-custom" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload me-1"></i>Import CSV
                        </button>
                        <a href="{{ route('books.create') }}" class="btn btn-success btn-custom">
                            <i class="fas fa-plus me-1"></i>Add Book
                        </a>
                        <form action="{{ route('books.reset') }}" method="POST" style="display: inline;"
                            onsubmit="return confirm('Are you sure you want to delete ALL books? This cannot be undone.');">
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
                <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-upload me-2"></i>Import Books from CSV
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Choose CSV File</label>
                            <input class="form-control" type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <div class="form-text">Select a CSV file with book data to import.</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>CSV Format:</strong> ISBN, Title, Authors/Editors, Year, Pages, Price, Category, Other Category, Stock
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-custom">
                            <i class="fas fa-upload me-1"></i>Import Books
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inline stock update functionality
            $('.inline-stock-form').on('submit', function (e) {
                e.preventDefault();

                const $form = $(this);
                const $button = $form.find('button');
                const bookId = $form.data('book-id');
                const formData = $form.serialize();

                $button.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'PATCH',
                    data: formData,
                    success: function () {
                        alert('✅ Stock updated for book ID ' + bookId);
                    },
                    error: function (xhr) {
                        alert('❌ Error updating stock: ' + xhr.responseJSON?.message);
                    },
                    complete: function () {
                        $button.prop('disabled', false).text('Save');
                    }
                });
            });

            // Live search functionality
            $('input[name="search"]').on('keyup', function () {
                let query = $(this).val();
                $.ajax({
                    url: "{{ route('books.index', [], true) }}",
                    type: "GET",
                    data: { search: query },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (data) {
                        $('#book-table').html(data);
                    },
                    error: function () {
                        console.log('Live search failed.');
                    }
                });
            });
        });

        // Barcode scanner functionality
        const scannerContainer = document.getElementById('scanner-container');
        const startScannerBtn = document.getElementById('startScanner');
        const closeScannerBtn = document.getElementById('closeScanner');
        const searchInput = document.getElementById('searchInput');

        let html5QrcodeScanner;

        startScannerBtn.addEventListener('click', () => {
            scannerContainer.style.display = 'block';
            html5QrcodeScanner = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: function (viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    return { width: minEdge * 0.8, height: minEdge * 0.8 };
                }
            };

            html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    console.log(`Code scanned: ${decodedText}`);
                    searchInput.value = decodedText;
                    document.getElementById('searchForm').submit();

                    html5QrcodeScanner.stop().then(() => {
                        scannerContainer.style.display = 'none';
                    });
                },
                (errorMessage) => {
                    // console.log(`Scan error: ${errorMessage}`);
                }
            ).catch((err) => {
                console.error(`Unable to start scanning: ${err}`);
            });
        });

        closeScannerBtn.addEventListener('click', () => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    scannerContainer.style.display = 'none';
                }).catch((err) => {
                    console.error(`Error stopping scanner: ${err}`);
                });
            }
        });
    </script>
</body>
</html>