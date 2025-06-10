<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_systems_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_systems_operation_id')->comment('Ссылка на операцию')->constrained('pro_systems_operations')->onDelete('cascade');
            $table->enum('type', ['Cash', 'Card', 'Credit', 'Tare', 'Mobile'])->comment('Тип платежа');
            $table->decimal('sum', 18, 4)->comment('Сумма платежа');
            $table->timestamps();
            $table->comment('Платежи');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_systems_payments');
    }
};