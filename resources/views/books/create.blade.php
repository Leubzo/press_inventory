<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
</head>
<body>
    <h1>Add New Book</h1>

    <a href="{{ route('books.index') }}">← Back to Book List</a>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('books.store') }}" method="POST">
        @csrf

        <label>ISBN:</label><br>
        <input type="text" name="isbn"><br><br>

        <label>Title:</label><br>
        <input type="text" name="title"><br><br>

        <label>Authors/Editors:</label><br>
        <input type="text" name="authors_editors"><br><br>

        <label>Year:</label><br>
        <input type="number" name="year"><br><br>

        <label>Pages:</label><br>
        <input type="number" name="pages"><br><br>

        <label>Price (MYR):</label><br>
        <input type="text" name="price"><br><br>

        <label>Category:</label><br>
        <input type="text" name="category"><br><br>

        <label>Other Category:</label><br>
        <input type="text" name="other_category"><br><br>

        <label>Stock:</label><br>
        <input type="number" name="stock"><br><br>

        <button type="submit">Add Book</button>
    </form>

</body>
</html>
