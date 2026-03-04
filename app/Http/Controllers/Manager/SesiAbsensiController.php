<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\Manager\Absensi\SesiAbsensiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SesiAbsensiController extends Controller
{
    protected $service;

    public function __construct(SesiAbsensiService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $managerId = $user->id;
        $date = $request->query('date');
        
        $sessions = $this->service->getSessionsByDivision($user->divisi_id, $date);

        $employees = \App\Models\User::where(function($q) use ($user, $managerId) {
                $q->where('divisi_id', $user->divisi_id)
                  ->orWhere('atasan_id', $managerId);
            })->get();

        return Inertia::render('Manager/Absensi/Index', [
            'sessions' => $sessions,
            'employees' => $employees,
            'filters' => [
                'date' => $date
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'is_bulk' => 'nullable|boolean',
            'tanggal' => 'required|date',
            // For single
            'nama' => 'required_if:is_bulk,false|string',
            'tipe' => 'required_if:is_bulk,false|in:masuk,istirahat,pulang',
            'jam_mulai' => 'required_if:is_bulk,false',
            'jam_selesai' => 'required_if:is_bulk,false',
            // For bulk
            'sessions' => 'required_if:is_bulk,true|array',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($request, $user) {
                if ($request->is_bulk) {
                    foreach ($request->sessions as $sessionData) {
                        if (!isset($sessionData['active']) || !$sessionData['active']) continue;

                        $this->service->createSession([
                            'nama' => $sessionData['nama'],
                            'tipe' => $sessionData['tipe'],
                            'tanggal' => $request->tanggal,
                            'jam_mulai' => $sessionData['jam_mulai'],
                            'jam_selesai' => $sessionData['jam_selesai'],
                            'divisi_id' => $user->divisi_id,
                            'dibuat_oleh' => $user->id,
                            'status' => 'dibuka',
                        ]);
                    }
                } else {
                    $this->service->createSession([
                        'nama' => $request->nama,
                        'tipe' => $request->tipe,
                        'tanggal' => $request->tanggal,
                        'jam_mulai' => $request->jam_mulai,
                        'jam_selesai' => $request->jam_selesai,
                        'divisi_id' => $user->divisi_id,
                        'dibuat_oleh' => $user->id,
                        'status' => 'dibuka',
                    ]);
                }
            });
            return back()->with('success', 'Sesi absensi berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sesi dengan tipe tersebut mungkin sudah ada di tanggal yang dipilih.');
        }
    }

    public function show($id)
    {
        $session = $this->service->getSessionDetails($id);
        $managerId = auth()->id();
        $availableEmployees = \App\Models\User::where(function($q) use ($session, $managerId) {
                $q->where('divisi_id', $session->divisi_id)
                  ->orWhere('atasan_id', $managerId);
            })
            ->whereDoesntHave('absensi', function($q) use ($id) {
                $q->where('sesi_absensi_id', $id);
            })->get();

        return Inertia::render('Manager/Absensi/Show', [
            'session' => $session,
            'availableEmployees' => $availableEmployees,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->service->closeSession($id);
        return back()->with('success', 'Sesi absensi telah ditutup.');
    }

    public function destroy($id)
    {
        $this->service->deleteSession($id);
        return back()->with('success', 'Sesi absensi berhasil dihapus.');
    }

    public function approve($id)
    {
        $this->service->approveAttendance($id, auth()->id());
        return back()->with('success', 'Absensi telah disetujui.');
    }

    public function reject($id)
    {
        $this->service->rejectAttendance($id, auth()->id());
        return back()->with('success', 'Absensi telah ditolak.');
    }
    
    public function approveAll($sessionId)
    {
        $this->service->approveAllAttendance($sessionId, auth()->id());
        return back()->with('success', 'Semua absensi pada sesi ini telah disetujui.');
    }

    public function updateRecord(Request $request, $id)
    {
        $request->validate([
            'jam_absen' => 'nullable',
            'status' => 'required',
            'status_persetujuan' => 'required',
        ]);

        $this->service->updateAttendanceRecord($id, $request->all());

        return back()->with('success', 'Data absensi diperbarui.');
    }

    public function storeRecord(Request $request, $sessionId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jam_absen' => 'nullable',
            'status' => 'required',
            'status_persetujuan' => 'required',
        ]);

        $data = $request->all();
        $data['disetujui_oleh'] = auth()->id(); // Log that the manager created/authorized this.

        $this->service->createAttendanceRecord($sessionId, $data);

        return back()->with('success', 'Data absensi manual berhasil ditambahkan.');
    }

    public function deleteRecord($id)
    {
        \App\Models\Absensi::findOrFail($id)->delete();
        return back()->with('success', 'Data absensi telah dihapus.');
    }

    public function bulkStoreRecord(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'records' => 'required|array',
            'catatan' => 'nullable|string',
        ]);

        $user = auth()->user();
        $data = $request->all();
        $data['divisi_id'] = $user->divisi_id;
        $data['creator_id'] = $user->id;

        $this->service->bulkCreateAttendance($data);

        return back()->with('success', 'Data absensi bulk berhasil ditambahkan.');
    }
}
