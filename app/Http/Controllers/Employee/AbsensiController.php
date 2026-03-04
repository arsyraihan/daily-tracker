<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\Absensi\AbsensiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsensiController extends Controller
{
    protected $service;

    public function __construct(AbsensiService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $user = auth()->user();
        $openSessions = $this->service->getMyOpenSessions($user->id, $user->divisi_id);
        $history = $this->service->getMyAttendanceHistory($user->id);
        $stats = $this->service->getSummaryStats($user->id);

        return Inertia::render('Employee/Absensi/Index', [
            'openSessions' => $openSessions,
            'history' => $history,
            'stats' => $stats,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'sesi_absensi_id' => 'required|exists:sesi_absensi,id',
            'catatan' => 'nullable|string'
        ]);
        
        try {
            $this->service->submitAttendance(auth()->id(), $request->sesi_absensi_id, $request->catatan);
            return back()->with('success', 'Absensi berhasil dikirim.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitRequest(Request $request)
    {
        $request->validate([
            'sesi_absensi_id' => 'required|exists:sesi_absensi,id',
            'status' => 'required|in:izin,sakit,cuti',
            'catatan' => 'required|string|max:500'
        ]);

        $this->service->submitRequest(auth()->id(), $request->sesi_absensi_id, $request->status, $request->catatan);

        return back()->with('success', 'Pengajuan berhasil dikirim.');
    }
}
