<?php


namespace webdophp\ProSystemsIntegration\Console\Commands;


use Illuminate\Console\Command;
use Throwable;
use webdophp\ProSystemsIntegration\Jobs\ProSystemsFetchAllData;

class ProSystemsDataInfo extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'app:pro-systems-data-info';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Выполняет синхронизацию детальной выгрузка данных из ProSystems';

    /**
     * Выполнение команды
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            $this->info('ProSystems Info command started  at ' . now());
            ProSystemsFetchAllData::dispatch();
            $this->info('ProSystems Info completed successfully at ' . now());
        } catch (Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}

