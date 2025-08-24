@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit User: {{ $user->name }}
                    </h5>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
                <div class="card-body">
                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control @error('role') is-invalid @enderror" 
                                    id="role" name="role" required>
                                <option value="">Select a role...</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="unit_head" {{ old('role', $user->role) === 'unit_head' ? 'selected' : '' }}>Unit Head</option>
                                <option value="storekeeper" {{ old('role', $user->role) === 'storekeeper' ? 'selected' : '' }}>Storekeeper</option>
                                <option value="salesperson" {{ old('role', $user->role) === 'salesperson' ? 'selected' : '' }}>Salesperson</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small>
                                    <strong>Administrator:</strong> Full access to all features including user management<br>
                                    <strong>Unit Head:</strong> Can approve/reject orders<br>
                                    <strong>Storekeeper:</strong> Can fulfill approved orders<br>
                                    <strong>Salesperson:</strong> Can create new orders
                                </small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" minlength="8">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 8 characters (only fill if changing password)</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" minlength="8">
                        </div>

                        @if($user->id === auth()->id())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You are editing your own account.
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Created: {{ $user->created_at->format('M j, Y g:i A') }}
                                    @if($user->updated_at->ne($user->created_at))
                                        <br>
                                        <i class="fas fa-edit me-1"></i>
                                        Last updated: {{ $user->updated_at->format('M j, Y g:i A') }}
                                    @endif
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-gradient">
                                    <i class="fas fa-save me-1"></i>Update User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection