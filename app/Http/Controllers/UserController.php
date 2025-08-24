<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only)
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user (Admin only)
     */
    public function create()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        return view('users.create');
    }

    /**
     * Store a newly created user in storage (Admin only)
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,salesperson,unit_head,storekeeper']
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            return redirect()->route('users.index')->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified user (Admin only)
     */
    public function edit(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage (Admin only)
     */
    public function update(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,salesperson,unit_head,storekeeper']
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('users.index')->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified user from storage (Admin only)
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only administrators can manage users.'
                ], 403);
            }
            abort(403, 'Access denied. Only administrators can manage users.');
        }

        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 400);
            }
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has associated orders
        $orderCount = $user->requestedOrders()->count() + 
                     $user->approvedOrders()->count() + 
                     $user->fulfilledOrders()->count();

        if ($orderCount > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with associated orders. Consider deactivating instead.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Cannot delete user with associated orders. Consider deactivating instead.');
        }

        try {
            $user->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully!'
                ]);
            }

            return redirect()->route('users.index')->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password (Admin only)
     */
    public function resetPassword(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only administrators can reset passwords.'
            ], 403);
        }

        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }
}