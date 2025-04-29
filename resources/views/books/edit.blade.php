<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

    <h1 class="mb-4">Edit Book</h1>

    <a href="{{ route('books.index') }}" class="btn btn-secondary mb-4">← Back to Book List</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('books.update', $book->id) }}" method="POST" class="mb-5">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">ISBN:</label>
            <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Title:</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $book->title) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Authors/Editors:</label>
            <input type="text" name="authors_editors" class="form-control"
                value="{{ old('authors_editors', $book->authors_editors) }}" required>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Year:</label>
                <input type="number" name="year" class="form-control" value="{{ old('year', $book->year) }}">
            </div>
            <div class="col mb-3">
                <label class="form-label">Pages:</label>
                <input type="number" name="pages" class="form-control" value="{{ old('pages', $book->pages) }}">
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Price (MYR):</label>
                <input type="number" step="0.01" name="price" class="form-control"
                    value="{{ old('price', $book->price) }}">
            </div>
            <div class="col mb-3">
                <label class="form-label">Stock:</label>
                <input type="number" name="stock" class="form-control" value="{{ old('stock', $book->stock) }}">
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Category:</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $book->category) }}">
            </div>
            <div class="col mb-3">
                <label class="form-label">Other Category:</label>
                <input type="text" name="other_category" class="form-control"
                    value="{{ old('other_category', $book->other_category) }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Book</button>
    </form>

</body>

</html>