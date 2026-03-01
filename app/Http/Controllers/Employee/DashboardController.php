<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load(['roles', 'divisi', 'jabatan', 'atasan']);

        $stats = [
            ['label' => 'Total Attendance', 'value' => 0, 'icon' => 'Clock', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], // Placeholder 
            ['label' => 'Total Tasks', 'value' => 0, 'icon' => 'CheckCircle', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50'], // Placeholder
        ];

        return Inertia::render('Employee/Index', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
