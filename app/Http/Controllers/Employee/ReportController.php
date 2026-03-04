<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Employee/Reports/Index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:attendance,tasks,productivity',
            'filter' => 'required|in:daily,monthly',
            'date' => 'required',
        ]);

        $type = $request->input('type');
        $filter = $request->input('filter');
        $date = $request->input('date');
        $preview = $request->has('preview') && $request->preview == 'true';
        $user = auth()->user();

        return match ($type) {
            'attendance' => $this->exportAttendance($filter, $date, $user, $preview),
            'tasks' => $this->exportTasks($filter, $date, $user, $preview),
            'productivity' => $this->exportProductivity($filter, $date, $user, $preview),
        };
    }

    private function exportAttendance($filter, $date, $user, $preview = false)
    {
        $query = Absensi::with('sesiAbsensi')->where('user_id', $user->id);

        if ($filter === 'daily') {
            $query->whereHas('sesiAbsensi', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            });
            $filename = "laporan_kehadiran_{$date}.xls";
        } else {
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();
            $query->whereHas('sesiAbsensi', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
            });
            $filename = "laporan_kehadiran_" . $startOfMonth->format('Y_m') . ".xls";
        }

        $data = $query->with('sesiAbsensi')
            ->get()
            ->sortBy(function($item) {
                return ($item->sesiAbsensi->tanggal ?? '') . ($item->sesiAbsensi->jam_mulai ?? '');
            });

        $groupedData = $data->groupBy(function($item) {
            return $item->sesiAbsensi ? Carbon::parse($item->sesiAbsensi->tanggal)->format('Y-m-d') : 'Unknown';
        });

        $callback_content = function () use ($groupedData, $filter, $date, $user) {
            $html = "<html><head><meta charset='utf-8'><style>
                body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; padding: 0; margin: 0; background: #ffffff; }
                table { border-collapse: collapse; width: 100%; border: 1px solid #d1d4d9; }
                th, td { border: 1px solid #d1d4d9; padding: 8px 12px; font-size: 11pt; color: #000000; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .title-cell { font-size: 14pt; font-weight: bold; padding: 15px; border-bottom: none; }
                .header-row { background-color: #ffffff; }
            </style></head><body>";

            $html .= "<table>";
            $html .= "<tr class='header-row'><td colspan='6' class='title-cell'>REKAP ABSENSI INDIVIDU (DAILY SUMMARY)</td></tr>";
            $html .= "<tr class='header-row'><td colspan='6' style='padding: 0 15px 15px 15px; font-size: 10pt; color: #666; border-top: none;'>Nama: " . $user->name . " | Periode: " . ($filter === 'daily' ? $date : Carbon::parse($date)->format('F Y')) . "</td></tr>";
            
            $html .= "<tr>
                <th>TANGGAL</th>
                <th class='text-center'>MASUK</th>
                <th class='text-center'>ISTIRAHAT</th>
                <th class='text-center'>PULANG</th>
                <th class='text-center'>TERLAMBAT</th>
                <th>KETERANGAN</th>
            </tr>";

            foreach ($groupedData as $dateKey => $entries) {
                $masuk = $entries->where('sesiAbsensi.tipe', 'masuk')->first();
                $istirahat = $entries->where('sesiAbsensi.tipe', 'istirahat')->first();
                $pulang = $entries->where('sesiAbsensi.tipe', 'pulang')->first();
                
                $totalLate = $entries->sum('menit_keterlambatan');
                $allNotes = $entries->pluck('catatan')->filter()->unique()->implode(', ');

                $html .= "<tr>
                    <td>" . $dateKey . "</td>
                    <td class='text-center'>" . ($masuk ? ($masuk->jam_absen ? Carbon::parse($masuk->jam_absen)->format('H:i') : '-') : '-') . "</td>
                    <td class='text-center'>" . ($istirahat ? ($istirahat->jam_absen ? Carbon::parse($istirahat->jam_absen)->format('H:i') : '-') : '-') . "</td>
                    <td class='text-center'>" . ($pulang ? ($pulang->jam_absen ? Carbon::parse($pulang->jam_absen)->format('H:i') : '-') : '-') . "</td>
                    <td class='text-center' style='" . ($totalLate > 0 ? "color: #ef4444; font-weight: bold;" : "") . "'>" . ($totalLate > 0 ? $totalLate . " m" : "0") . "</td>
                    <td style='font-size: 9pt; color: #64748b;'>" . ($allNotes ?: '-') . "</td>
                </tr>";
            }
            $html .= "</table></body></html>";
            return $html;
        };

        if ($preview) {
            return response($callback_content())->header('Content-Type', 'text/html');
        }

        $headers = ['Content-Type' => 'application/vnd.ms-excel', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        return Response::stream(function() use ($callback_content) { echo $callback_content(); }, 200, $headers);
    }

    private function exportTasks($filter, $date, $user, $preview = false)
    {
        $isLeader = $user->hasRole('leader');

        $query = Tugas::with(['sesiTugas', 'divisi'])
            ->where(function($q) use ($user, $isLeader) {
                $q->where('ditugaskan_ke', $user->id)
                  ->orWhere(function($sub) use ($user, $isLeader) {
                      $sub->where('tipe_penugasan', 'divisi')
                          ->where('divisi_id', $user->divisi_id);
                      if (!$isLeader) {
                          $sub->whereHas('subTugas', function($st) {
                              $st->where('status_usulan', 'disetujui');
                          });
                      }
                  });
            });

        if ($filter === 'daily') {
            $query->whereHas('sesiTugas', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            });
            $filename = "laporan_tugas_{$date}.xls";
        } else {
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();
            $query->whereHas('sesiTugas', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
            });
            $filename = "laporan_tugas_" . $startOfMonth->format('Y_m') . ".xls";
        }

        $data = $query->get();

        $callback_content = function () use ($data, $filter, $date, $user) {
            $html = "<html><head><meta charset='utf-8'><style>
                body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; padding: 0; margin: 0; background: #ffffff; }
                table { border-collapse: collapse; width: 100%; border: 1px solid #d1d4d9; }
                th, td { border: 1px solid #d1d4d9; padding: 8px 12px; font-size: 11pt; color: #000000; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .title-cell { font-size: 14pt; font-weight: bold; padding: 15px; border-bottom: none; }
                .header-row { background-color: #ffffff; }
            </style></head><body>";

            $html .= "<table>";
            $html .= "<tr class='header-row'><td colspan='7' class='title-cell'>ANALISIS TUGAS INDIVIDU</td></tr>";
            $html .= "<tr class='header-row'><td colspan='7' style='padding: 0 15px 15px 15px; font-size: 10pt; color: #666; border-top: none;'>Nama: " . $user->name . " | Periode: " . ($filter === 'daily' ? $date : Carbon::parse($date)->format('F Y')) . "</td></tr>";
            
            $html .= "<tr>
                <th>TANGGAL</th>
                <th>TIPE</th>
                <th>JUDUL TUGAS</th>
                <th>STATUS</th>
                <th>PROGRESS</th>
                <th>PRIORITAS</th>
                <th>PERSETUJUAN</th>
            </tr>";

            $totalMyPoints = 0;
            foreach ($data as $row) {
                $html .= "<tr>
                    <td>" . ($row->sesiTugas ? Carbon::parse($row->sesiTugas->tanggal)->format('Y-m-d') : '-') . "</td>
                    <td>" . strtoupper($row->tipe_penugasan) . "</td>
                    <td>" . $row->judul . "</td>
                    <td>" . strtoupper($row->status) . "</td>
                    <td class='text-center'>" . $row->progress . "%</td>
                    <td>" . strtoupper($row->prioritas) . "</td>
                    <td>" . strtoupper($row->status_persetujuan) . "</td>
                </tr>";
            }
            $html .= "</table></body></html>";
            return $html;
        };

        if ($preview) {
            return response($callback_content())->header('Content-Type', 'text/html');
        }

        $headers = ['Content-Type' => 'application/vnd.ms-excel', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        return Response::stream(function() use ($callback_content) { echo $callback_content(); }, 200, $headers);
    }

    private function exportProductivity($filter, $date, $user, $preview = false)
    {
        if ($filter === 'daily') {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();
            $filename = "laporan_produktivitas_{$date}.xls";
        } else {
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate = Carbon::parse($date)->endOfMonth();
            $filename = "laporan_produktivitas_" . $startDate->format('Y_m') . ".xls";
        }

        $callback_content = function () use ($user, $startDate, $endDate, $filter, $date) {
            $html = "<html><head><meta charset='utf-8'><style>
                body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; padding: 0; margin: 0; background: #ffffff; }
                table { border-collapse: collapse; width: 100%; border: 1px solid #d1d4d9; }
                th, td { border: 1px solid #d1d4d9; padding: 8px 12px; font-size: 11pt; color: #000000; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .header-row { background-color: #ffffff; }
                .title-cell { font-size: 14pt; font-weight: bold; padding: 15px; border-bottom: none; }
            </style></head><body>";

            $html .= "<table>";
            $html .= "<tr class='header-row'><td colspan='7' class='title-cell'>ANALISIS PRODUKTIVITAS INDIVIDU</td></tr>";
            $html .= "<tr class='header-row'><td colspan='7' style='padding: 0 15px 15px 15px; font-size: 10pt; color: #666; border-top: none;'>Nama: " . $user->name . " | Periode: " . ($filter === 'daily' ? $date : Carbon::parse($date)->format('F Y')) . "</td></tr>";
            
            $html .= "<tr>
                <th>TOTAL TUGAS</th>
                <th>SELESAI</th>
                <th>DIKERJAKAN</th>
                <th>AVG PROG (%)</th>
                <th>TOTAL POIN</th>
                <th>HARI HADIR</th>
                <th>TERLAMBAT</th>
            </tr>";

            $isLeader = $user->hasRole('leader');

            $tasks = Tugas::where(function($q) use ($user, $isLeader) {
                    $q->where('ditugaskan_ke', $user->id)
                      ->orWhere(function($sub) use ($user, $isLeader) {
                          $sub->where('tipe_penugasan', 'divisi')
                              ->where('divisi_id', $user->divisi_id);
                          
                          if (!$isLeader) {
                              $sub->whereHas('subTugas', function($st) {
                                  $st->where('status_usulan', 'disetujui');
                              });
                          }
                      });
                })
                ->whereHas('sesiTugas', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal', [$startDate, $endDate]);
                })->get();

            $attendance = Absensi::where('user_id', $user->id)
                ->whereHas('sesiAbsensi', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal', [$startDate, $endDate]);
                })->get();

            $totalTasks = $tasks->count();
            $completed = $tasks->where('status', 'selesai')->count();
            $working = $tasks->where('status', 'dikerjakan')->count();
            $avgProgress = $totalTasks > 0 ? $tasks->avg('progress') : 0;
            $uniqueDaysPresent = $attendance->whereIn('status', ['hadir', 'terlambat'])
                ->groupBy(function($item) {
                     return Carbon::parse($item->sesiAbsensi->tanggal)->format('Y-m-d');
                })->count();

            $lates = $attendance->where('status', 'terlambat')->count();

            $totalPoints = 0;
            $milestoneHtml = "";
            foreach ($tasks as $task) {
                $myMilestones = $task->subTugas()->where('assigned_to', $user->id)->get();
                if ($myMilestones->count() > 0) {
                    foreach ($myMilestones as $sub) {
                        $totalPoints += $sub->bobot;
                        $milestoneHtml .= "<tr>
                            <td>" . $task->judul . "</td>
                            <td>" . $sub->judul . "</td>
                            <td class='text-center'>" . $sub->bobot . "</td>
                            <td>" . ($sub->is_completed ? 'SELESAI' : 'PROSES') . "</td>
                        </tr>";
                    }
                }
            }

            $html .= "<tr>
                <td class='text-right'>" . $totalTasks . "</td>
                <td class='text-right'>" . $completed . "</td>
                <td class='text-right'>" . $working . "</td>
                <td class='text-right'>" . round($avgProgress, 2) . "%</td>
                <td class='text-right' style='font-weight:bold; color: #059669;'>" . $totalPoints . "</td>
                <td class='text-right'>" . $uniqueDaysPresent . "</td>
                <td class='text-right'>" . $lates . "</td>
            </tr>";
            
            if ($totalPoints > 0) {
                $html .= "</table><br><table>";
                $html .= "<tr class='header-row'><td colspan='4' class='title-cell'>RINCIAN POINT MILSTONE SAYA</td></tr>";
                $html .= "<tr>
                    <th>TUGAS UTAMA</th>
                    <th>MILSTONE / PECAHAN</th>
                    <th class='text-center'>POIN</th>
                    <th>STATUS</th>
                </tr>";
                $html .= $milestoneHtml;
                $html .= "<tr style='font-weight:bold; background-color: #f1f5f9;'>
                    <td colspan='2' style='text-align:right;'>TOTAL AKUMULASI POIN:</td>
                    <td class='text-center'>" . $totalPoints . "</td>
                    <td></td>
                </tr>";
            }
            
            $html .= "</table></body></html>";
            return $html;
        };

        if ($preview) {
            return response($callback_content())->header('Content-Type', 'text/html');
        }

        $headers = ['Content-Type' => 'application/vnd.ms-excel', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        return Response::stream(function() use ($callback_content) { echo $callback_content(); }, 200, $headers);
    }
}
