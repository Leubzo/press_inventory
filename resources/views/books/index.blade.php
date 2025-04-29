<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <h1>Books</h1>

    <a href="{{ route('books.create') }}">Add New Book</a>

    <h2>Import Books from CSV</h2>

    <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data" class="mb-3">
        @csrf
        <div class="mb-3">
            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Import CSV</button>
    </form>

    <hr>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('books.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by ISBN, Title, or Authors/Editors">
            <button type="submit" class="btn btn-secondary">Search</button>
            @if(!empty($search))
                <a href="{{ route('books.index') }}" class="btn btn-link">Clear</a>
            @endif
        </div>
    </form>

    <hr>

    
    <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th><a href="{{ route('books.index', ['sort' => 'isbn', 'direction' => $sortField == 'isbn' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">ISBN</a></th>
            <th><a href="{{ route('books.index', ['sort' => 'title', 'direction' => $sortField == 'title' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Title</a></th>
            <th><a href="{{ route('books.index', ['sort' => 'authors_editors', 'direction' => $sortField == 'authors_editors' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Authors/Editors</a></th>
            <th><a href="{{ route('books.index', ['sort' => 'year', 'direction' => $sortField == 'year' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Year</a></th>
            <th><a href="{{ route('books.index', ['sort' => 'pages', 'direction' => $sortField == 'pages' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Pages</a></th>
            <th><a href="{{ route('books.index', ['sort' => 'price', 'direction' => $sortField == 'price' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Price (MYR)</a></th>
            <th><a href="{{ route('books.index', ['sort' => 'category', 'direction' => $sortField == 'category' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Category</a></th>
            <th>Other Category</th>
            <th><a href="{{ route('books.index', ['sort' => 'stock', 'direction' => $sortField == 'stock' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">Stock</a></th>
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
                    <td>{{ $book->stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $books->links() }}
    </div>


</body>
</html>
