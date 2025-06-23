<?php


namespace webdophp\ProSystemsIntegration\Console\Commands;


use Illuminate\Console\Command;
use Throwable;
use webdophp\ProSystemsIntegration\Jobs\ProSystemsFetchData;

class ProSystemsData extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'app:pro-systems-data';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Выполняет синхронизацию пакетной загрузки данных из ProSystems';

    /**
     * Выполнение команды
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            $this->info('ProSystems command started at ' . now());
            ProSystemsFetchData::dispatch();
            $this->info('ProSystems completed successfully at ' . now());
        } catch (Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}

