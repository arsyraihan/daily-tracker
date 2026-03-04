<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames()->toArray(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name')->toArray(),
                    'divisi' => $request->user()->divisi,
                    'jabatan' => $request->user()->jabatan,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
            'notifications' => $request->user() && $request->user()->hasAnyRole(['manager', 'supervisor']) ? [
                'unapproved_tasks' => \App\Models\Tugas::with('user')
                    ->where('status_persetujuan', 'menunggu')
                    // Show tasks waiting for final approval (progress 100 or marked selesai)
                    ->whereIn('status', ['selesai', 'WAITING APPROVAL']) 
                    ->whereHas('sesiTugas', function($q) use ($request) {
                        $q->where('divisi_id', $request->user()->divisi_id);
                    })
                    ->latest()
                    ->take(5)
                    ->get(),
                'pending_plans' => \App\Models\Tugas::with(['user', 'subTugas'])
                    ->whereHas('subTugas', function($q) {
                        $q->where('status_usulan', 'pending');
                    })
                    ->whereHas('sesiTugas', function($q) use ($request) {
                        $q->where('divisi_id', $request->user()->divisi_id);
                    })
                    ->latest()
                    ->take(5)
                    ->get(),
                'total_pending' => \App\Models\Tugas::where('status_persetujuan', 'menunggu')
                    ->whereHas('sesiTugas', function($q) use ($request) {
                        $q->where('divisi_id', $request->user()->divisi_id);
                    })->count() + \App\Models\Tugas::whereHas('subTugas', function($q) {
                        $q->where('status_usulan', 'pending');
                    })->whereHas('sesiTugas', function($q) use ($request) {
                        $q->where('divisi_id', $request->user()->divisi_id);
                    })->count(),
            ] : null,
            'global_divisions' => \App\Models\Divisi::all(),
        ];
    }
}
