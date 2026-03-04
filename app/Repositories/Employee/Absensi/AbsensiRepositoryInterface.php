<?php

namespace App\Repositories\Employee\Absensi;

interface AbsensiRepositoryInterface
{
    public function getByUserAndSession(int $userId, int $sessionId);
    public function getOpenSessionsForUser(int $userId, int $divisionId);
    public function createOrUpdate(int $userId, int $sessionId, array $data);
    public function getAttendanceLog(int $userId, int $perPage = 10);
    public function getSummaryStats(int $userId): array;
}
