<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $manager = auth()->user();
        
        // Members under this manager
        $team = User::where('atasan_id', $manager->id)
            ->with(['roles', 'divisi', 'jabatan', 'atasan'])
            ->get();

        $stats = [
            ['label' => 'Team Members', 'value' => $team->count(), 'icon' => 'Users', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50'],
            ['label' => 'Active Sessions', 'value' => 0, 'icon' => 'Calendar', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], // Placeholder for step 4
        ];

        return Inertia::render('Manager/Index', [
            'team' => $team,
            'stats' => $stats,
        ]);
    }
}
