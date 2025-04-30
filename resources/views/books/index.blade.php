<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Book List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <h1>Books</h1>

    <h2>Import Books from CSV</h2>

    <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data" class="mb-3">
        @csrf
        <div class="mb-3">
            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Import CSV</button>
    </form>

    <hr>
    <h4 class="text-danger">When importing a clean CSV file with updated ISBNs</h4>


    <form action="{{ route('books.reset') }}" method="POST"
        onsubmit="return confirm('Are you sure you want to delete ALL books? This cannot be undone.');">
        @csrf
        <button type="submit" class="btn btn-danger mb-3">Reset Book Table</button>
    </form>

    <hr>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex justify-content-start mb-3">
        <a href="{{ route('books.create') }}" class="btn btn-success">+ Add New Book</a>
    </div>

    <form action="{{ route('books.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                placeholder="Search by ISBN, Title, or Authors/Editors">
            <button type="submit" class="btn btn-secondary">Search</button>
            @if(!empty($search))
                <a href="{{ route('books.index') }}" class="btn btn-link">Clear</a>
            @endif
        </div>
    </form>

    <hr>

    <div id="book-table">
        @include('books.partials.book-table', ['books' => $books])
    </div>

    <div class="d-flex justify-content-center">
        {{ $books->links() }}
    </div>

    <!-- jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Toast or Simple Alert -->
    <script>
        $(document).ready(function () {
            $('.inline-stock-form').on('submit', function (e) {
                e.preventDefault();

                const $form = $(this);
                const $button = $form.find('button');
                const bookId = $form.data('book-id');
                const formData = $form.serialize();

                $button.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'PATCH',
                    data: formData,
                    success: function () {
                        alert('✅ Stock updated for book ID ' + bookId);
                    },
                    error: function (xhr) {
                        alert('❌ Error updating stock: ' + xhr.responseJSON?.message);
                    },
                    complete: function () {
                        $button.prop('disabled', false).text('Save');
                    }
                });
            });
        });
    </script>
    
    <script>
        $(document).ready(function () {
            $('input[name="search"]').on('keyup', function () {
                let query = $(this).val();
                $.ajax({
                    url: "{{ route('books.index') }}",
                    type: "GET",
                    data: { search: query },
                    success: function (data) {
                        $('#book-table').html(data);
                    },
                    error: function () {
                        alert('Live search failed.');
                    }
                });
            });
        });
    </script>




</body>

</html>