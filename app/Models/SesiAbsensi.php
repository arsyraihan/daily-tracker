<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiAbsensi extends Model
{
    protected $table = 'sesi_absensi';

    protected $fillable = [
        'nama',
        'tipe',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'divisi_id',
        'dibuat_oleh',
        'status',
    ];

    protected $appends = ['is_active'];

    public function getIsActiveAttribute()
    {
        if ($this->status !== 'dibuka') {
            return false;
        }

        $now = \Carbon\Carbon::now();
        $startTime = \Carbon\Carbon::parse($this->tanggal . ' ' . $this->jam_mulai);
        $endTime = \Carbon\Carbon::parse($this->tanggal . ' ' . $this->jam_selesai);

        return $now->between($startTime, $endTime);
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'sesi_absensi_id');
    }
}
