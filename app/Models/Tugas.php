<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = [
        'sesi_tugas_id',
        'divisi_id',
        'ditugaskan_ke',
        'ditugaskan_oleh',
        'tipe_penugasan',
        'judul',
        'deskripsi',
        'prioritas',
        'status',
        'status_persetujuan',
        'progress',
        'deadline',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    protected $casts = [
        'deadline' => 'datetime',
    ];

    protected $appends = ['is_overdue'];

    public function sesiTugas()
    {
        return $this->belongsTo(SesiTugas::class, 'sesi_tugas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'ditugaskan_ke');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'ditugaskan_oleh');
    }

    public function logs()
    {
        return $this->hasMany(LogTugas::class, 'tugas_id');
    }

    public function subTugas()
    {
        return $this->hasMany(SubTugas::class, 'tugas_id');
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->deadline || $this->status === 'selesai') {
            return false;
        }

        return now()->gt($this->deadline);
    }
}
