<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ActivityService;
use App\Http\Requests\StoreActivityRequest;
use Inertia\Inertia;
use App\Repositories\ActivityRepository;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function __construct(
        private ActivityService $activityService
    ) {}
    public function index()
    {
        $userId = auth()->id();
        $activities = $this->activityService->getUserActivities($userId);

        return Inertia::render('Activities/Index', [
            'activities' => $activities
        ]);
    }

    public function store(StoreActivityRequest $request)
    {
        $this->activityService->createActivity($request->validated(), auth()->id());

        return redirect()->route('activities.index')
            ->with('success', 'Aktifitas Berhasil Dibuat!');
    }

    public function destroy(int $id)
    {
        $activity = \App\Models\Activity::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Aktifitas Dihapus!');
    }
}
