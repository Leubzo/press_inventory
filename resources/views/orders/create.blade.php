@extends('orders.layout')

@section('page-content')
<h5><i class="fas fa-plus me-2"></i>Create New Order</h5>
<p class="text-muted">Add books to create a new stock application form</p>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form id="orderForm" action="{{ route('orders.store') }}" method="POST">
    @csrf
    
    <!-- Purpose Field -->
    <div class="mb-4">
        <label for="purpose" class="form-label">Purpose (Optional)</label>
        <input type="text" id="purpose" name="purpose" class="form-control" 
               placeholder="Enter the purpose for this order..." value="{{ old('purpose') }}">
        <small class="text-muted">Optional: Describe the purpose or reason for this order</small>
    </div>

    <!-- Order Items -->
    <div class="mb-4">
        <h6>Order Items</h6>
        <div id="orderCart" class="order-cart">
            <div class="text-center text-muted py-3" id="emptyCart">
                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                <p>No books added yet. Search and add books below.</p>
            </div>
        </div>
    </div>

    <!-- Add Book Section -->
    <div class="mb-4">
        <h6>Add Book to Order</h6>
        <div class="row">
            <div class="col-md-6">
                <label for="bookSearch" class="form-label">Search Books</label>
                <div class="book-search-container">
                    <input type="text" id="bookSearch" class="form-control" 
                           placeholder="Type to search books by title, ISBN, or author...">
                    <div id="bookSuggestions" class="book-suggestions"></div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="orderQuantity" class="form-label">Quantity</label>
                <input type="number" id="orderQuantity" class="form-control" value="1" min="1">
            </div>
            <div class="col-md-2">
                <label for="unitPrice" class="form-label">Unit Price (RM)</label>
                <input type="number" id="unitPrice" class="form-control" step="0.01" min="0" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-gradient w-100" onclick="addBookToOrder()" id="addBookBtn" disabled>
                    <i class="fas fa-plus me-1"></i>Add Book
                </button>
            </div>
        </div>
    </div>

    <!-- Submit Section -->
    <div class="text-end">
        <button type="button" class="btn btn-outline-secondary me-2" onclick="clearOrder()">
            <i class="fas fa-times me-1"></i>Clear All
        </button>
        <button type="submit" class="btn btn-gradient" id="submitOrderBtn" disabled>
            <i class="fas fa-paper-plane me-1"></i>Submit Order
        </button>
    </div>
</form>

<script>
let orderItems = [];
let selectedBook = null;

document.addEventListener('DOMContentLoaded', function() {
    const bookSearch = document.getElementById('bookSearch');
    const suggestions = document.getElementById('bookSuggestions');
    let searchTimeout;

    // Book search functionality
    bookSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestions.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('sales.search-books') }}?search=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(books => {
                displayBookSuggestions(books);
            })
            .catch(error => {
                console.error('Error searching books:', error);
            });
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.book-search-container')) {
            suggestions.style.display = 'none';
        }
    });
});

function displayBookSuggestions(books) {
    const suggestions = document.getElementById('bookSuggestions');
    
    if (books.length === 0) {
        suggestions.innerHTML = '<div class="book-suggestion text-muted">No books found</div>';
        suggestions.style.display = 'block';
        return;
    }

    suggestions.innerHTML = books.map(book => `
        <div class="book-suggestion" onclick="selectBook(${book.id}, '${book.title.replace(/'/g, "\\'")}', '${book.isbn}', '${book.authors_editors.replace(/'/g, "\\'")}', ${book.selling_price})">
            <div><strong>${book.title}</strong></div>
            <div class="book-info">${book.authors_editors} | ISBN: ${book.isbn} | Stock: ${book.stock} | RM ${parseFloat(book.selling_price).toFixed(2)}</div>
        </div>
    `).join('');
    
    suggestions.style.display = 'block';
}

function selectBook(id, title, isbn, authors, price) {
    selectedBook = { id, title, isbn, authors, price };
    
    document.getElementById('bookSearch').value = `${title} (${isbn})`;
    document.getElementById('unitPrice').value = parseFloat(price).toFixed(2);
    document.getElementById('bookSuggestions').style.display = 'none';
    document.getElementById('addBookBtn').disabled = false;
}

