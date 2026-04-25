<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NPSyncLog extends Model
{
    protected $table = 'np_sync_logs';

    protected $fillable = [
        'type',
        'started_at',
        'finished_at',
        'success',
        'items_processed',
        'error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'success' => 'boolean',
        'items_processed' => 'integer',
    ];
}
