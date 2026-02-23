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
            'name' => 'Solomon',
            'email' => 'solomon@gmail.com',
            'password' => Hash::make('jokowi'),
            'role' => 'supervisor',
        ]);

        User::factory()->create([
            'name' => 'Arsy',
            'email' => 'Raihan@gmail.com',
            'password' => Hash::make('arsyraihan'),
            'role' => 'user',
        ]);
    }
}
