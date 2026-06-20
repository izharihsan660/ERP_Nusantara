<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(MasterDataSeeder::class);

        $superadmin = User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@naj.local')],
            [
                'name' => env('SUPERADMIN_NAME', 'Superadmin NAJ'),
                'email_verified_at' => now(),
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password')),
                'is_active' => true,
            ],
        );

        $superadmin->assignRole('Superadmin');

        $dummyUsers = [
            ['name' => 'Sales NAJ', 'email' => 'sales@naj.local', 'role' => 'Sales'],
            ['name' => 'Gudang NAJ', 'email' => 'gudang@naj.local', 'role' => 'Gudang'],
            ['name' => 'Finance NAJ', 'email' => 'finance@naj.local', 'role' => 'Finance'],
            ['name' => 'Procurement NAJ', 'email' => 'procurement@naj.local', 'role' => 'Procurement'],
            ['name' => 'Manager NAJ', 'email' => 'manager@naj.local', 'role' => 'Manager'],
        ];

        foreach ($dummyUsers as $dummyUser) {
            $user = User::updateOrCreate(
                ['email' => $dummyUser['email']],
                [
                    'name' => $dummyUser['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
            );

            $user->syncRoles([$dummyUser['role']]);
        }
    }
}
