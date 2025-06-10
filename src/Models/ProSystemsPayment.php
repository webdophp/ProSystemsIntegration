<?php

namespace webdophp\ProSystemsIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProSystemsPayment extends Model
{

    protected $fillable = [
        'pro_systems_operation_id',
        'type',
        'sum',
    ];

    protected $casts = [
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
}