function addBookToOrder() {
    if (!selectedBook) return;
    
    const quantity = parseInt(document.getElementById('orderQuantity').value);
    const price = parseFloat(document.getElementById('unitPrice').value);
    
    if (quantity <= 0 || price < 0) {
        alert('Please enter valid quantity and price.');
        return;
    }

    // Check if book already exists in order
    const existingIndex = orderItems.findIndex(item => item.book_id === selectedBook.id);
    
    if (existingIndex !== -1) {
        // Update existing item
        orderItems[existingIndex].quantity += quantity;
    } else {
        // Add new item
        orderItems.push({
            book_id: selectedBook.id,
            title: selectedBook.title,
            isbn: selectedBook.isbn,
            authors: selectedBook.authors,
            quantity: quantity,
            unit_price: price,
            item_number: orderItems.length + 1
        });
    }

    updateOrderDisplay();
    clearBookSearch();
}

function updateOrderDisplay() {
    const cart = document.getElementById('orderCart');
    const emptyCart = document.getElementById('emptyCart');
    const submitBtn = document.getElementById('submitOrderBtn');

    if (orderItems.length === 0) {
        emptyCart.style.display = 'block';
        submitBtn.disabled = true;
        return;
    }

    emptyCart.style.display = 'none';
    submitBtn.disabled = false;

    // Re-number items
    orderItems.forEach((item, index) => {
        item.item_number = index + 1;
    });

    const totalValue = orderItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);

    cart.innerHTML = orderItems.map(item => `
        <div class="order-item">
            <div class="item-number">${item.item_number}</div>
            <div class="row">
                <div class="col-md-5">
                    <strong>${item.title}</strong><br>
                    <small class="text-muted">${item.authors} | ISBN: ${item.isbn}</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" value="${item.quantity}" 
                           min="1" onchange="updateItemQuantity(${item.book_id}, this.value)">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" class="form-control" value="${item.unit_price}" 
                           step="0.01" min="0" onchange="updateItemPrice(${item.book_id}, this.value)">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <div class="form-control-plaintext">RM ${(item.quantity * item.unit_price).toFixed(2)}</div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                            onclick="removeItem(${item.book_id})" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('') + `
        <div class="mt-3 p-3 bg-light rounded">
            <div class="row">
                <div class="col-md-8">
                    <strong>Total Items: ${orderItems.length}</strong>
                </div>
                <div class="col-md-4 text-end">
                    <strong>Total Value: RM ${totalValue.toFixed(2)}</strong>
                </div>
            </div>
        </div>
    `;
}

function updateItemQuantity(bookId, newQuantity) {
    const item = orderItems.find(item => item.book_id === bookId);
    if (item) {
        item.quantity = parseInt(newQuantity);
        updateOrderDisplay();
    }
}

function updateItemPrice(bookId, newPrice) {
    const item = orderItems.find(item => item.book_id === bookId);
    if (item) {
        item.unit_price = parseFloat(newPrice);
        updateOrderDisplay();
    }
}

function removeItem(bookId) {
    orderItems = orderItems.filter(item => item.book_id !== bookId);
    updateOrderDisplay();
}

function clearBookSearch() {
    document.getElementById('bookSearch').value = '';
    document.getElementById('orderQuantity').value = '1';
    document.getElementById('unitPrice').value = '';
    document.getElementById('addBookBtn').disabled = true;
    selectedBook = null;
}

function clearOrder() {
    if (orderItems.length > 0 && !confirm('Are you sure you want to clear all items?')) {
        return;
    }
    
    orderItems = [];
    updateOrderDisplay();
    clearBookSearch();
}

// Handle form submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (orderItems.length === 0) {
        alert('Please add at least one book to the order.');
        return;
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('purpose', document.getElementById('purpose').value);
    
    orderItems.forEach((item, index) => {
        formData.append(`items[${index}][book_id]`, item.book_id);
        formData.append(`items[${index}][quantity]`, item.quantity);
        formData.append(`items[${index}][unit_price]`, item.unit_price);
    });

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Clear form and redirect
            orderItems = [];
            updateOrderDisplay();
            document.getElementById('purpose').value = '';
            window.location.href = '{{ route("orders.pending") }}';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the order.');
    });
});
</script>
@endsection