<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'task',
        'start_time',
        'end_time',
        'durasi_menit',
        'output',
        'kategori',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
