<?php

namespace webdophp\ProSystemsIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProSystemsPacket extends Model
{
    // Первичный ключ — это UUID, а не автоинкремент
    protected $primaryKey = 'guid';

    public $incrementing = false;

    protected $keyType = 'string';

    // В таблице только одно поле created_at, нет updated_at
    public $timestamps = false;

    // Атрибуты, которые можно массово заполнять
    protected $fillable = [
        'guid',
        'created_at',
        'confirmed',
        'confirmed_at',
    ];

    // Кастинг типов полей
    protected $casts = [
        'created_at'    => 'datetime',
        'confirmed'     => 'boolean',
        'confirmed_at'  => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function operations(): HasMany
    {
        return $this->hasMany(ProSystemsOperation::class, 'packet_guid', 'guid');
    }
}