import './bootstrap';
import jQuery from 'jquery';
import { Html5Qrcode } from 'html5-qrcode';

// Make jQuery available globally
window.$ = window.jQuery = jQuery;

// Make Html5Qrcode available globally  
window.Html5Qrcode = Html5Qrcode;

document.addEventListener('DOMContentLoaded', function() {
    console.log('App.js loaded successfully - jQuery:', typeof $, 'Html5Qrcode:', typeof Html5Qrcode);
    
    // Setup CSRF token for all AJAX requests
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initial binding of table events
        bindTableEvents();

        // Live search functionality
        let searchTimeout;
        $('input[name="search"]').on('keyup', function () {
            let query = $(this).val();
            
            // Clear previous timeout to debounce the search
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(function() {
                console.log('Performing live search for:', query);
                
                // Get current sort parameters, but don't send if empty
                const urlParams = new URLSearchParams(window.location.search);
                const sortParam = urlParams.get('sort');
                const directionParam = urlParams.get('direction');
                
                let ajaxData = { search: query };
                
                // Only add sort parameters if they have valid values
                if (sortParam && sortParam !== '') {
                    ajaxData.sort = sortParam;
                }
                if (directionParam && directionParam !== '' && (directionParam === 'asc' || directionParam === 'desc')) {
                    ajaxData.direction = directionParam;
                }
                
                $.ajax({
                    url: window.location.pathname,
                    type: "GET",
                    data: ajaxData,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    beforeSend: function() {
                        console.log('Searching with data:', ajaxData);
                    },
                    success: function (data) {
                        console.log('Live search successful');
                        $('#book-table').html(data);
                        
                        // Re-bind any events that might have been lost
                        bindTableEvents();
                    },
                    error: function (xhr, status, error) {
                        console.error('Live search failed:', {
                            status: status,
                            error: error,
                            response: xhr.responseText,
                            url: window.location.pathname,
                            statusCode: xhr.status
                        });
                        
                        // Don't reload immediately - let's debug first
                        console.log('Full error response:', xhr.responseText);
                        
                        // Show user-friendly message
                        alert('Search error: ' + (xhr.status === 500 ? 'Server Error - Check console for details' : error));
                    }
                });
            }, 300); // Wait 300ms after user stops typing
        });
    }

});

// Function to bind table events (for stock updates, etc.)
function bindTableEvents() {
    // Remove existing event handlers to prevent duplicates
    $('.inline-stock-form').off('submit').on('submit', function (e) {
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
                console.error('Stock update error:', xhr);
                alert('❌ Error updating stock: ' + (xhr.responseJSON?.message || 'Unknown error'));
            },
            complete: function () {
                $button.prop('disabled', false).text('Save');
            }
        });
    });
}

