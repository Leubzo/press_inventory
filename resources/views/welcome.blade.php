<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Book Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }
        
        .login-left {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .login-right {
            padding: 60px 40px;
        }
        
        .system-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .system-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .system-subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 30px;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            text-align: left;
        }
        
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            margin-right: 10px;
            width: 20px;
        }
        
        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
        }
        
        .form-title h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .form-title p {
            color: #666;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e1e5e9;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .login-left {
                padding: 40px 30px;
            }
            
            .login-right {
                padding: 40px 30px;
            }
            
            .system-title {
                font-size: 1.5rem;
            }
            
            .system-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0 h-100">
                <!-- Left Side - System Information -->
                <div class="col-lg-6">
                    <div class="login-left h-100">
                        <div>
                            <img src="{{ asset('images/uum-press-logo.jpg') }}" alt="UUM Press Logo" class="system-logo mb-3" style="max-width: 120px; height: auto; border-radius: 10px;">
                            <h1 class="system-title">UUM Press Inventory Management System</h1>
                            <p class="system-subtitle">Streamline your book inventory with modern technology</p>
                            
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Real-time inventory tracking</li>
                                <li><i class="fas fa-check"></i>Barcode scanning support</li>
                                <li><i class="fas fa-check"></i>CSV bulk import/export</li>
                                <li><i class="fas fa-check"></i>Advanced search & filtering</li>
                                <li><i class="fas fa-check"></i>Mobile-responsive design</li>
                                <li><i class="fas fa-check"></i>Secure data management</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Login Form -->
                <div class="col-lg-6">
                    <div class="login-right">
                        <div class="login-form">
                            <div class="form-title">
                                <img src="{{ asset('images/uum-press-logo.jpg') }}" alt="UUM Press Logo" style="max-width: 80px; height: auto; margin: 0 auto 20px; border-radius: 8px; display: block;">
                                <h2>Welcome to UUM Press</h2>
                                <p>Please sign in to access the inventory system</p>
                            </div>
                            
                            <!-- Display validation errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <!-- Display session messages -->
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif
                            
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               required 
                                               autocomplete="email" 
                                               autofocus
                                               placeholder="Enter your email">
                                    </div>
                                    @error('email')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required 
                                               autocomplete="current-password"
                                               placeholder="Enter your password">
                                    </div>
                                    @error('password')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
                            
                            <div class="forgot-password">
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">
                                        Forgot your password?
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Demo credentials -->
                            <div class="mt-4 pt-3 border-top">
                                <small class="text-muted d-block text-center">
                                    <strong>Demo Credentials:</strong><br>
                                    admin@bookstore.com<br>
                                    password123<br>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>