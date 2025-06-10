<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_systems_operation_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_systems_operation_id')->comment('Ссылка на операцию')->constrained('pro_systems_operations')->onDelete('cascade');
            $table->enum('type', ['Discount', 'Markup'])->comment('Тип скидка или наценка');
            $table->decimal('sum', 18, 4)->comment('Сумма Скидки или наценки');
            $table->timestamps();
            $table->comment('Скидки и наценки');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_systems_operation_modifiers');
    }
};