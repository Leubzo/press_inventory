<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'admin@bookstore.com')->first();
        
        if ($user) {
            $user->update(['role' => 'admin']);
            echo "User {$user->name} ({$user->email}) is now admin\n";
        } else {
            echo "User admin@bookstore.com not found\n";
        }
    }
}