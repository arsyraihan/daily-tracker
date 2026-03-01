<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Divisions
        $divisi = [
            ['nama' => 'IT & Development', 'deskripsi' => 'Pengembangan sistem dan infrastruktur IT'],
            ['nama' => 'Human Resources', 'deskripsi' => 'Pengelolaan SDM dan rekrutmen'],
            ['nama' => 'Marketing', 'deskripsi' => 'Pemasaran dan hubungan masyarakat'],
            ['nama' => 'Operations', 'deskripsi' => 'Operasional harian perusahaan'],
        ];

        foreach ($divisi as $d) {
            Divisi::firstOrCreate(['nama' => $d['nama']], $d);
        }

        // Positions
        $jabatan = [
            ['nama' => 'Director', 'level' => 10],
            ['nama' => 'Manager', 'level' => 5],
            ['nama' => 'Supervisor', 'level' => 3],
            ['nama' => 'Team Leader', 'level' => 2],
            ['nama' => 'Senior Staff', 'level' => 1],
            ['nama' => 'Staff', 'level' => 1],
        ];

        foreach ($jabatan as $j) {
            Jabatan::firstOrCreate(['nama' => $j['nama']], $j);
        }
    }
}
