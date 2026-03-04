<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TugasController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Fetch tasks assigned to this division or specifically to this leader
        // Leader needs to see all division tasks to breakdown/delegate them
        $divisionTasks = Tugas::with(['subTugas.assignee', 'assigner', 'user'])
            ->where('divisi_id', $user->divisi_id)
            ->whereHas('sesiTugas', function($q) {
                $q->where('status', 'dibuka');
            })
            ->latest()
            ->get();

        // Fetch only Employees and the Leader themselves for delegation
        $divisionEmployees = User::where('divisi_id', $user->divisi_id)
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['employee', 'leader']);
            })
            ->get();

        return Inertia::render('Leader/Tugas/Index', [
            'tasks' => $divisionTasks,
            'divisionEmployees' => $divisionEmployees
        ]);
    }
}
