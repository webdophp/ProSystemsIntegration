<?php

namespace webdophp\ProSystemsIntegration\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProSystemsOperation extends Model
{

    protected $fillable = [
        'company_id',
        'pro_systems_packet_guid',
        'kkm_code',
        'type',
        'tax_payer_bin',
        'operation_date',
        'document_number',
        'work_session_number',
        'unique_id',
        'tag',
        'cashier',
        'amount',
        'operation_type',
        'sent_data',
        'date_sent_data',
        'received_data'
    ];

    protected $casts = [
        'operation_date' => 'datetime',
        'amount' => 'decimal:4',
    ];


    /**
     * Мутатор для поля operation_date
     * из строковой переменной 24.04.2025 12:13:55,000 GMT+02:00 в  d.m.Y H:i:s.u P
     * @return Attribute
     */
    protected function operationDate(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (is_string($value)) {
                    // Приводим строку к читаемому Carbon формату
                    $formatted = str_replace([',', 'GMT'], ['.', ''], $value);

                    try {
                        return Carbon::createFromFormat('d.m.Y H:i:s.u P', $formatted);
                    } catch (\Exception $e) {
                        // Можешь логировать или выбросить исключение
                        throw new Exception("Неверный формат даты: $value");
                    }
                }

                // Если это уже Carbon объект или null
                return $value;
            }
        );
    }

    /**
     * Связь с пакетом
     * @return BelongsTo
     */
    public function packet(): BelongsTo
    {
        return $this->belongsTo(ProSystemsPacket::class, 'pro_systems_packet_guid', 'guid');
    }

    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ProSystemsPayment::class, 'pro_systems_operation_id');
    }

    /**
     * Связь с товаром или услугой
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProSystemsItem::class, 'pro_systems_operation_id');
    }

    /**
     * @return HasOne
     */
    public function modifier(): HasOne
    {
        return $this->hasOne(ProSystemsOperationModifier::class, 'pro_systems_operation_id');
    }
}