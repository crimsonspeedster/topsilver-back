<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FilterPageFilter extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'filter_page_id',
        'attribute_id',
        'attribute_term_id',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(
            Attribute::class,
        );
    }

    public function attributeTerm(): BelongsTo
    {
        return $this->belongsTo(
            AttributeTerm::class,
        );
    }

    public function filterPage(): BelongsTo
    {
        return $this->belongsTo(
            FilterPage::class,
        );
    }
}
