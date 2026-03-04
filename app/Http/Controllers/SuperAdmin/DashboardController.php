<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $stats = [
            ['label' => 'Total Employee', 'value' => $totalUsers, 'icon' => 'Users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'trend' => 'HR Metrics'],
            ['label' => 'Org Units', 'value' => Divisi::count(), 'icon' => 'Building', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'trend' => 'Hierarchy'],
            ['label' => 'Total Positions', 'value' => Jabatan::count(), 'icon' => 'Briefcase', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50', 'trend' => 'Structure'],
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
