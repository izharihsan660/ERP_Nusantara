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

        $superadmin = User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@naj.local')],
            [
                'name' => env('SUPERADMIN_NAME', 'Superadmin NAJ'),
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password')),
                'is_active' => true,
            ],
        );

        $superadmin->assignRole('Superadmin');
    }
}
