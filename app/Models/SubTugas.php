<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubTugas extends Model
{
    protected $table = 'sub_tugas';

    protected $fillable = [
        'tugas_id',
        'judul',
        'assigned_to',
        'bobot',
        'is_completed',
        'completed_by',
        'completed_at',
        'status_usulan',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
