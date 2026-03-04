<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\Manager\Tugas\TugasService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\SesiTugas;

class TugasController extends Controller
{
    protected $service;

    public function __construct(TugasService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->query('date');
        
        $sessions = $this->service->getSessionsByDivision($user->divisi_id, $date);

        return Inertia::render('Manager/Tugas/Index', [
            'sessions' => $sessions,
            'filters' => ['date' => $date]
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'tanggal' => 'required|date',
        ]);

        try {
            $this->service->createSession([
                'tanggal' => $request->tanggal,
                'divisi_id' => $user->divisi_id,
                'dibuat_oleh' => $user->id,
                'status' => 'dibuka',
            ]);
            return back()->with('success', 'Sesi tugas harian berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sesi untuk divisi ini sudah ada di tanggal tersebut.');
        }
    }

    public function show($id)
    {
        $session = $this->service->getSessionDetails($id);
        $managerId = auth()->id();
        
        // Get employees that can be assigned tasks
        // Either in the same division or direct reports
        $availableEmployees = User::where(function($q) use ($session, $managerId) {
                $q->where('divisi_id', $session->divisi_id)
                  ->orWhere('atasan_id', $managerId);
            })
            ->where('status', 'aktif')
            ->get();

        // Get all divisions for cross-divisional assignment
        $divisions = \App\Models\Divisi::all();

        return Inertia::render('Manager/Tugas/Show', [
            'session' => $session,
            'availableEmployees' => $availableEmployees,
            'divisions' => $divisions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->service->closeSession($id);
        return back()->with('success', 'Sesi tugas harian telah ditutup.');
    }

    public function destroy($id)
    {
        $this->service->deleteSession($id);
        return back()->with('success', 'Sesi tugas harian telah dihapus.');
    }

    // Task Assignment methods
    public function storeTask(Request $request, $sessionId)
    {
        $session = SesiTugas::findOrFail($sessionId);

        $request->validate([
            'tipe_penugasan' => 'required|in:individu,divisi',
            'ditugaskan_ke' => 'required_if:tipe_penugasan,individu|nullable|exists:users,id',
            'divisi_id' => 'required_if:tipe_penugasan,divisi|nullable|exists:divisi,id',
            'judul' => 'required|string',
            'deskripsi' => 'nullable|string',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'deadline' => 'nullable',
        ]);

        $this->service->createTask([
            'sesi_tugas_id' => $sessionId,
            'divisi_id' => $request->tipe_penugasan === 'divisi' ? $request->divisi_id : null,
            'ditugaskan_ke' => $request->tipe_penugasan === 'individu' ? $request->ditugaskan_ke : null,
            'ditugaskan_oleh' => auth()->id(),
            'tipe_penugasan' => $request->tipe_penugasan,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'prioritas' => $request->prioritas,
            'deadline' => $request->deadline,
            'status' => 'menunggu',
            'status_persetujuan' => 'menunggu',
        ]);

        return back()->with('success', 'Tugas harian berhasil ditugaskan.');
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string',
            'deskripsi' => 'nullable|string',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'deadline' => 'nullable',
            'status' => 'required',
            'status_persetujuan' => 'required',
        ]);

        $this->service->updateTask($id, $request->all());
        return back()->with('success', 'Tugas harian berhasil diperbarui.');
    }

    public function deleteTask($id)
    {
        $this->service->deleteTask($id);
        return back()->with('success', 'Tugas harian berhasil dihapus.');
    }

    public function approveTask($id)
    {
        $this->service->approveTask($id, auth()->id());
        return back()->with('success', 'Tugas telah disetujui.');
    }

    public function rejectTask($id)
    {
        $this->service->rejectTask($id, auth()->id());
        return back()->with('success', 'Tugas telah ditolak.');
    }

    public function taskDetail($id)
    {
        $task = \App\Models\Tugas::with(['user', 'divisi', 'assigner', 'sesiTugas.divisi', 'logs.user', 'subTugas.assignee', 'subTugas.user'])
            ->findOrFail($id);

        return Inertia::render('Manager/Tugas/TaskDetail', [
            'task' => $task
        ]);
    }

    public function proposeSubTugas(Request $request, $id)
    {
        $request->validate([
            'sub_tugas' => 'required|array',
            'sub_tugas.*.judul' => 'required|string',
            'sub_tugas.*.bobot' => 'required|integer|min:1|max:100',
            'sub_tugas.*.assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = \App\Models\Tugas::findOrFail($id);
        
        // Remove existing pending sub-tasks if any to avoid confusion
        $task->subTugas()->where('is_completed', false)->delete();

        foreach ($request->sub_tugas as $item) {
            $task->subTugas()->create([
                'judul' => $item['judul'],
                'bobot' => $item['bobot'],
                'assigned_to' => $item['assigned_to'] ?? null,
                'status_usulan' => 'pending'
            ]);
        }

        return back()->with('success', 'Usulan sub-tugas telah dikirim.');
    }

    public function approveSubTugasPlan($id)
    {
        $task = \App\Models\Tugas::findOrFail($id);
        $task->subTugas()->update(['status_usulan' => 'disetujui']);
        
        // Mark main task as active once roadmap is approved
        if ($task->status === 'menunggu') {
            $task->update(['status' => 'dikerjakan']);
        }
        
        return back()->with('success', 'Rencana kerja telah disetujui. Tugas kini aktif di workspace tim.');
    }

    public function completeSubTugas($id)
    {
        $sub = \App\Models\SubTugas::findOrFail($id);
        $sub->update([
            'is_completed' => true,
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        // Auto update main task progress
        $task = $sub->tugas;
        $totalProgress = $task->subTugas()->where('is_completed', true)->sum('bobot');
        
        $prevProgress = $task->progress;
        $task->update([
            'progress' => min(100, $totalProgress),
            'status' => $totalProgress >= 100 ? 'selesai' : 'dikerjakan',
            'status_persetujuan' => $totalProgress >= 100 ? 'menunggu' : $task->status_persetujuan
        ]);

        // Log the achievement
        $task->logs()->create([
            'user_id' => auth()->id(),
            'progress_sebelum' => $prevProgress,
            'progress_sesudah' => $task->progress,
            'keterangan' => "Menyelesaikan poin: {$sub->judul} (+{$sub->bobot}%)",
        ]);

        return back()->with('success', "Poin '{$sub->judul}' berhasil diklaim!");
    }
}
