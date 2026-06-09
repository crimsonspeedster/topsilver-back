<?php

namespace App\Models;

use App\Enums\IntegrationBatchStatus;
use Illuminate\Database\Eloquent\Model;

class IntegrationBatch extends Model
{
    protected $fillable = [
        'integration',
        'entity',
        'status',
        'items_count',
        'payload',
        'error_message',
        'processed_at',
        'processed_count',
        'failed_count',
    ];

    protected $casts = [
        'status' => IntegrationBatchStatus::class,
        'processed_at' => 'datetime',
        'items_count' => 'integer',
        'failed_count' => 'integer',
        'processed_count' => 'integer',
    ];
}
