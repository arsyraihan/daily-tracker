<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\SesiAbsensi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load(['roles', 'divisi', 'jabatan', 'atasan']);

        $userId = $user->id;
        
        $totalLogs = Absensi::where('user_id', $userId)->count();
        $onTime = Absensi::where('user_id', $userId)->where('status', 'hadir')->count();
        $pending = Absensi::where('user_id', $userId)->where('status_persetujuan', 'menunggu')->count();
        $uniqueDays = Absensi::where('user_id', $userId)
            ->join('sesi_absensi', 'absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->distinct('sesi_absensi.tanggal')
            ->count('sesi_absensi.tanggal');

        $stats = [
            ['label' => 'Total Logs', 'value' => $totalLogs, 'icon' => 'Clock', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
            ['label' => 'On Time', 'value' => $onTime, 'icon' => 'CheckCircle', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
            ['label' => 'Days Active', 'value' => $uniqueDays, 'icon' => 'Calendar', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50'],
            ['label' => 'Pending Verification', 'value' => $pending, 'icon' => 'TrendingUp', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50'],
        ];

        return Inertia::render('Employee/Index', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
