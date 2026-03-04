<?php

namespace App\Services\Manager\Tugas;

use App\Models\SesiTugas;
use App\Models\Tugas;
use App\Models\LogTugas;
use Carbon\Carbon;

class TugasService
{
    public function getSessionsByDivision($divisionId, $date = null)
    {
        $query = SesiTugas::where('divisi_id', $divisionId)
            ->withCount('tugas')
            ->orderBy('tanggal', 'desc');

        if ($date) {
            $query->whereDate('tanggal', $date);
        }

        return $query->paginate(10);
    }

    public function createSession($data)
    {
        return SesiTugas::create($data);
    }

    public function getSessionDetails($id)
    {
        return SesiTugas::with(['divisi', 'creator', 'tugas.user', 'tugas.assigner', 'tugas.divisi', 'tugas.logs.user', 'tugas.subTugas.assignee'])
            ->findOrFail($id);
    }

    public function createTask($data)
    {
        return Tugas::create($data);
    }

    public function updateTask($id, $data)
    {
        $task = Tugas::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function deleteTask($id)
    {
        return Tugas::findOrFail($id)->delete();
    }

    public function approveTask($id, $managerId)
    {
        $task = Tugas::findOrFail($id);
        $task->update([
            'status_persetujuan' => 'disetujui',
            'status' => 'selesai'
        ]);
        return $task;
    }

    public function rejectTask($id, $managerId)
    {
        $task = Tugas::findOrFail($id);
        $task->update([
            'status_persetujuan' => 'ditolak',
            'status' => 'ditolak'
        ]);
        return $task;
    }

    public function closeSession($id)
    {
        $session = SesiTugas::findOrFail($id);
        $session->update(['status' => 'ditutup']);
        return $session;
    }

    public function deleteSession($id)
    {
        return SesiTugas::findOrFail($id)->delete();
    }

    // Employee specific methods
    public function getEmployeeTasks($userId, $divisionId)
    {
        $isLeader = auth()->user()->hasRole('leader');

        return Tugas::with(['sesiTugas.divisi', 'assigner', 'subTugas.user', 'subTugas.assignee'])
            ->where(function($q) use ($userId, $divisionId, $isLeader) {
                // 1. Tugas Individu (Langsung muncul)
                $q->where('ditugaskan_ke', $userId)
                  // 2. Tugas Divisi
                  ->orWhere(function($sub) use ($divisionId, $isLeader) {
                      $sub->where('tipe_penugasan', 'divisi')
                          ->where('divisi_id', $divisionId);
                      
                      // Jika bukan leader, hanya bisa liat kalau roadmap sudah approve
                      if (!$isLeader) {
                          $sub->whereHas('subTugas', function($st) {
                              $st->where('status_usulan', 'disetujui');
                          });
                      }
                  });
            })
            ->whereHas('sesiTugas', function($q) {
                $q->where('status', 'dibuka');
            })
            ->latest()
            ->get();
    }

    public function submitProgress($taskId, $userId, $data)
    {
        $task = Tugas::findOrFail($taskId);
        
        // Log the progress update
        LogTugas::create([
            'tugas_id' => $taskId,
            'user_id' => $userId,
            'keterangan' => $data['keterangan'] ?? 'Update Progres',
            'progress_sebelum' => $task->progress,
            'progress_sesudah' => $data['progress'],
        ]);

        $updateData = [
            'progress' => $data['progress'],
            'status' => $data['progress'] >= 100 ? 'selesai' : 'dikerjakan',
        ];

        // If finished, set approval status to waiting
        if ($data['progress'] >= 100) {
            $updateData['status_persetujuan'] = 'menunggu';
        }

        $task->update($updateData);

        return $task;
    }
}
