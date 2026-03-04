<?php

namespace App\Services\Employee\Absensi;

use App\Repositories\Employee\Absensi\AbsensiRepositoryInterface;
use Carbon\Carbon;

class AbsensiService
{
    protected $repository;

    public function __construct(AbsensiRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getMyOpenSessions(int $userId, int $divisionId)
    {
        return $this->repository->getOpenSessionsForUser($userId, $divisionId);
    }

    public function submitAttendance(int $userId, int $sessionId, ?string $catatan = null)
    {
        $now = Carbon::now();
        $session = \App\Models\SesiAbsensi::findOrFail($sessionId);

        // Validation: Session must be 'dibuka'
        if ($session->status !== 'dibuka') {
            throw new \Exception('Sesi absensi sudah ditutup.');
        }

        // Validation: Must be within time window
        $startTime = Carbon::parse($session->tanggal . ' ' . $session->jam_mulai);
        $endTime = Carbon::parse($session->tanggal . ' ' . $session->jam_selesai);

        if (!$now->between($startTime, $endTime)) {
            throw new \Exception('Waktu absensi belum dimulai atau sudah berakhir.');
        }
        
        // Determine status (Hadir vs Terlambat)
        $jamMulai = Carbon::parse($session->tanggal . ' ' . $session->jam_mulai);
        $menitTerlambat = 0;
        $status = 'hadir';

        if ($now->greaterThan($jamMulai)) {
            $menitTerlambat = $now->diffInMinutes($jamMulai);
            if ($menitTerlambat > 0) {
                $status = 'terlambat';
            }
        }
        
        $payload = [
            'jam_absen' => $now->toTimeString(),
            'status' => $status,
            'menit_keterlambatan' => $menitTerlambat,
            'status_persetujuan' => 'menunggu',
            'catatan' => $catatan
        ];

        return $this->repository->createOrUpdate($userId, $sessionId, $payload);
    }

    public function submitRequest(int $userId, int $sessionId, string $status, string $catatan)
    {
        /**
         * 'izin', 'sakit', 'cuti'
         */
        return $this->repository->createOrUpdate($userId, $sessionId, [
            'status' => $status,
            'status_persetujuan' => 'menunggu',
            'catatan' => $catatan
        ]);
    }

    public function getMyAttendanceHistory(int $userId)
    {
        return $this->repository->getAttendanceLog($userId);
    }
    public function getSummaryStats(int $userId)
    {
        return $this->repository->getSummaryStats($userId);
    }
}
