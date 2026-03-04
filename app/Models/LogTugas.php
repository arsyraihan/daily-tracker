<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogTugas extends Model
{
    protected $table = 'log_tugas';

    protected $fillable = [
        'tugas_id',
        'user_id',
        'keterangan',
        'progress_sebelum',
        'progress_sesudah',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
