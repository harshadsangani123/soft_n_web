<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@yopmail.com',
            'password' => Hash::make('Admin123'),
            'role' => 'admin',
        ]);

        // Technicians
        User::create([
            'name' => 'John Technician',
            'email' => 'tech1@yopmail.com',
            'password' => Hash::make('Technician123'),
            'role' => 'technician',
            'is_available' => true,
        ]);

        User::create([
            'name' => 'Jane Technician',
            'email' => 'tech2@yopmail.com',
            'password' => Hash::make('Technician123'),
            'role' => 'technician',
            'is_available' => true,
        ]);

        // Customers
        User::create([
            'name' => 'Customer One',
            'email' => 'customer1@yopmail.com',
            'password' => Hash::make('Customer123'),
            'role' => 'customer',
        ]);

        User::create([
            'name' => 'Customer Two',
            'email' => 'customer2@yopmail.com',
            'password' => Hash::make('Customer123'),
            'role' => 'customer',
        ]);
    }
}
