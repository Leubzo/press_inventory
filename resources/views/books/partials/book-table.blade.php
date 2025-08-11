<table class="table table-custom">
    <thead>
        <tr>
            <th>
                <a href="{{ route('books.index', ['sort' => 'isbn', 'direction' => $sortField == 'isbn' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    ISBN <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'title', 'direction' => $sortField == 'title' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    Title <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'authors_editors', 'direction' => $sortField == 'authors_editors' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    Authors/Editors <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'year', 'direction' => $sortField == 'year' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    Year <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'pages', 'direction' => $sortField == 'pages' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    Pages <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'price', 'direction' => $sortField == 'price' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    Price (MYR) <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'category', 'direction' => $sortField == 'category' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                    Category <i class="fas fa-sort"></i>
                </a>
            </th>
            <th>Other Category</th>
            <th>
                <a href="{{ route('books.index', ['sort' => 'stock', 'direction' => $sortField == 'stock' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
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
                    @if($book->category)
                        <small class="text-muted">{{ $book->category }}</small>
                    @endif
                </td>
                <td>{{ $book->authors_editors }}</td>
                <td>{{ $book->year }}</td>
                <td>{{ $book->pages }}</td>
                <td>RM {{ number_format($book->price, 2) }}</td>
                <td>{{ $book->category }}</td>
                <td>{{ $book->other_category }}</td>
                <td>
                    <form action="{{ route('books.updateStock', $book->id) }}" method="POST"
                        class="inline-stock-form d-flex align-items-center" data-book-id="{{ $book->id }}">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="stock" value="{{ $book->stock }}" min="0" 
                               class="form-control form-control-sm me-2" style="width: 80px;" />
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning btn-sm me-2" title="Edit">Edit</a>
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" style="display: inline;"
                            onsubmit="return confirm('Are you sure you want to delete this book?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center py-4">
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