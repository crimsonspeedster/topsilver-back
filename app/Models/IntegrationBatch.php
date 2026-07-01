<?php

namespace App\Models;

use App\Enums\IntegrationBatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationBatch extends Model
{
    protected $table = 'integration_batches';

    protected $fillable = [
        'integration',
        'entity',
        'status',
        'items_count',
        'payload',
        'error_message',
        'started_at',
        'finished_at',
        'processed_count',
        'failed_count',
    ];

    protected $casts = [
        'status' => IntegrationBatchStatus::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'items_count' => 'integer',
        'failed_count' => 'integer',
        'processed_count' => 'integer',
    ];

    public function errors(): HasMany
    {
        return $this->hasMany(
            IntegrationBatchError::class,
            'integration_batch_id',
        );
    }
}
