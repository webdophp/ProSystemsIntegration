<?php

namespace webdophp\ProSystemsIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProSystemsItem extends Model
{
    protected $table = 'pro_systems_items';

    protected $fillable = [
        'pro_systems_operation_id',
        'code',
        'name',
        'price',
        'quantity',
        'sum',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'quantity' => 'decimal:4',
        'sum' => 'decimal:4',
    ];

    /**
     * Связь с операцией
     * @return BelongsTo
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(ProSystemsOperation::class, 'pro_systems_operation_id');
    }

    /**
     * @return HasOne
     */
    public function modifier(): HasOne
    {
        return $this->hasOne(ProSystemsItemModifier::class, 'pro_systems_item_id');
    }
}