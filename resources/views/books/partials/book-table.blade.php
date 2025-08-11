<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
    --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.table-custom {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.table-custom thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: none;
    font-weight: 600;
    color: #4a5568;
    padding: 1.25rem 1rem;
    border-bottom: 2px solid #e2e8f0;
    position: relative;
}

.table-custom thead th a {
    color: #4a5568;
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
}

.table-custom thead th a:hover {
    color: #667eea;
    transform: translateY(-1px);
}

.table-custom tbody td {
    border: none;
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.table-custom tbody tr {
    transition: all 0.3s ease;
}

.table-custom tbody tr:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.table-custom tbody tr:last-child td {
    border-bottom: none;
}

/* Column widths */
.table-custom th:nth-child(1) { width: 12%; } /* ISBN */
.table-custom th:nth-child(2) { width: 25%; } /* Title */
.table-custom th:nth-child(3) { width: 22%; } /* Authors - Longer */
.table-custom th:nth-child(4) { width: 6%; }  /* Year - Shorter */
.table-custom th:nth-child(5) { width: 6%; }  /* Pages - Shorter */
.table-custom th:nth-child(6) { width: 8%; }  /* Price */
.table-custom th:nth-child(7) { width: 12%; } /* Category */
.table-custom th:nth-child(8) { width: 12%; } /* Stock */
.table-custom th:nth-child(9) { width: 8%; }  /* Actions */

/* ISBN Badge */
.badge {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%) !important;
    color: #4a5568 !important;
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Title styling */
.fw-bold {
    font-weight: 700 !important;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.text-muted {
    color: #718096 !important;
    font-size: 0.85rem;
}

/* Stock form styling */
.inline-stock-form {
    gap: 0.5rem;
}

.inline-stock-form .form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.5rem;
    width: 70px !important;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
}

.inline-stock-form .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.inline-stock-form .btn {
    padding: 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.inline-stock-form .btn-primary {
    background: var(--primary-gradient);
}

.inline-stock-form .btn:hover {
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
    background: var(--warning-gradient);
    color: white;
}

.action-buttons .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 216, 155, 0.4);
    color: white;
}

.action-buttons .btn-danger {
    background: var(--danger-gradient);
    color: #721c24;
}

.action-buttons .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 154, 158, 0.4);
    color: #721c24;
}

/* Empty state */
.text-center .text-muted {
    color: #718096 !important;
}

.text-center .text-muted i {
    color: #cbd5e0 !important;
    margin-bottom: 1rem;
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
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($books as $book)
            <tr>
                <td>
                    <span class="badge bg-light text-dark">{{ $book->isbn }}</span>
                </td>
                <td>
                    <div class="fw-bold">{{ $book->title }}</div>
                    @if($book->other_category)
                        <small class="text-muted">{{ $book->other_category }}</small>
                    @endif
                </td>
                <td>{{ $book->authors_editors }}</td>
                <td>{{ $book->year }}</td>
                <td>{{ $book->pages }}</td>
                <td>RM {{ number_format($book->price, 2) }}</td>
                <td>{{ $book->category }}</td>
                <td>
                    <form action="{{ route('books.updateStock', $book->id) }}" method="POST"
                        class="inline-stock-form d-flex align-items-center" data-book-id="{{ $book->id }}">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="stock" value="{{ $book->stock }}" min="0" 
                               class="form-control form-control-sm me-2" />
                        <button type="submit" class="btn btn-sm btn-primary" title="Save Stock">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" style="display: inline;"
                            onsubmit="return confirm('Are you sure you want to delete this book?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-book fa-3x mb-3"></i>
                        <p class="mb-0">No books found.</p>
                        @if(!empty($search))
                            <small>Try adjusting your search terms.</small>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>