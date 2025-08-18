@extends('layouts.base')

@push('styles')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - UUM Press Inventory</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #f77062 0%, #fe5196 100%);
            --inventory-gradient: linear-gradient(135deg, #667eea 0%, #4facfe 100%);
            --sales-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
            --card-shadow-hover: 0 15px 40px rgba(0,0,0,0.15);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .navbar {
            background: var(--primary-gradient);
            box-shadow: var(--card-shadow);
            padding: 1rem 0;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.4rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .main-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .page-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }


        .page-title {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 0.5rem 0;
            line-height: 1.2;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 400;
            margin: 0;
        }

        .nav-pills {
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .nav-pills .nav-link {
            border-radius: 12px;
            font-weight: 600;
            padding: 1rem 1.5rem;
            margin: 0 0.5rem;
            transition: var(--transition);
            color: #6b7280;
            background: transparent;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-pills .nav-link:hover {
            color: #4f46e5;
            background: rgba(79, 70, 229, 0.05);
            border-color: rgba(79, 70, 229, 0.1);
            transform: translateY(-1px);
        }

        .nav-pills .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
            transform: translateY(-2px);
        }

        .nav-pills .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .stats-card.search-card {
            overflow: visible;
            z-index: 1;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        .stats-card h5 {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .stats-card h5 i {
            margin-right: 0.75rem;
            font-size: 1.3rem;
            opacity: 0.8;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .metric-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .metric-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-weight: 700;
            color: #1f2937;
            font-size: 1.3rem;
        }

        .metric-value.text-danger {
            color: #ef4444 !important;
        }

        .metric-value.text-warning {
            color: #f59e0b !important;
        }

        .metric-value.text-success {
            color: #10b981 !important;
        }

        .chart-container {
            position: relative;
            height: 350px;
            margin: 1rem 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: none;
            font-weight: 700;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            padding: 1rem;
        }

        .table tbody td {
            border-color: rgba(0,0,0,0.05);
            padding: 1rem;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,0.01);
        }

        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.2);
        }


        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            color: #6b7280;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(79, 70, 229, 0.05) 100%);
            color: #4f46e5;
            border-left: 4px solid #4f46e5;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .search-container {
            position: relative;
        }

        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.1);
            z-index: 1050;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            margin-top: 2px;
        }

        .search-suggestion {
            padding: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
            transition: var(--transition);
        }

        .search-suggestion:hover {
            background: rgba(79, 70, 229, 0.05);
        }

        .search-suggestion:last-child {
            border-bottom: none;
        }

        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(79, 70, 229, 0.2);
            border-radius: 50%;
            border-top-color: #4f46e5;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 0 0.5rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .nav-pills .nav-link {
                margin: 0.25rem;
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            .metric-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                height: 300px;
            }
        }

        .export-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .export-section .btn {
            margin: 0 0.25rem 0.5rem 0;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .status-indicator.success {
            background-color: #10b981;
        }

        .status-indicator.warning {
            background-color: #f59e0b;
        }

        .status-indicator.danger {
            background-color: #ef4444;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid main-container">
    <div class="page-header fade-in">
        <h1 class="page-title">
            <i class="fas fa-chart-line me-2"></i>Reports & Analytics
        </h1>
        <p class="page-subtitle">Comprehensive inventory and sales reporting with advanced analytics</p>
    </div>

    <div class="nav-pills custom-tabs fade-in">
        <ul class="nav nav-pills" id="reportsNav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}" 
                   href="{{ route('reports.inventory') }}">
                    <i class="fas fa-boxes"></i>Inventory Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}" 
                   href="{{ route('reports.sales') }}">
                    <i class="fas fa-chart-bar"></i>Sales Analytics
                </a>
            </li>
        </ul>
    </div>

    @yield('page-content')
</div>
@endsection

@push('scripts')
<script>
// Global chart configuration
Chart.defaults.font.family = 'Figtree, -apple-system, BlinkMacSystemFont, sans-serif';
Chart.defaults.font.weight = '500';
Chart.defaults.color = '#6b7280';
Chart.defaults.borderColor = 'rgba(0,0,0,0.1)';
Chart.defaults.backgroundColor = 'rgba(79, 70, 229, 0.1)';

// Global animations
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to dynamically loaded content
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observe all stats cards
    document.querySelectorAll('.stats-card').forEach(card => {
        observer.observe(card);
    });

    // Smooth scroll for navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Utility functions for reports
window.ReportsUtils = {
    formatCurrency: function(amount) {
        return 'RM ' + parseFloat(amount).toLocaleString('en-MY', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    
    formatNumber: function(number) {
        return parseInt(number).toLocaleString('en-MY');
    },
    
    showLoading: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<div class="text-center"><div class="loading-spinner"></div> Loading...</div>';
        }
    },
    
    hideLoading: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '';
        }
    },
    
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};
</script>
@stack('page-scripts')
@endpush