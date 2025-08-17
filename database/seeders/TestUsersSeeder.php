<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Sales Person',
                'email' => 'sales@uumpress.com',
                'password' => Hash::make('password123'),
                'role' => 'salesperson'
            ],
            [
                'name' => 'Unit Head',
                'email' => 'unithead@uumpress.com', 
                'password' => Hash::make('password123'),
                'role' => 'unit_head'
            ],
            [
                'name' => 'Store Keeper',
                'email' => 'storekeeper@uumpress.com',
                'password' => Hash::make('password123'),
                'role' => 'storekeeper'
            ]
        ];

        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();
            
            if ($user) {
                $user->update(['role' => $userData['role']]);
                echo "Updated existing user: {$user->name} ({$user->email}) role: {$userData['role']}\n";
            } else {
                $user = User::create($userData);
                echo "Created new user: {$user->name} ({$user->email}) role: {$userData['role']}\n";
            }
        }
    }
}