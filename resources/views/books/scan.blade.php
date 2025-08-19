@extends('layouts.base')

@push('styles')
<style>
    /* Mobile-first design for scanning page */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin: 0;
        padding: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .scan-container {
        min-height: 100vh;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        max-width: 100%;
    }

    .scan-header {
        text-align: center;
        color: white;
        margin-bottom: 2rem;
    }

    .scan-header h1 {
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }

    .scan-header p {
        opacity: 0.9;
        margin: 0;
        font-size: 1rem;
    }

    .scan-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .scanner-section {
        text-align: center;
        margin-bottom: 2rem;
    }

    .scanner-container {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .scanner-container.active {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    #scanner-display {
        width: 100%;
        border-radius: 10px;
        overflow: hidden;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        color: #6c757d;
        font-size: 1.1rem;
    }

    .scan-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        margin: 0.5rem;
        min-width: 200px;
        cursor: pointer;
    }

    .scan-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
    }

    .scan-btn:active {
        transform: translateY(0);
    }

    .scan-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .manual-input {
        margin-top: 1rem;
        text-align: center;
    }

    .manual-input input {
        width: 100%;
        max-width: 300px;
        padding: 1rem;
        border: 2px solid #e9ecef;
        border-radius: 15px;
        font-size: 1.1rem;
        text-align: center;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .manual-input input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .book-details {
        display: none;
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1rem 0;
        border: 2px solid #e9ecef;
    }

    .book-details.show {
        display: block;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .book-info h3 {
        color: #2d3748;
        margin: 0 0 1rem 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .book-info p {
        margin: 0.5rem 0;
        color: #4a5568;
        font-size: 0.95rem;
    }

    .stock-input-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .stock-input-section label {
        display: block;
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
    }

    .stock-input {
        width: 150px;
        padding: 1rem;
        border: 3px solid #e9ecef;
        border-radius: 15px;
        font-size: 1.5rem;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .stock-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .current-stock {
        display: inline-block;
        background: #e9ecef;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .btn-update {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
        min-width: 160px;
        cursor: pointer;
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-cancel {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        transition: all 0.3s ease;
        min-width: 160px;
        cursor: pointer;
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(108, 117, 125, 0.4);
    }

    .alert {
        border-radius: 15px;
        padding: 1rem;
        margin: 1rem 0;
        font-weight: 500;
        text-align: center;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }

    .back-link {
        position: absolute;
        top: 1rem;
        left: 1rem;
        color: white;
        text-decoration: none;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .scan-container {
            padding: 0.5rem;
        }
        
        .scan-card {
            padding: 1.5rem;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .scan-btn, .btn-update, .btn-cancel {
            min-width: 100%;
            max-width: 300px;
        }
    }
</style>
@endpush

@section('content')
<div class="scan-container">
    <a href="{{ route('books.index') }}" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>Back to Books
    </a>

    <div class="scan-header">
        <h1><i class="fas fa-camera me-2"></i>Barcode Scanner</h1>
        <p>Scan a book's barcode to quickly update stock</p>
    </div>

    <div class="scan-card">
        <!-- Scanner Section -->
        <div class="scanner-section">
            <div class="scanner-container" id="scanner-container">
                <div id="scanner-display">
                    <div>
                        <i class="fas fa-camera fa-3x mb-3 d-block"></i>
                        <p>Press "Start Scanning" to activate camera</p>
                    </div>
                </div>
            </div>
            
            <button type="button" class="scan-btn" id="start-scan-btn">
                <i class="fas fa-camera me-2"></i>Start Scanning
            </button>
            
            <button type="button" class="scan-btn" id="stop-scan-btn" style="display: none; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <i class="fas fa-stop me-2"></i>Stop Scanning
            </button>
        </div>

        <!-- Manual Input -->
        <div class="manual-input">
            <input type="text" id="manual-isbn" placeholder="Or enter ISBN manually" maxlength="20">
            <button type="button" class="scan-btn" id="manual-search-btn">
                <i class="fas fa-search me-2"></i>Search ISBN
            </button>
        </div>

        <!-- Alert Area -->
        <div id="alert-area"></div>

        <!-- Book Details Section -->
        <div class="book-details" id="book-details">
            <div class="book-info" id="book-info">
                <!-- Book details will be populated here -->
            </div>
            
            <div class="stock-input-section">
                <label for="new-stock">New Stock Quantity:</label>
                <input type="number" id="new-stock" class="stock-input" min="0" step="1">
                <div class="current-stock" id="current-stock">Current: 0</div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn-update" id="update-stock-btn">
                    <i class="fas fa-save me-2"></i>Update Stock
                </button>
                <button type="button" class="btn-cancel" id="cancel-btn">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let html5QrcodeScanner;
    let currentBook = null;

    const elements = {
        scannerContainer: document.getElementById('scanner-container'),
        scannerDisplay: document.getElementById('scanner-display'),
        startScanBtn: document.getElementById('start-scan-btn'),
        stopScanBtn: document.getElementById('stop-scan-btn'),
        manualIsbn: document.getElementById('manual-isbn'),
        manualSearchBtn: document.getElementById('manual-search-btn'),
        alertArea: document.getElementById('alert-area'),
        bookDetails: document.getElementById('book-details'),
        bookInfo: document.getElementById('book-info'),
        currentStock: document.getElementById('current-stock'),
        newStock: document.getElementById('new-stock'),
        updateStockBtn: document.getElementById('update-stock-btn'),
        cancelBtn: document.getElementById('cancel-btn')
    };

    // Start scanning
    elements.startScanBtn.addEventListener('click', startScanner);
    
    // Stop scanning
    elements.stopScanBtn.addEventListener('click', stopScanner);
    
    // Manual search
    elements.manualSearchBtn.addEventListener('click', () => {
        const isbn = elements.manualIsbn.value.trim();
        if (isbn) {
            searchBook(isbn);
        } else {
            showAlert('Please enter an ISBN', 'error');
        }
    });

    // Enter key on manual input
    elements.manualIsbn.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            elements.manualSearchBtn.click();
        }
    });

    // Update stock button
    elements.updateStockBtn.addEventListener('click', updateStock);
    
    // Cancel button
    elements.cancelBtn.addEventListener('click', () => {
        window.location.href = '{{ route("books.index") }}';
    });

    async function startScanner() {
        try {
            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('Scanner library not loaded');
            }

            elements.scannerContainer.classList.add('active');
            elements.startScanBtn.style.display = 'none';
            elements.stopScanBtn.style.display = 'inline-block';
            
            elements.scannerDisplay.innerHTML = '<div style="width: 100%; height: 250px;" id="qr-reader"></div>';
            
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
            
            const config = {
                fps: 10,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    return { width: minEdge * 0.8, height: minEdge * 0.8 };
                },
                aspectRatio: 1.0
            };

            await html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                (decodedText) => {
                    console.log(`Scanned: ${decodedText}`);
                    stopScanner();
                    searchBook(decodedText);
                },
                (errorMessage) => {
                    // Silent error handling
                }
            );

        } catch (err) {
            console.error('Scanner error:', err);
            stopScanner();
            showAlert('Camera not available. Please check permissions or use manual input.', 'error');
        }
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                resetScannerUI();
            }).catch(() => {
                resetScannerUI();
            });
        } else {
            resetScannerUI();
        }
    }

    function resetScannerUI() {
        elements.scannerContainer.classList.remove('active');
        elements.startScanBtn.style.display = 'inline-block';
        elements.stopScanBtn.style.display = 'none';
        elements.scannerDisplay.innerHTML = `
            <div>
                <i class="fas fa-camera fa-3x mb-3 d-block"></i>
                <p>Press "Start Scanning" to activate camera</p>
            </div>
        `;
    }

    function searchBook(isbn) {
        showAlert('Searching for book...', 'info');
        
        fetch(`{{ route('books.scan.search') }}?isbn=${encodeURIComponent(isbn)}`)
            .then(response => response.json())
            .then(data => {
                elements.alertArea.innerHTML = '';
                
                if (data.success) {
                    currentBook = data.book;
                    displayBookDetails(data.book);
                } else {
                    showAlert(data.error || 'Book not found', 'error');
                    elements.bookDetails.classList.remove('show');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showAlert('Error searching for book. Please try again.', 'error');
                elements.bookDetails.classList.remove('show');
            });
    }

    function displayBookDetails(book) {
        elements.bookInfo.innerHTML = `
            <h3><i class="fas fa-book me-2"></i>${book.title}</h3>
            <p><strong>ISBN:</strong> ${book.isbn}</p>
            <p><strong>Author/Editor:</strong> ${book.authors_editors}</p>
            <p><strong>Category:</strong> ${book.category}</p>
            <p><strong>Price:</strong> RM ${parseFloat(book.price).toFixed(2)}</p>
        `;
        
        elements.currentStock.textContent = `Current: ${book.current_stock}`;
        elements.newStock.value = book.current_stock;
        elements.newStock.focus();
        elements.newStock.select();
        
        elements.bookDetails.classList.add('show');
    }

    function updateStock() {
        if (!currentBook) {
            showAlert('No book selected', 'error');
            return;
        }

        const newStock = parseInt(elements.newStock.value);
        if (isNaN(newStock) || newStock < 0) {
            showAlert('Please enter a valid stock quantity', 'error');
            return;
        }

        elements.updateStockBtn.disabled = true;
        elements.updateStockBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'PATCH');
        formData.append('stock', newStock);

        fetch(`/books/${currentBook.id}/stock`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showAlert('Stock updated successfully! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("books.index") }}';
            }, 1500);
        })
        .catch(error => {
            console.error('Update error:', error);
            showAlert('Error updating stock. Please try again.', 'error');
            elements.updateStockBtn.disabled = false;
            elements.updateStockBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Stock';
        });
    }

    function showAlert(message, type) {
        const alertClass = type === 'error' ? 'alert-error' : (type === 'success' ? 'alert-success' : 'alert-info');
        elements.alertArea.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
        
        // Auto-hide info alerts
        if (type === 'info') {
            setTimeout(() => {
                if (elements.alertArea.innerHTML.includes(message)) {
                    elements.alertArea.innerHTML = '';
                }
            }, 3000);
        }
    }
});
</script>
@endpush
@endsection