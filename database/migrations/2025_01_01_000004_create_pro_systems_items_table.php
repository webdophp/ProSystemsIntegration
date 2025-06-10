<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_systems_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_systems_operation_id')->comment('Ссылка на операцию')->constrained('pro_systems_operations')->onDelete('cascade');
            $table->bigInteger('code')->nullable()->comment('Код товара');
            $table->string('name')->comment('Наименование');
            $table->decimal('price', 18, 4)->comment('Цена');
            $table->decimal('quantity', 18, 4)->comment('Количество');
            $table->decimal('sum', 18, 4)->comment('Сумма без скидки и наценки');
            $table->timestamps();
            $table->comment('Товары/услуги');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_systems_items');
    }
};