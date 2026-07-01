<?php
namespace App\Models;

use App\Enums\IntegrationErrorCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationBatchError extends Model
{
    protected $table = 'integration_batch_errors';

    protected $fillable = [
        'integration_batch_id',
        'external_id',
        'item_index',
        'field',
        'code',
        'message',
    ];

    protected $casts = [
        'code' => IntegrationErrorCode::class,
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(
            IntegrationBatch::class,
            'integration_batch_id',
        );
    }
}
