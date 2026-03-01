<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Redirect based on roles
        if ($user->hasRole('superadmin')) {
            $totalUsers = \App\Models\User::count();
            $stats = [
                ['label' => 'Total Employee', 'value' => $totalUsers, 'icon' => 'Users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'trend' => 'HR Metrics'],
                ['label' => 'Org Units', 'value' => \App\Models\Divisi::count(), 'icon' => 'Building', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'trend' => 'Hierarchy'],
                ['label' => 'Total Positions', 'value' => \App\Models\Jabatan::count(), 'icon' => 'Briefcase', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50', 'trend' => 'Structure'],
            ];

            return Inertia::render('Dashboard', [
                'stats' => $stats,
            ]);
        }

        if ($user->hasAnyRole(['manager', 'supervisor'])) {
            return redirect('/manager/dashboard');
        }

        return redirect('/employee/dashboard');
    }
}
