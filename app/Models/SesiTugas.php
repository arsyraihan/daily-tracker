<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiTugas extends Model
{
    protected $table = 'sesi_tugas';

    protected $fillable = [
        'tanggal',
        'divisi_id',
        'dibuat_oleh',
        'status',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'sesi_tugas_id');
    }
}
