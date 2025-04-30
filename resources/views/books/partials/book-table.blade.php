<table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th><a
                        href="{{ route('books.index', ['sort' => 'isbn', 'direction' => $sortField == 'isbn' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">ISBN</a>
                </th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'title', 'direction' => $sortField == 'title' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Title</a>
                </th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'authors_editors', 'direction' => $sortField == 'authors_editors' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Authors/Editors</a>
                </th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'year', 'direction' => $sortField == 'year' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Year</a>
                </th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'pages', 'direction' => $sortField == 'pages' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Pages</a>
                </th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'price', 'direction' => $sortField == 'price' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Price
                        (MYR)</a></th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'category', 'direction' => $sortField == 'category' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Category</a>
                </th>
                <th>Other Category</th>
                <th><a
                        href="{{ route('books.index', ['sort' => 'stock', 'direction' => $sortField == 'stock' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Stock</a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($books as $book)
                <tr>
                    <td>{{ $book->isbn }}</td>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->authors_editors }}</td>
                    <td>{{ $book->year }}</td>
                    <td>{{ $book->pages }}</td>
                    <td>{{ $book->price }}</td>
                    <td>{{ $book->category }}</td>
                    <td>{{ $book->other_category }}</td>
                    <td>
                        <form action="{{ route('books.updateStock', $book->id) }}" method="POST"
                            class="inline-stock-form d-flex align-items-center" data-book-id="{{ $book->id }}">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="stock" value="{{ $book->stock }}" min="0" class="form-control form-control-sm"
                                style="width: 80px;" />
                            <button type="submit" class="btn btn-sm btn-primary ms-2">Save</button>
                        </form>
                    </td>

                    <td class="d-flex">
                        <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning btn-sm me-2">Edit</a>

                        <form action="{{ route('books.destroy', $book->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this book?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>