<?php

namespace App\Repositories\Manager\Absensi;

use App\Models\SesiAbsensi;

class SesiAbsensiRepository implements SesiAbsensiRepositoryInterface
{
    public function getAllByDivision(int $divisionId, ?string $date = null)
    {
        return SesiAbsensi::where('divisi_id', $divisionId)
            ->when($date, function($q) use ($date) {
                return $q->where('tanggal', $date);
            })
            ->with(['divisi', 'creator'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(10);
    }

    public function find(int $id)
    {
        return SesiAbsensi::with(['divisi', 'creator', 'absensi.user'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return SesiAbsensi::create($data);
    }

    public function update(int $id, array $data)
    {
        $session = SesiAbsensi::findOrFail($id);
        $session->update($data);
        return $session;
    }

    public function delete(int $id)
    {
        $session = SesiAbsensi::findOrFail($id);
        return $session->delete();
    }

    public function closeSession(int $id)
    {
        $session = SesiAbsensi::findOrFail($id);
        $session->status = 'ditutup';
        $session->save();
        return $session;
    }
}
