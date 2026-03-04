<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Manager/Reports/Index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:attendance,tasks,productivity',
            'filter' => 'required|in:daily,monthly',
            'date' => 'required',
        ]);

        $type = $request->type;
        $filter = $request->filter;
        $date = $request->date;
        $manager = auth()->user();
        $preview = $request->has('preview') && $request->preview == 'true';

        if ($type === 'attendance') {
            return $this->exportAttendance($filter, $date, $manager, $preview);
        } elseif ($type === 'tasks') {
            return $this->exportTasks($filter, $date, $manager, $preview);
        } elseif ($type === 'productivity') {
            return $this->exportProductivity($filter, $date, $manager, $preview);
        }
    }

    private function exportAttendance($filter, $date, $manager, $preview = false)
    {
        $query = Absensi::with(['user', 'sesiAbsensi'])
            ->whereHas('sesiAbsensi', function ($q) use ($manager) {
                $q->where('divisi_id', $manager->divisi_id);
            });

        if ($filter === 'daily') {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();
            $query->whereHas('sesiAbsensi', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            });
            $filename = "rekap_absensi_{$date}.xls";
        } else {
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate = Carbon::parse($date)->endOfMonth();
            $query->whereHas('sesiAbsensi', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            });
            $filename = "rekap_absensi_" . $startDate->format('Y_m') . ".xls";
        }

        $allData = $query->get();
        
        // Group by user and date
        $grouped = [];
        $uniqueUsers = [];
        foreach ($allData as $row) {
            $uId = $row->user_id;
            $tgl = $row->sesiAbsensi->tanggal;
            $key = "{$uId}_{$tgl}";
            $uniqueUsers[$uId] = true;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'id' => $uId,
                    'name' => $row->user->name,
                    'date' => $tgl,
                    'in' => '-',
                    'break' => '-',
                    'out' => '-',
                    'status' => strtoupper($row->status),
                    'approval' => strtoupper($row->status_persetujuan),
                    'notes' => $row->catatan
                ];
            }

            $type = strtolower($row->sesiAbsensi->tipe);
            if ($type === 'masuk') {
                $grouped[$key]['in'] = $row->jam_absen;
            } elseif ($type === 'istirahat' || $type === 'break') {
                $grouped[$key]['break'] = $row->jam_absen;
            } elseif ($type === 'pulang') {
                $grouped[$key]['out'] = $row->jam_absen;
            }
        }

        // Productivity Logic
        $userTasks = Tugas::whereIn('ditugaskan_ke', array_keys($uniqueUsers))
            ->whereHas('sesiTugas', function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })->get();

        $avgPos = $userTasks->avg('progress') ?? 0;

        $callback_content = function () use ($grouped, $uniqueUsers, $allData, $avgPos, $userTasks, $filter, $date) {
            $html = "<html>";
            $html .= "<head><meta charset='utf-8'><style>
                body { 
                    font-family: Calibri, 'Segoe UI', Arial, sans-serif; 
                    padding: 0; 
                    margin: 0; 
                    background: #ffffff; 
                }
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                    table-layout: fixed;
                }
                th, td { 
                    border: 1px solid #d1d4d9; 
                    padding: 10px 14px; 
                    font-size: 10pt; 
                    color: #000000;
                    text-align: left;
                }
                th { 
                    background-color: #f2f2f2; 
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .summary-row { background-color: #ffffff; font-weight: bold; }
                .header-title { padding: 20px; font-size: 16pt; font-weight: bold; border: none; }
            </style></head>";
            $html .= "<body>";
            $html .= "<table>";
            $html .= "<tr><td colspan='8' class='header-title'>REKAP ABSENSI KARYAWAN</td></tr>";
            $html .= "<tr><td colspan='8' style='border:none; padding: 0 20px 20px 20px; font-size: 10pt; color: #666;'>Periode: " . ($filter === 'daily' ? $date : Carbon::parse($date)->format('F Y')) . "</td></tr>";
            
            $html .= "<tr>";
            $html .= "<th>NAMA KARYAWAN</th>";
            $html .= "<th>TANGGAL</th>";
            $html .= "<th>MASUK</th>";
            $html .= "<th>ISTIRAHAT</th>";
            $html .= "<th>PULANG</th>";
            $html .= "<th>STATUS</th>";
            $html .= "<th>PROG (%)</th>";
            $html .= "<th>PERSETUJUAN</th>";
            $html .= "</tr>";

            foreach ($grouped as $row) {
                $p = $userTasks->where('ditugaskan_ke', $row['id'])->avg('progress') ?? 0;
                
                $html .= "<tr>";
                $html .= "<td>" . $row['name'] . "</td>";
                $html .= "<td class='text-center'>" . date('d/m/Y', strtotime($row['date'])) . "</td>";
                $html .= "<td class='text-center'>" . $row['in'] . "</td>";
                $html .= "<td class='text-center'>" . $row['break'] . "</td>";
                $html .= "<td class='text-center'>" . $row['out'] . "</td>";
                $html .= "<td class='text-center'>" . $row['status'] . "</td>";
                $html .= "<td class='text-right'>" . round($p, 1) . "%</td>";
                $html .= "<td>" . $row['approval'] . "</td>";
                $html .= "</tr>";
            }

            $html .= "<tr class='summary-row'><td colspan='8'>EXECUTIVE SUMMARY</td></tr>";
            $html .= "<tr><td colspan='3'>Total Karyawan Aktif</td><td colspan='5'>" . count($uniqueUsers) . " Orang</td></tr>";
            $html .= "<tr><td colspan='3'>Rata-rata Produktivitas Tim</td><td colspan='5'>" . round($avgPos, 2) . "%</td></tr>";
            $html .= "<tr><td colspan='3'>Total Catatan Absensi</td><td colspan='5'>" . $allData->count() . " Sesi</td></tr>";
            
            $html .= "</table>";
            $html .= "</body>";
            $html .= "</html>";
            return $html;
        };

        if ($preview) {
            return response($callback_content());
        }

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($callback_content) {
            echo $callback_content();
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportTasks($filter, $date, $manager, $preview = false)
    {
        $query = Tugas::with(['user', 'sesiTugas', 'divisi'])
            ->whereHas('sesiTugas', function ($q) use ($manager) {
                $q->where('divisi_id', $manager->divisi_id);
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
 
        $callback_content = function () use ($data, $filter, $date) {
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
            $html .= "<tr class='header-row'><td colspan='7' class='title-cell'>LAPORAN TUGAS DIVISI</td></tr>";
            $html .= "<tr class='header-row'><td colspan='7' style='padding: 0 15px 15px 15px; font-size: 10pt; color: #666; border-top: none;'>Periode: " . ($filter === 'daily' ? $date : Carbon::parse($date)->format('F Y')) . "</td></tr>";
            
            $html .= "<tr>
                <th>PELAKSANA</th>
                <th>TIPE</th>
                <th>JUDUL TUGAS</th>
                <th>STATUS</th>
                <th>PROGRESS</th>
                <th>PRIORITAS</th>
                <th>PERSETUJUAN</th>
            </tr>";
 
            foreach ($data as $row) {
                $html .= "<tr>
                    <td>" . ($row->tipe_penugasan === 'individu' ? $row->user->name : $row->divisi->nama) . "</td>
                    <td>" . strtoupper($row->tipe_penugasan) . "</td>
                    <td>" . $row->judul . "</td>
                    <td>" . strtoupper($row->status) . "</td>
                    <td>" . $row->progress . "%</td>
                    <td>" . strtoupper($row->prioritas) . "</td>
                    <td>" . strtoupper($row->status_persetujuan) . "</td>
                </tr>";
            }
            $html .= "</table></body></html>";
            return $html;
        };
 
        if ($preview) return response($callback_content());
 
        $headers = ['Content-Type' => 'application/vnd.ms-excel', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        return Response::stream(function() use ($callback_content) { echo $callback_content(); }, 200, $headers);
    }

    private function exportProductivity($filter, $date, $manager, $preview = false)
    {
        $employees = User::with('roles')->where('divisi_id', $manager->divisi_id)->get();
        
        if ($filter === 'daily') {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();
            $filename = "laporan_produktivitas_{$date}.xls";
        } else {
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate = Carbon::parse($date)->endOfMonth();
            $filename = "laporan_produktivitas_" . $startDate->format('Y_m') . ".xls";
        }
 
        $callback_content = function () use ($employees, $startDate, $endDate, $filter, $date) {
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
            $html .= "<tr class='header-row'><td colspan='8' class='title-cell'>ANALISIS PRODUKTIVITAS KARYAWAN</td></tr>";
            $html .= "<tr class='header-row'><td colspan='8' style='padding: 0 15px 15px 15px; font-size: 10pt; color: #666; border-top: none;'>Periode: " . ($filter === 'daily' ? $date : Carbon::parse($date)->format('F Y')) . "</td></tr>";
            
            $html .= "<tr>
                <th>NAMA KARYAWAN</th>
                <th>TOTAL TUGAS</th>
                <th>SELESAI</th>
                <th>AVG PROG (%)</th>
                <th>TOTAL POIN</th>
                <th>HARI HADIR</th>
                <th>TERLAMBAT</th>
            </tr>";
 
            foreach ($employees as $emp) {
                $isLeader = $emp->hasRole('leader');

                $tasks = Tugas::where(function($q) use ($emp, $isLeader) {
                        $q->where('ditugaskan_ke', $emp->id)
                          ->orWhere(function($sub) use ($emp, $isLeader) {
                              $sub->where('tipe_penugasan', 'divisi')
                                  ->where('divisi_id', $emp->divisi_id);
                              
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
 
                $attendance = Absensi::where('user_id', $emp->id)
                    ->whereHas('sesiAbsensi', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('tanggal', [$startDate, $endDate]);
                    })->get();
 
                $totalTasks = $tasks->count();
                $completed = $tasks->where('status', 'selesai')->count();
                $avgProgress = $totalTasks > 0 ? $tasks->avg('progress') : 0;
                $daysPresent = $attendance->whereIn('status', ['hadir', 'terlambat'])->count();
                $lates = $attendance->where('status', 'terlambat')->count();

                $totalPoints = \App\Models\SubTugas::whereIn('tugas_id', $tasks->pluck('id'))
                    ->where('assigned_to', $emp->id)
                    ->sum('bobot');

                $html .= "<tr>
                    <td>" . $emp->name . "</td>
                    <td class='text-right'>" . $totalTasks . "</td>
                    <td class='text-right'>" . $completed . "</td>
                    <td class='text-right'>" . round($avgProgress, 2) . "%</td>
                    <td class='text-right' style='font-weight:bold; color: #4338ca;'>" . $totalPoints . "</td>
                    <td class='text-right'>" . $daysPresent . "</td>
                    <td class='text-right'>" . $lates . "</td>
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
