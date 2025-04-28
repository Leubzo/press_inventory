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

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ISBN</th>
                <th>Title</th>
                <th>Authors/Editors</th>
                <th>Year</th>
                <th>Pages</th>
                <th>Price (MYR)</th>
                <th>Category</th>
                <th>Other Category</th>
                <th>Stock</th>
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
