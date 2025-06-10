<?php

namespace webdophp\ProSystemsIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProSystemsItemModifier extends Model
{

    protected $fillable = [
        'pro_systems_item_id',
        'type',
        'sum',
    ];

    protected $casts = [
        'sum' => 'decimal:4',
    ];

    /**
     * Связь с товаром или услугой
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ProSystemsItem::class, 'pro_systems_item_id');
    }
}