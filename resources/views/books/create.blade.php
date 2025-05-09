<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

    <h1 class="mb-4">Add New Book</h1>

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

    <form action="{{ route('books.store') }}" method="POST" class="mb-5">
        @csrf

        <div class="mb-3">
            <label class="form-label">ISBN:</label>
            <input type="text" name="isbn" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Title:</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Authors/Editors:</label>
            <input type="text" name="authors_editors" class="form-control" required>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Year:</label>
                <input type="number" name="year" class="form-control">
            </div>
            <div class="col mb-3">
                <label class="form-label">Pages:</label>
                <input type="number" name="pages" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Price (MYR):</label>
                <input type="number" step="0.01" name="price" class="form-control">
            </div>
            <div class="col mb-3">
                <label class="form-label">Stock:</label>
                <input type="number" name="stock" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Category:</label>
                <input type="text" name="category" class="form-control">
            </div>
            <div class="col mb-3">
                <label class="form-label">Other Category:</label>
                <input type="text" name="other_category" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Book</button>
    </form>

</body>

</html>