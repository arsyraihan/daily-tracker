<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ActivityService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private ActivityService $activityService
    ) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'supervisor') {
            $activities = $this->activityService->getAllActivitiesForSupervisor();
        } else {
            $activities = $this->activityService->getUserActivities($user->id);
        }

        return Inertia::render('Dashboard', [
            'activities' => $activities,
            'role' => $user->role
        ]);
    }
}
