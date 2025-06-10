<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_systems_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_systems_item_id')->comment('Ссылка на товар/услугу')->constrained('pro_systems_items')->onDelete('cascade');
            $table->enum('type', ['Discount', 'Markup'])->comment('Скидка или наценка');
            $table->decimal('sum', 18, 4)->comment('Сумма скидки или наценки');
            $table->timestamps();
            $table->comment('Товары/услуги');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_systems_item_modifiers');
    }
};