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
        color: #718096;
        background: #f7fafc;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
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

    .inline-stock-form input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

    /* Responsive */
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

        /* Reset column widths on mobile */
        .table-custom th {
            width: auto !important;
        }
    }
</style>

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
                <a href="{{ route('books.index', ['sort' => 'pages', 'direction' => $sortField == 'pages' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                    Pages <i class="fas fa-sort"></i>
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
            <td>{{ $book->pages ?? 'N/A' }}</td>
            <td>
                <span class="price-display">RM {{ number_format($book->price ?? 0, 2) }}</span>
            </td>
            <td>
                <span class="category-badge">{{ $book->category ?? 'Uncategorized' }}</span>
            </td>
            <td>
                <form action="{{ route('books.updateStock', $book) }}" method="POST" class="inline-stock-form" data-book-id="{{ $book->id }}">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="stock" value="{{ $book->stock ?? 0 }}" min="0" required>
                    <button type="submit"><i class="fas fa-save"></i></button>
                </form>
                @php
                $stockValue = $book->stock ?? 0;
                $stockLevel = $stockValue > 20 ? 'high' : ($stockValue > 10 ? 'medium' : 'low');
                $stockIcon = $stockValue > 20 ? 'check-circle' : ($stockValue > 10 ? 'exclamation-circle' : 'times-circle');
                @endphp
                <span class="stock-badge stock-{{ $stockLevel }} ms-2">
                    <i class="fas fa-{{ $stockIcon }}"></i>
                    {{ $stockValue }}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <a href="{{ route('books.edit', $book) }}" class="btn btn-warning btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>

                    <button type="button" class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#auditModal{{ $book->id }}"
                        title="View History">
                        <i class="fas fa-history"></i>
                    </button>

                    <form action="{{ route('books.destroy', $book) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Are you sure you want to delete this book?')"
                            title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
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
                                        {{ $log->created_at->format('M d, Y h:i A') }}
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