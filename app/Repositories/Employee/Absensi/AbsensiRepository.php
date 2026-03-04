<?php

namespace App\Repositories\Employee\Absensi;

use App\Models\Absensi;
use App\Models\SesiAbsensi;

class AbsensiRepository implements AbsensiRepositoryInterface
{
    public function getByUserAndSession(int $userId, int $sessionId)
    {
        return Absensi::where('user_id', $userId)
            ->where('sesi_absensi_id', $sessionId)
            ->first();
    }

    public function getOpenSessionsForUser(int $userId, int $divisionId)
    {
        $now = \Carbon\Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        // An open session has status 'dibuka' for the same division on current date
        // and current time must be within jam_mulai and jam_selesai
        return SesiAbsensi::where('divisi_id', $divisionId)
            ->where('status', 'dibuka')
            ->whereDate('tanggal', $today)
            ->whereTime('jam_mulai', '<=', $currentTime)
            ->whereTime('jam_selesai', '>=', $currentTime)
            ->whereDoesntHave('absensi', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('jam_mulai', 'asc')
            ->get();
    }

    public function createOrUpdate(int $userId, int $sessionId, array $data)
    {
        return Absensi::updateOrCreate(
            ['user_id' => $userId, 'sesi_absensi_id' => $sessionId],
            $data
        );
    }

    public function getAttendanceLog(int $userId, int $perPage = 10)
    {
        return Absensi::with(['sesiAbsensi', 'approver'])
            ->join('sesi_absensi', 'absensi.sesi_absensi_id', '=', 'sesi_absensi.id')
            ->where('absensi.user_id', $userId)
            ->orderBy('sesi_absensi.tanggal', 'desc')
            ->orderBy('sesi_absensi.jam_mulai', 'desc')
            ->select('absensi.*') // Avoid column name collision
            ->paginate($perPage);
    }

    public function getSummaryStats(int $userId): array
    {
        return [
            'total_logs' => Absensi::where('user_id', $userId)->count(),
            'on_time_count' => Absensi::where('user_id', $userId)->where('status', 'hadir')->count(),
        ];
    }
}
