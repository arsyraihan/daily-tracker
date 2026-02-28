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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('pradiza123'),
            'role' => 'supervisor',
            'kode_karyawan' => 'SA001',
        ]);

        User::factory()->create([
            'name' => 'Arsy',
            'email' => 'Raihan@gmail.com',
            'password' => Hash::make('arsyraihan'),
            'role' => 'user',
            'kode_karyawan' => 'EMP001',
        ]);

        $this->call(RolesAndPermissionsSeeder::class);
    }
}
