<?php

namespace App\Services\Manager\Absensi;

use App\Repositories\Manager\Absensi\SesiAbsensiRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SesiAbsensiService
{
    protected $repository;

    public function __construct(SesiAbsensiRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getSessionsByDivision(int $divisionId, ?string $date = null)
    {
        return $this->repository->getAllByDivision($divisionId, $date);
    }

    public function getSessionDetails(int $id)
    {
        return $this->repository->find($id);
    }

    public function createSession(array $data)
    {
        return $this->repository->create($data);
    }

    public function closeSession(int $id)
    {
        return $this->repository->closeSession($id);
    }

    public function deleteSession(int $id)
    {
        return $this->repository->delete($id);
    }

    public function approveAttendance(int $attendanceId, int $approverId)
    {
        $attendance = \App\Models\Absensi::findOrFail($attendanceId);
        
        $attendance->update([
            'status_persetujuan' => 'disetujui',
            'disetujui_oleh' => $approverId,
        ]);

        return $attendance;
    }

    public function rejectAttendance(int $attendanceId, int $approverId)
    {
        $attendance = \App\Models\Absensi::findOrFail($attendanceId);
        $attendance->update([
            'status' => 'alpa', // Rejecting might mean marking as 'alpa' or just 'ditolak'
            'status_persetujuan' => 'ditolak',
            'disetujui_oleh' => $approverId,
        ]);

        return $attendance;
    }

    public function approveAllAttendance(int $sessionId, int $approverId)
    {
        return \App\Models\Absensi::where('sesi_absensi_id', $sessionId)
            ->where('status_persetujuan', 'menunggu')
            ->update([
                'status_persetujuan' => 'disetujui',
                'disetujui_oleh' => $approverId
            ]);
    }

    public function updateAttendanceRecord(int $id, array $data)
    {
        $attendance = \App\Models\Absensi::with('sesiAbsensi')->findOrFail($id);
        
        if (isset($data['jam_absen']) && $attendance->sesiAbsensi) {
            $data['menit_keterlambatan'] = $this->calculateLateness($attendance->sesiAbsensi, $data['jam_absen']);
            if ($data['menit_keterlambatan'] > 0 && ($data['status'] ?? $attendance->status) === 'hadir') {
                $data['status'] = 'terlambat';
            }
        }

        $attendance->update($data);
        return $attendance;
    }

    public function createAttendanceRecord(int $sessionId, array $data)
    {
        $session = \App\Models\SesiAbsensi::findOrFail($sessionId);
        $data['sesi_absensi_id'] = $sessionId;

        if (isset($data['jam_absen'])) {
            $data['menit_keterlambatan'] = $this->calculateLateness($session, $data['jam_absen']);
            if ($data['menit_keterlambatan'] > 0 && ($data['status'] ?? '') === 'hadir') {
                $data['status'] = 'terlambat';
            }
        }

        return \App\Models\Absensi::create($data);
    }

    public function bulkCreateAttendance(array $data)
    {
        return DB::transaction(function() use ($data) {
            $userId = $data['user_id'];
            $tanggal = $data['tanggal'];
            $divisiId = $data['divisi_id'];
            $creatorId = $data['creator_id'];

            $results = [];

            foreach ($data['records'] as $type => $record) {
                if (!isset($record['active']) || !$record['active']) continue;

                // Find or create session for this type on this date
                $session = \App\Models\SesiAbsensi::where('tanggal', $tanggal)
                    ->where('divisi_id', $divisiId)
                    ->where('tipe', $type)
                    ->first();

                if (!$session) {
                    // Default times based on type
                    $defaultTimes = [
                        'masuk' => ['start' => '07:00', 'end' => '08:30', 'name' => 'Absensi Masuk Pagi'],
                        'istirahat' => ['start' => '12:00', 'end' => '13:00', 'name' => 'Istirahat Siang'],
                        'pulang' => ['start' => '16:30', 'end' => '18:00', 'name' => 'Absensi Pulang'],
                    ];

                    $session = \App\Models\SesiAbsensi::create([
                        'nama' => $defaultTimes[$type]['name'] ?? 'Auto-created ' . $type,
                        'tipe' => $type,
                        'tanggal' => $tanggal,
                        'jam_mulai' => $defaultTimes[$type]['start'] ?? '08:00',
                        'jam_selesai' => $defaultTimes[$type]['end'] ?? '17:00',
                        'divisi_id' => $divisiId,
                        'dibuat_oleh' => $creatorId,
                        'status' => 'dibuka',
                    ]);
                }

                // If jam_absen is not provided, use session's official start time
                $jamAbsen = $record['jam_absen'] ?? $session->jam_mulai;

                $attendanceData = [
                    'sesi_absensi_id' => $session->id,
                    'user_id' => $userId,
                    'jam_absen' => $jamAbsen,
                    'status' => $record['status'] ?? 'hadir',
                    'status_persetujuan' => 'disetujui',
                    'disetujui_oleh' => $creatorId,
                    'catatan' => $data['catatan'] ?? null,
                ];

                // Calculate lateness
                $attendanceData['menit_keterlambatan'] = $this->calculateLateness($session, $record['jam_absen']);
                if ($attendanceData['menit_keterlambatan'] > 0 && $attendanceData['status'] === 'hadir') {
                    $attendanceData['status'] = 'terlambat';
                }

                $results[] = \App\Models\Absensi::updateOrCreate(
                    ['sesi_absensi_id' => $session->id, 'user_id' => $userId],
                    $attendanceData
                );
            }

            return $results;
        });
    }

    protected function calculateLateness($session, $jamAbsen)
    {
        $jamMulai = \Carbon\Carbon::parse($session->tanggal . ' ' . $session->jam_mulai);
        $scanTime = \Carbon\Carbon::parse($session->tanggal . ' ' . $jamAbsen);
        
        if ($scanTime->greaterThan($jamMulai)) {
            return $scanTime->diffInMinutes($jamMulai);
        }
        return 0;
    }
}
