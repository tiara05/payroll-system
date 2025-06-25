<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role jika belum ada
        $roles = ['finance', 'manager', 'director'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Finance
        $finance = User::firstOrCreate(
            ['email' => 'finance@example.com'],
            [
                'name' => 'Finance User',
                'password' => Hash::make('password')
            ]
        );
        $finance->assignRole('finance');

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password')
            ]
        );
        $manager->assignRole('manager');

        // Director
        $director = User::firstOrCreate(
            ['email' => 'director@example.com'],
            [
                'name' => 'Director User',
                'password' => Hash::make('password')
            ]
        );
        $director->assignRole('director');
    }
}
