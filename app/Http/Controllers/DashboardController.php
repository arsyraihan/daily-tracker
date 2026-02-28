<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            'role' => $user->role ?? 'user',
            'stats' => [
                'total_hours' => 0,
                'goal_progress' => '0%',
                'active_team' => 0
            ]
        ]);
    }
}
