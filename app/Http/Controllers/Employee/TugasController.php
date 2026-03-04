<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Manager\Tugas\TugasService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TugasController extends Controller
{
    protected $service;

    public function __construct(TugasService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $user = auth()->user()->load('roles');
        $tasks = $this->service->getEmployeeTasks($user->id, $user->divisi_id);
        
        $divisionEmployees = \App\Models\User::where('divisi_id', $user->divisi_id)
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['employee', 'leader']);
            })
            ->get();

        return Inertia::render('Employee/Tugas/Index', [
            'tasks' => $tasks,
            'divisionEmployees' => $divisionEmployees
        ]);
    }

    public function submitProgress(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $this->service->submitProgress($id, auth()->id(), $request->all());

        return back()->with('success', 'Rincian tugas harian berhasil diperbarui.');
    }
}
