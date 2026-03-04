<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SesiAbsensi;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

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
            [
                'label' => 'Team Members', 
                'value' => $team->count(), 
                'icon' => 'Users', 
                'color' => 'text-indigo-600', 
                'bg' => 'bg-indigo-50'
            ],
            [
                'label' => 'Active Sessions', 
                'value' => SesiAbsensi::where('divisi_id', $manager->divisi_id)
                    ->where('status', 'dibuka')
                    ->whereDate('tanggal', Carbon::today())
                    ->whereTime('jam_mulai', '<=', Carbon::now()->toTimeString())
                    ->whereTime('jam_selesai', '>=', Carbon::now()->toTimeString())
                    ->count(), 
                'icon' => 'Calendar', 
                'color' => 'text-emerald-600', 
                'bg' => 'bg-emerald-50'
            ],
            [
                'label' => 'Pending Approvals', 
                'value' => Absensi::whereHas('sesiAbsensi', function($q) use ($manager) {
                    $q->where('divisi_id', $manager->divisi_id);
                })->where('status_persetujuan', 'menunggu')->count(), 
                'icon' => 'ClipboardList', 
                'color' => 'text-amber-600', 
                'bg' => 'bg-amber-50'
            ],
            [
                'label' => 'Late Arrivals', 
                'value' => Absensi::whereHas('sesiAbsensi', function($q) use ($manager) {
                    $q->where('divisi_id', $manager->divisi_id)->where('tanggal', Carbon::today());
                })->where('status', 'terlambat')->count(), 
                'icon' => 'AlertCircle', 
                'color' => 'text-rose-600', 
                'bg' => 'bg-rose-50'
            ],
        ];

        return Inertia::render('Manager/Index', [
            'team' => $team,
            'stats' => $stats,
        ]);
    }
}
