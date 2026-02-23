<?php

namespace App\Services;

use App\Models\Activity;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ActivityService
{
    public function __construct(
        private ActivityRepositoryInterface $activityRepository
    ) {}
    public function getUserActivities(int $userId): Collection
    {
        return $this->activityRepository->getByUser($userId);
    }

    public function getAllActivitiesForSupervisor(): Collection
    {
        return $this->activityRepository->getAllWithUser();
    }
    public function createActivity(array $data, int $userId): Activity
    {
        $data['user_id'] = $userId;

        if (isset($data['start_time']) && isset($data['end_time'])) {
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $data['durasi_menit'] = $start->diffInMinutes($end);
        }

        return $this->activityRepository->create($data);
    }

    public function getDashboardStats(int $userId): array
    {
        $activities = $this->activityRepository->getByUser($userId);

        $uniqueDays = $activities->groupBy('tanggal');
        $totalHariKerja = 0;
        $totalCuti = 0;
        $totalDayOff = 0;
        $totalLibur = 0;

        foreach ($uniqueDays as $date => $dailyActivities) {
            if ($dailyActivities->contains('kategori', 'Cuti')) {
                $totalCuti++;
            } elseif ($dailyActivities->contains('kategori', 'Day Off')) {
                $totalDayOff++;
            } elseif ($dailyActivities->contains('kategori', 'Libur')) {
                $totalLibur++;
            } else {
                $totalHariKerja++;
            }
        }

        $kategoriKerja = ['BSC / OKR', 'Daily Task', 'Improvement Goal'];
        $totalMenitEfektif = $activities->whereIn('kategori', $kategoriKerja)->sum('durasi_menit');

        $jam = floor($totalMenitEfektif / 60);
        $menit = $totalMenitEfektif % 60;
        $durasiEfektifFormatted = "{$jam} Jam {$menit} Menit";

        $breakdownKategori = [
            'BSC / OKR' => $activities->where('kategori', 'BSC / OKR')->sum('durasi_menit'),
            'Daily Task' => $activities->where('kategori', 'Daily Task')->sum('durasi_menit'),
            'Improvement Goal' => $activities->where('kategori', 'Improvement Goal')->sum('durasi_menit'),
            'Ibadah' => $activities->where('kategori', 'Ibadah')->sum('durasi_menit'),
        ];

        return [
            'total_hari_kerja' => $totalHariKerja,
            'total_cuti' => $totalCuti,
            'total_day_off' => $totalDayOff,
            'total_libur' => $totalLibur,
            'durasi_efektif' => $durasiEfektifFormatted,
            'breakdown' => $breakdownKategori,
        ];
    }
}
