@php
$sortField = $sortField ?? request('sort', 'title');
$sortDirection = $sortDirection ?? request('direction', 'asc');
@endphp

<style>
    /* Modern Table Styling */
    .table-custom {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .table-custom thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .table-custom thead th {
        color: white !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
        background: transparent;
    }

    .table-custom thead th a {
        color: white !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    .table-custom thead th a:hover {
        opacity: 0.8;
        color: #f0f0f0 !important;
    }

    .table-custom thead th a i {
        color: white !important;
        margin-left: 0.5rem;
    }

    .table-custom tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .table-custom tbody tr:hover {
        background: linear-gradient(90deg, #f8f9ff 0%, #f0f4ff 100%);
        transform: scale(1.01);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .table-custom tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: #2d3748;
    }

    /* Stock badge styling */
    .stock-badge {
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .stock-high {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #0a5f3e;
    }

    .stock-medium {
        background: linear-gradient(135deg, #ffd89b 0%, #ffb347 100%);
        color: #7c4a00;
    }

    .stock-low {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        color: #721c24;
    }

    .stock-badge i {
        font-size: 0.75rem;
    }

    /* Price styling */
    .price-display {
        font-weight: 600;
        color: #48bb78;
    }

    /* Category badge */
    .category-badge {
        padding: 0.35rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        font-weight: 500;
    }

    /* ISBN styling */
    .isbn-code {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 500;
    }

    /* Title and author styling */
    .book-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }

    .book-authors {
        font-size: 0.9rem;
        color: #718096;
    }

    /* Year badge */
    .year-badge {
        background: #f0f4f8;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        color: #4a5568;
        font-weight: 500;
    }

    /* Inline stock form */
    .inline-stock-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .inline-stock-form input {
        width: 80px;
        padding: 0.35rem 0.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .inline-stock-form input.stock-high {
        border-color: #84fab0;
        background: rgba(132, 250, 176, 0.1);
    }

    .inline-stock-form input.stock-medium {
        border-color: #ffd89b;
        background: rgba(255, 216, 155, 0.1);
    }

    .inline-stock-form input.stock-low {
        border-color: #ff9a9e;
        background: rgba(255, 154, 158, 0.1);
    }

    .inline-stock-form input:focus {
        outline: none;
        border-color: #667eea !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white !important;
    }

    .inline-stock-form button {
        padding: 0.35rem 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .inline-stock-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }


    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .action-buttons .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-buttons .btn-warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .action-buttons .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 216, 155, 0.4);
        color: white;
    }

    .action-buttons .btn-danger {
        background: linear-gradient(135deg, #f77062 0%, #fe5196 100%);
        color: white;
    }

    .action-buttons .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 154, 158, 0.4);
        color: white;
    }

    .action-buttons .btn-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .action-buttons .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
        color: white;
    }

    /* Empty state */
    .text-center .text-muted {
        color: #718096 !important;
    }

    .text-center .text-muted i {
        color: #cbd5e0 !important;
        margin-bottom: 1rem;
    }

    /* Timeline styles for modal */
    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-badge {
        position: absolute;
        left: -25px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
    }

    .timeline-badge.created {
        background: #28a745;
    }

    .timeline-badge.updated {
        background: #007bff;
    }

    .timeline-badge.deleted {
        background: #dc3545;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-left: 15px;
    }

    .change-summary {
        font-size: 0.9rem;
        padding: 5px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .change-summary:last-child {
        border-bottom: none;
    }

    /* Mobile-friendly table scrolling */
    .table-scroll-container {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
        scroll-behavior: smooth;
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .table-scroll-container::before,
    .table-scroll-container::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 20px;
        pointer-events: none;
        z-index: 2;
    }

    /* Left scroll indicator */
    .table-scroll-container::before {
        left: 0;
        background: linear-gradient(to right, rgba(255,255,255,0.9), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* Right scroll indicator */
    .table-scroll-container::after {
        right: 0;
        background: linear-gradient(to left, rgba(255,255,255,0.9), transparent);
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .table-scroll-container.scrolled-left::before {
        opacity: 1;
    }

    .table-scroll-container.scrolled-right::after {
        opacity: 0;
    }

    /* Make ISBN column sticky for better navigation */
    .table-custom th:first-child,
    .table-custom td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 1;
        box-shadow: 2px 0 4px rgba(0,0,0,0.1);
    }

    .table-custom thead th:first-child {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Desktop: Make table fit screen width */
    @media (min-width: 769px) {
        .table-scroll-container {
            overflow-x: visible; /* No horizontal scroll on desktop */
        }
        
        .table-custom {
            width: 100%;
            table-layout: fixed; /* Fixed layout for consistent column widths */
        }
        
        .table-custom th,
        .table-custom td {
            white-space: normal; /* Allow text wrapping on desktop */
            word-wrap: break-word;
        }
        
        /* Define proportional column widths for desktop */
        .table-custom th:nth-child(1), .table-custom td:nth-child(1) { width: 12%; } /* ISBN */
        .table-custom th:nth-child(2), .table-custom td:nth-child(2) { width: 18%; } /* Title */
        .table-custom th:nth-child(3), .table-custom td:nth-child(3) { width: 15%; } /* Authors */
        .table-custom th:nth-child(4), .table-custom td:nth-child(4) { width: 8%; }  /* Year */
        .table-custom th:nth-child(5), .table-custom td:nth-child(5) { width: 10%; } /* Price */
        .table-custom th:nth-child(6), .table-custom td:nth-child(6) { width: 10%; } /* Category */
        .table-custom th:nth-child(7), .table-custom td:nth-child(7) { width: 10%; } /* Other Category */
        .table-custom th:nth-child(8), .table-custom td:nth-child(8) { width: 9%; }  /* Stock */
        .table-custom th:nth-child(9), .table-custom td:nth-child(9) { width: 8%; }  /* Actions */
        
        /* Remove sticky positioning and shadows on desktop */
        .table-custom th:first-child,
        .table-custom td:first-child {
            position: static;
            box-shadow: none;
        }
        
        /* Hide scroll indicators on desktop */
        .table-scroll-container::before,
        .table-scroll-container::after {
            display: none;
        }
    }

    /* Mobile: Maintain horizontal scrolling */
    @media (max-width: 768px) {
        .table-custom th,
        .table-custom td {
            min-width: 120px;
            white-space: nowrap;
        }

        .table-custom th:first-child,
        .table-custom td:first-child {
            min-width: 140px; /* Slightly wider for ISBN */
        }

        .table-custom th:last-child,
        .table-custom td:last-child {
            min-width: 160px; /* Wider for actions */
        }
        
        /* Force table to be wider than mobile screen for scrolling */
        .table-custom {
            min-width: 1000px;
        }
    }

    /* Additional mobile responsive adjustments */
    @media (max-width: 768px) {
        .table-custom thead th,
        .table-custom tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.9rem;
        }

        .action-buttons {
            flex-direction: column;
            gap: 0.25rem;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0.4rem;
        }

        /* Keep scroll indicators visible on mobile */
        .table-scroll-container::before,
        .table-scroll-container::after {
            width: 15px;
        }
    }
</style>

<!-- Mobile-friendly scrollable container -->
<div class="table-scroll-container">
    <table class="table table-custom">
    <thead>
        <tr>
            <th>
                <a href="{{ route('books.index', ['sort' => 'isbn', 'direction' => $sortField == 'isbn' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    ISBN <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'title', 'direction' => $sortField == 'title' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Title <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'authors_editors', 'direction' => $sortField == 'authors_editors' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Authors/Editors <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'year', 'direction' => $sortField == 'year' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Year <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'price', 'direction' => $sortField == 'price' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Price (MYR) <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'category', 'direction' => $sortField == 'category' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Category <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'other_category', 'direction' => $sortField == 'other_category' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Other Category <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'stock', 'direction' => $sortField == 'stock' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Stock <i class="fas fa-sort"></i>
                </a>
            </th>
            <th class="text-center">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($books as $book)
        <tr>
            <td>
                <span class="isbn-code">{{ $book->isbn }}</span>
            </td>
            <td>
                <div class="book-title">{{ $book->title }}</div>
            </td>
            <td>
                <div class="book-authors">{{ \Illuminate\Support\Str::limit($book->authors_editors, 50) }}</div>
            </td>
            <td>
                <span class="year-badge">{{ $book->year ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="price-display">RM {{ number_format($book->price ?? 0, 2) }}</span>
            </td>
            <td>
                <span class="category-badge">{{ $book->category_display }}</span>
            </td>
            <td>
                <span class="category-badge">{{ $book->other_category_display }}</span>
            </td>
            <td>
                @php
                $stockValue = $book->stock ?? 0;
                $stockLevel = $stockValue >= 10 ? 'high' : ($stockValue >= 1 ? 'medium' : 'low');
                $stockIcon = $stockValue >= 10 ? 'check' : ($stockValue >= 1 ? 'exclamation' : 'times');
                @endphp

                @if(auth()->user()->canManageInventory())
                    <form action="{{ route('books.updateStock', $book) }}" method="POST" class="inline-stock-form" data-book-id="{{ $book->id }}">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="stock" value="{{ $stockValue }}" min="0" required class="stock-{{ $stockLevel }}">
                        <button type="submit"><i class="fas fa-save"></i></button>
                    </form>
                @else
                    <span class="stock-badge stock-{{ $stockLevel }}">
                        <i class="fas fa-{{ $stockIcon }}"></i>
                        {{ $stockValue }}
                    </span>
                @endif
            </td>
            <td>
                <div class="action-buttons">
                    @if(auth()->user()->canManageInventory())
                        <button type="button" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal{{ $book->id }}"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endif

                    <button type="button" class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#auditModal{{ $book->id }}"
                        title="View History">
                        <i class="fas fa-history"></i>
                    </button>

                    @if(auth()->user()->canManageInventory())
                        <form action="{{ route('books.destroy', $book) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this book?')"
                                title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>

        <!-- Audit History Modal for each book -->
        <div class="modal fade" id="auditModal{{ $book->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-history me-2"></i>Audit History: {{ \Illuminate\Support\Str::limit($book->title, 40) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @php
                        try {
                            $bookLogs = $book->auditLogs()->take(5)->get();
                        } catch (\Exception $e) {
                            $bookLogs = collect(); // Empty collection if audit logs fail
                        }
                        @endphp

                        @if($bookLogs->count() > 0)
                        <div class="timeline">
                            @foreach($bookLogs as $log)
                            <div class="timeline-item">
                                <div class="timeline-badge {{ $log->action ?? 'updated' }}">
                                    @if(($log->action ?? 'updated') == 'created')
                                    <i class="fas fa-plus"></i>
                                    @elseif(($log->action ?? 'updated') == 'updated')
                                    <i class="fas fa-edit"></i>
                                    @else
                                    <i class="fas fa-trash"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ ucfirst($log->action ?? 'Updated') }}</h6>
                                    <small class="text-muted">
                                        {{ $log->created_at->setTimezone('Asia/Kuala_Lumpur')->format('M d, Y h:i A') }} MYT
                                        by {{ $log->user_identifier ?? 'System' }}
                                    </small>

                                    @if(($log->action ?? 'updated') == 'updated' && method_exists($log, 'getReadableChanges') && $log->getReadableChanges())
                                    <div class="mt-2">
                                        @foreach($log->getReadableChanges() as $change)
                                        <div class="change-summary">
                                            <strong>{{ $change['field'] }}:</strong>
                                            <span class="text-danger">{{ $change['old'] }}</span>
                                            →
                                            <span class="text-success">{{ $change['new'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('audit-logs.index', ['book_id' => $book->id]) }}" class="btn btn-primary btn-sm">
                                View Full History
                            </a>
                        </div>
                        @else
                        <p class="text-muted text-center">No audit history available for this book.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->canManageInventory())
        <!-- Edit Book Modal for each book -->
        <div class="modal fade" id="editModal{{ $book->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Book: {{ \Illuminate\Support\Str::limit($book->title, 40) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('books.update', $book->id) }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="modal-body">
                            <!-- Error messages will be shown at page level -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_isbn_{{ $book->id }}" class="form-label">ISBN *</label>
                                    <input type="text" class="form-control" id="edit_isbn_{{ $book->id }}" name="isbn" value="{{ $book->isbn }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_title_{{ $book->id }}" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="edit_title_{{ $book->id }}" name="title" value="{{ $book->title }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_authors_editors_{{ $book->id }}" class="form-label">Authors/Editors *</label>
                                <input type="text" class="form-control" id="edit_authors_editors_{{ $book->id }}" name="authors_editors" value="{{ $book->authors_editors }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="edit_year_{{ $book->id }}" class="form-label">Year</label>
                                    <input type="number" class="form-control" id="edit_year_{{ $book->id }}" name="year" value="{{ $book->year }}" min="1900" max="2099">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_pages_{{ $book->id }}" class="form-label">Pages</label>
                                    <input type="number" class="form-control" id="edit_pages_{{ $book->id }}" name="pages" value="{{ $book->pages }}" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_price_{{ $book->id }}" class="form-label">Price (RM)</label>
                                    <input type="number" class="form-control" id="edit_price_{{ $book->id }}" name="price" value="{{ $book->price }}" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="edit_category_{{ $book->id }}" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="edit_category_{{ $book->id }}" name="category" value="{{ $book->category }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_other_category_{{ $book->id }}" class="form-label">Other Category</label>
                                    <input type="text" class="form-control" id="edit_other_category_{{ $book->id }}" name="other_category" value="{{ $book->other_category }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="edit_stock_{{ $book->id }}" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="edit_stock_{{ $book->id }}" name="stock" value="{{ $book->stock }}" min="0">
                                </div>
                            </div>
                        </div>
                        <!-- End modal-body -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @empty
        <tr>
            <td colspan="9" class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <p class="text-muted">No books found. Start by adding your first book!</p>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>