<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_systems_operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('packet_guid')->comment('GUID пакета');
            $table->string('kkm_code')->comment('Код ККМ');
            $table->enum('type', [
                'Sell', 'SellReturn', 'Buy', 'BuyReturn',
                'Deposit', 'Withdrawal', 'WithdrawalOnClose'
            ])->comment('Тип операции');
            $table->string('tax_payer_bin')->nullable('БИН налогоплательщика');
            $table->dateTime('operation_date')->comment('Дата и время операции');
            $table->string('document_number')->comment('Номер документа');
            $table->integer('work_session_number')->comment('Номер смены');
            $table->uuid('unique_id')->unique()->comment('Уникальный ID операции');
            $table->string('tag')->nullable()->comment('Ярлык операции');
            $table->string('cashier')->comment('Кассир');
            $table->decimal('amount', 18, 4)->comment('Сумма операции');
            $table->string('operation_type')->nullable()->comment('Тип объекта: Operation, SimpleOperation, ServiceOperation');
            $table->boolean('received_detailed')->default(false)->comment('Получил подробные данные');
            $table->boolean('sent_data')->default(false)->comment('Отправил данные');
            $table->dateTimeTz('date_sent_data')->nullable()->comment('Дата отправки данных');
            $table->boolean('received_data')->default(false)->comment('Полученные данные');
            $table->timestamps();

            $table->foreign('packet_guid')->references('guid')->on('pro_systems_packets');
            $table->index(['kkm_code', 'operation_date']);
            $table->comment('Операций');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_systems_operations');
    }
};
