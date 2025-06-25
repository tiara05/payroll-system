<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat roles jika belum ada
        $roles = ['finance', 'manager', 'director'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Buat data employees & users yang punya role
        $data = [
            [
                'nip' => 'EMP037',
                'name' => 'Dina Keuangan',
                'divisi' => 'Staff Finance',
                'email' => 'dinaa@company.com',
                'role' => 'finance',
            ],
            [
                'nip' => 'EMP018',
                'name' => 'Budi Manager',
                'divisi' => 'Manager Operasional',
                'email' => 'budii@company.com',
                'role' => 'manager',
            ],
            [
                'nip' => 'EMP019',
                'name' => 'Rama Direktur',
                'divisi' => 'Direktur',
                'email' => 'ramaa@company.com',
                'role' => 'director',
            ],
        ];

        foreach ($data as $item) {
            $employee = Employee::create([
                'nip' => $item['nip'],
                'name' => $item['name'],
                'divisi' => $item['divisi'],
            ]);

            $user = User::create([
                'name' => $item['name'],
                'email' => $item['email'],
                'password' => Hash::make('password'),
                'employee_id' => $employee->id,
            ]);

            $user->assignRole($item['role']);
        }

        // Tambah satu user tanpa role
        $employee = Employee::create([
            'nip' => 'EMP999',
            'name' => 'Tiara Karyawan',
            'divisi' => 'Staff',
        ]);

        User::create([
            'name' => 'Tiara Karyawan',
            'email' => 'tiara@company.com',
            'password' => Hash::make('password'),
            'employee_id' => $employee->id,
        ]);
    }
}
