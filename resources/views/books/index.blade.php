<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book List</title>
</head>
<body>
    <h1>Books</h1>

    <a href="{{ route('books.create') }}">Add New Book</a>

    <h2>Import Books from CSV</h2>

        <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Import CSV</button>
        </form>

    <hr>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <h2>Search Books</h2>

    <form action="{{ route('books.index') }}" method="GET">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by ISBN, Title, or Author">
        <button type="submit">Search</button>
        @if(!empty($search))
            <a href="{{ route('books.index') }}">Clear</a>
        @endif
    </form>

    <hr>

    
    <table border="1" cellpadding="5" cellspacing="0">
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

</body>
</html>
