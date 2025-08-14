@extends('layouts.app')

@section('content')
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
        border-radius: 15px 15px 0 0;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: white;
        font-size: 1.5rem;
    }

    .stats-icon.sales { background: var(--primary-gradient); }
    .stats-icon.revenue { background: var(--success-gradient); }
    .stats-icon.quantity { background: var(--warning-gradient); }
    .stats-icon.platforms { background: var(--secondary-gradient); }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #718096;
        font-weight: 500;
        margin: 0;
    }

    .card-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .btn-gradient {
        background: var(--primary-gradient);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .search-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8f9fa;
        border: none;
        padding: 1rem;
        font-weight: 600;
        color: #495057;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
    }

    .warning-card {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffeaa7;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .book-search-container {
        position: relative;
    }

    .book-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .book-suggestion {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .book-suggestion:hover {
        background-color: #f8f9fa;
    }

    .book-suggestion:last-child {
        border-bottom: none;
    }

    .book-info {
        font-size: 0.9rem;
        color: #666;
    }

    .stock-warning {
        color: #dc3545;
        font-weight: bold;
    }

    .platform-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        margin: 0.1rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .platform-shopee { background-color: #ff5722; color: white; }
    .platform-lazada { background-color: #1565c0; color: white; }
    .platform-tiktok { background-color: #000; color: white; }
    .platform-facebook { background-color: #4267b2; color: white; }
    .platform-instagram { background-color: #e4405f; color: white; }
    .platform-offline { background-color: #6c757d; color: white; }

    @media (max-width: 768px) {
        .stats-card {
            margin-bottom: 1rem;
        }

        .table-responsive {
            font-size: 0.9rem;
        }

        .search-section {
            padding: 1rem;
        }
    }
</style>

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
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
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
            <a class="nav-link" href="{{ route('books.index') }}">
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
            <a class="nav-link" href="{{ route('reports.index') }}">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('sales.index') }}">
                <i class="fas fa-shopping-cart me-2"></i>Sales
            </a>
        </li>
    </ul>
</div>

<div class="container-fluid main-container">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon sales">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="stats-number">{{ $summaryStats['total_sales'] }}</h3>
                <p class="stats-label">Total Sales</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3 class="stats-number">RM {{ number_format($summaryStats['total_revenue'], 2) }}</h3>
                <p class="stats-label">Total Revenue</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon quantity">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="stats-number">{{ $summaryStats['total_quantity'] }}</h3>
                <p class="stats-label">Books Sold</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon platforms">
                    <i class="fas fa-store"></i>
                </div>
                <h3 class="stats-number">{{ $summaryStats['platforms']->count() }}</h3>
                <p class="stats-label">Active Platforms</p>
            </div>
        </div>
    </div>

    <!-- Stock Update Warning -->
    @if($booksNeedingUpdate->count() > 0)
    <div class="warning-card">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Stock Update Required</h5>
        <p>The following books have sales that exceed their current stock levels and need physical stock updates:</p>
        <div class="row">
            @foreach($booksNeedingUpdate as $book)
            <div class="col-md-6 mb-2">
                <strong>{{ $book->title }}</strong><br>
                <small>Current Stock: {{ $book->stock }} | Total Sold: {{ $book->total_sold }}</small>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Add New Sale Form -->
    <div class="card-container">
        <h5><i class="fas fa-plus me-2"></i>Record New Sale</h5>
        <form id="saleForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="bookSearch" class="form-label">Search Book</label>
                    <div class="book-search-container">
                        <input type="text" id="bookSearch" class="form-control" placeholder="Enter ISBN or title..." autocomplete="off">
                        <div id="bookSuggestions" class="book-suggestions"></div>
                        <input type="hidden" id="selectedBookId" name="book_id">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="platform" class="form-label">Platform</label>
                    <select id="platform" name="platform" class="form-select" required>
                        <option value="">Select Platform</option>
                        <option value="Shopee">Shopee</option>
                        <option value="Lazada">Lazada</option>
                        <option value="TikTok Shop">TikTok Shop</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Offline">Offline</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            
            <div id="selectedBookInfo" style="display: none;" class="mb-3 p-3 bg-light rounded">
                <!-- Book info will be displayed here -->
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="unit_price" class="form-label">Unit Price (RM)</label>
                    <input type="number" id="unit_price" name="unit_price" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="sale_date" class="form-label">Sale Date</label>
                    <input type="date" id="sale_date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="order_number" class="form-label">Order Number</label>
                    <input type="text" id="order_number" name="order_number" class="form-control" placeholder="Optional">
                </div>
            </div>

            <div class="mb-3">
                <label for="buyer_info" class="form-label">Buyer Information (Optional)</label>
                <textarea id="buyer_info" name="buyer_info" class="form-control" rows="2" placeholder="Customer name, address, etc."></textarea>
            </div>

            <button type="submit" class="btn btn-gradient">
                <i class="fas fa-save me-2"></i>Record Sale
            </button>
        </form>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-section">
        <div class="row">
            <div class="col-md-4 mb-3">
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search sales..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-3">
                <select name="platform" id="platformFilter" class="form-select">
                    <option value="">All Platforms</option>
                    @foreach($summaryStats['platforms'] as $platform)
                    <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ $platform }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <button type="button" class="btn btn-outline-primary" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card-container">
        <div id="sales-table">
            @include('sales.partials.sales-table')
        </div>
    </div>
</div>

<!-- Edit Sale Modal -->
<div class="modal fade" id="editSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSaleForm">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editSaleId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" id="editQuantity" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Price (RM)</label>
                            <input type="number" id="editUnitPrice" name="unit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Platform</label>
                            <select id="editPlatform" name="platform" class="form-select" required>
                                <option value="Shopee">Shopee</option>
                                <option value="Lazada">Lazada</option>
                                <option value="TikTok Shop">TikTok Shop</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Offline">Offline</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Date</label>
                            <input type="date" id="editSaleDate" name="sale_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Order Number</label>
                        <input type="text" id="editOrderNumber" name="order_number" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Buyer Information</label>
                        <textarea id="editBuyerInfo" name="buyer_info" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Update Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Book search functionality
    const bookSearch = document.getElementById('bookSearch');
    const bookSuggestions = document.getElementById('bookSuggestions');
    const selectedBookId = document.getElementById('selectedBookId');
    const selectedBookInfo = document.getElementById('selectedBookInfo');
    const unitPrice = document.getElementById('unit_price');

    let searchTimeout;

    bookSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            bookSuggestions.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/sales/search-books?search=${encodeURIComponent(query)}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(books => {
                    console.log('Books received:', books);
                    displayBookSuggestions(books);
                })
                .catch(error => {
                    console.error('Error searching books:', error);
                    bookSuggestions.style.display = 'none';
                });
        }, 300);
    });

    function displayBookSuggestions(books) {
        if (books.length === 0) {
            bookSuggestions.style.display = 'none';
            return;
        }

        bookSuggestions.innerHTML = books.map(book => `
            <div class="book-suggestion" data-book='${JSON.stringify(book)}'>
                <div><strong>${book.title}</strong></div>
                <div class="book-info">
                    ISBN: ${book.isbn} | Price: RM ${book.price} | Stock: ${book.stock}
                    ${book.needs_update ? '<span class="stock-warning"> ⚠️ Needs stock update</span>' : ''}
                </div>
            </div>
        `).join('');

        bookSuggestions.style.display = 'block';

        // Add click handlers
        bookSuggestions.querySelectorAll('.book-suggestion').forEach(suggestion => {
            suggestion.addEventListener('click', function() {
                const book = JSON.parse(this.dataset.book);
                selectBook(book);
            });
        });
    }

    function selectBook(book) {
        selectedBookId.value = book.id;
        bookSearch.value = `${book.isbn} - ${book.title}`;
        unitPrice.value = book.price;
        
        selectedBookInfo.innerHTML = `
            <h6>Selected Book:</h6>
            <div class="row">
                <div class="col-md-6">
                    <strong>${book.title}</strong><br>
                    <small>Authors: ${book.authors_editors}</small>
                </div>
                <div class="col-md-3">
                    Current Stock: <strong>${book.stock}</strong><br>
                    Total Sold: <strong>${book.total_sold}</strong>
                </div>
                <div class="col-md-3">
                    Price: <strong>RM ${book.price}</strong><br>
                    ${book.needs_update ? '<span class="stock-warning">⚠️ Needs stock update</span>' : '<span class="text-success">✓ Stock OK</span>'}
                </div>
            </div>
        `;
        
        selectedBookInfo.style.display = 'block';
        bookSuggestions.style.display = 'none';
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!bookSearch.contains(e.target) && !bookSuggestions.contains(e.target)) {
            bookSuggestions.style.display = 'none';
        }
    });

    // Sale form submission
    const saleForm = document.getElementById('saleForm');
    saleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("sales.store") }}', {
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
                this.reset();
                selectedBookInfo.style.display = 'none';
                selectedBookId.value = '';
                location.reload(); // Refresh to show new sale
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while recording the sale.');
        });
    });

    // Search functionality
    let searchTimeout2;
    const searchInput = document.getElementById('searchInput');
    const platformFilter = document.getElementById('platformFilter');

    function performSearch() {
        const searchValue = searchInput.value;
        const platformValue = platformFilter.value;
        
        const params = new URLSearchParams();
        if (searchValue) params.append('search', searchValue);
        if (platformValue) params.append('platform', platformValue);
        
        fetch(`${window.location.pathname}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('sales-table').innerHTML = html;
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    }

    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout2);
        searchTimeout2 = setTimeout(performSearch, 300);
    });

    platformFilter.addEventListener('change', performSearch);

    window.clearFilters = function() {
        searchInput.value = '';
        platformFilter.value = '';
        performSearch();
    };

    // Edit sale functionality
    window.editSale = function(saleId) {
        fetch(`/sales/${saleId}`)
            .then(response => response.json())
            .then(sale => {
                document.getElementById('editSaleId').value = sale.id;
                document.getElementById('editQuantity').value = sale.quantity;
                document.getElementById('editUnitPrice').value = sale.unit_price;
                document.getElementById('editPlatform').value = sale.platform;
                document.getElementById('editSaleDate').value = sale.sale_date;
                document.getElementById('editOrderNumber').value = sale.order_number || '';
                document.getElementById('editBuyerInfo').value = sale.buyer_info || '';
                
                new bootstrap.Modal(document.getElementById('editSaleModal')).show();
            })
            .catch(error => {
                console.error('Error loading sale:', error);
                alert('Error loading sale data');
            });
    };

    // Edit sale form submission
    document.getElementById('editSaleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const saleId = document.getElementById('editSaleId').value;
        const formData = new FormData(this);
        
        fetch(`/sales/${saleId}`, {
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
                bootstrap.Modal.getInstance(document.getElementById('editSaleModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the sale.');
        });
    });

    // Delete sale functionality
    window.deleteSale = function(saleId) {
        if (confirm('Are you sure you want to delete this sale?')) {
            fetch(`/sales/${saleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the sale.');
            });
        }
    };
});
</script>
@endsection