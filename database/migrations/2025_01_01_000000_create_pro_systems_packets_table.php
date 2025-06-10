<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_systems_packets', function (Blueprint $table) {
            $table->uuid('guid')->primary()->comment('Уникальный идентификатор пакета');
            $table->timestamp('created_at')->useCurrent()->comment('Дата получения пакета');
            $table->boolean('confirmed')->default(false)->comment('Подтвержден ли пакет');
            $table->timestamp('confirmed_at')->nullable()->comment('Дата подтверждения');
            $table->comment('Полученные пакеты');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_systems_packets');
    }
};
