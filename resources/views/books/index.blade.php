<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Books</h1>
        <div>
            <span class="me-3">Welcome, {{ auth()->user()->name }}!</span>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
            </form>
        </div>
    </div>
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

    <form id="searchForm" action="{{ route('books.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}" class="form-control"
                placeholder="Search by ISBN, Title, or Authors/Editors">
            <button type="submit" class="btn btn-secondary">Search</button>
            <button type="button" class="btn btn-outline-primary" id="startScanner">Scan Barcode</button>
            @if(!empty($search))
            <a href="{{ route('books.index') }}" class="btn btn-link">Clear</a>
            @endif
        </div>
    </form>

    <!-- Hidden scanner box -->
    <div id="scanner-container" style="width: 100%; display: none; margin-top: 10px;">
        <div id="reader" style="width: 100%;"></div>
        <button class="btn btn-danger mt-2" id="closeScanner">Close Scanner</button>
    </div>

    <hr>

    <div id="book-table">
        @include('books.partials.book-table', ['books' => $books])
    </div>

    <div class="d-flex justify-content-center">
        {{ $books->links() }}
    </div>

    <!-- jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- HTML5-QRCode (CDN) -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- Bootstrap Toast or Simple Alert -->
    <script>
        $(document).ready(function() {
            $('.inline-stock-form').on('submit', function(e) {
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
                    success: function() {
                        alert('✅ Stock updated for book ID ' + bookId);
                    },
                    error: function(xhr) {
                        alert('❌ Error updating stock: ' + xhr.responseJSON?.message);
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Save');
                    }
                });
            });
        });
    </script>

    <script>
        console.log("Search URL: {{ route('books.index', [], true) }}");
        $(document).ready(function() {
            $('input[name="search"]').on('keyup', function() {
                let query = $(this).val();
                $.ajax({
                    url: "{{ route('books.index', [], true) }}",
                    type: "GET",
                    data: {
                        search: query
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(data) {
                        $('#book-table').html(data);
                    },
                    error: function() {
                        alert('Live search failed.');
                    }
                });
            });
        });
    </script>

    <script>
        const scannerContainer = document.getElementById('scanner-container');
        const startScannerBtn = document.getElementById('startScanner');
        const closeScannerBtn = document.getElementById('closeScanner');
        const searchInput = document.getElementById('searchInput');

        let html5QrcodeScanner;

        startScannerBtn.addEventListener('click', () => {
            scannerContainer.style.display = 'block';
            html5QrcodeScanner = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    return {
                        width: minEdge * 0.8,
                        height: minEdge * 0.8
                    }; // 80% of the smaller dimension
                }
            };

            html5QrcodeScanner.start({
                    facingMode: "environment"
                },
                config,
                (decodedText, decodedResult) => {
                    console.log(`Code scanned: ${decodedText}`);
                    // Insert scanned code into the search field
                    searchInput.value = decodedText;
                    // Optional: auto-submit the search form if you have one
                    document.getElementById('searchForm').submit();

                    // Stop scanner after successful scan
                    html5QrcodeScanner.stop().then(() => {
                        scannerContainer.style.display = 'none';
                    });
                },
                (errorMessage) => {
                    // console.log(`Scan error: ${errorMessage}`);
                }
            ).catch((err) => {
                console.error(`Unable to start scanning: ${err}`);
            });
        });

        closeScannerBtn.addEventListener('click', () => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    scannerContainer.style.display = 'none';
                }).catch((err) => {
                    console.error(`Error stopping scanner: ${err}`);
                });
            }
        });
    </script>


</body>

</html>