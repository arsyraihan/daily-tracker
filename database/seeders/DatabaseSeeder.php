<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Essential Users
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'RBAC Administrator',
                'password' => Hash::make('admin123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'chilmi@gmail.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('chilmi123'),
            ]
        );

        $this->call([
            RolesAndPermissionsSeeder::class,
            OrganizationSeeder::class,
        ]);
    }
}
