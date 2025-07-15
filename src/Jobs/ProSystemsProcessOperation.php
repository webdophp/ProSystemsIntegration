<?php

namespace webdophp\ProSystemsIntegration\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use webdophp\ProSystemsIntegration\Mall\ProSystemsJobFailed;
use webdophp\ProSystemsIntegration\Models\ProSystemsOperation;
use webdophp\ProSystemsIntegration\Services\ProSystemsAllDataService;
use Illuminate\Support\Facades\Cache;


class ProSystemsProcessOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $timeout = 120;

    /**
     * @var int
     */
    public int $tries = 3;

    /**
     * @var ProSystemsOperation
     */
    protected ProSystemsOperation $operation;

    /**
     * @param ProSystemsOperation $operation
     */
    public function __construct(ProSystemsOperation $operation)
    {
        $this->operation = $operation;
    }

    /**
     * @param ProSystemsAllDataService $service
     * @return void
     * @throws Throwable
     */
    public function handle(ProSystemsAllDataService $service): void
    {
        try {
            Log::info("ProSystems: Start processing operation #{$this->operation->id}");

            $auth = $service->authorize();
            // Если ошибка, то прекращаем работу
            if ($auth->get('Code') !== '000') {
                throw new Exception('ProSystems: Authorization failed. Message:'.$auth->get('Message').';Code - '.$auth->get('Code'), 1001);
            }
            $resultObject = $auth->get('ResultObject');
            if (!is_array($resultObject) || !isset($resultObject['Token'])) {
                throw new Exception('ProSystems: Missing token in authorization response.', 1001);
            }
            $token = $resultObject['Token'];
            if (!$token) {
                throw new Exception('ProSystems: Token authorization failed.', 1001);
            }

            $data = $service->provideOperationDetailsByUniqueId($token, $this->operation);
            // Если ошибка, то прекращаем работу
            if ($data->get('Code') !== '000') {
                throw new Exception('ProSystems:  Message:'.$data->get('Message').';Code - '.$data->get('Code'), 1002);
            }
            $soapResponse = $data->get('ResultObject');


            DB::transaction(function () use ($soapResponse) {


                // Скидка или наценка на весь чек (если есть)
                if (isset($soapResponse['Operation']['Modifier'])) {
                    $this->operation->modifier()->create([
                        'type' => $soapResponse['Operation']['Modifier']['Type'],
                        'sum' => $soapResponse['Operation']['Modifier']['Sum']
                    ]);
                }

                // Товары
                if (isset($soapResponse['Operation']['Items']['Item'])) {
                    $items = $soapResponse['Operation']['Items']['Item'];
                    // Приводим к массиву платежей
                    $soapResponseItems = isset($items[0]) ? $items : [$items];


                    foreach ($soapResponseItems as $itemData) {
                        $item = $this->operation->items()->create([
                            'code' => $itemData['Code'] ?? null,
                            'name' => $itemData['Name'] ?? null,
                            'price' => $itemData['Price'] ?? null,
                            'quantity' => $itemData['Quantity'] ?? null,
                            'sum' => $itemData['Sum'] ?? null,
                        ]);

                        // Скидка или наценка на товар (если есть)
                        if (isset($itemData['Modifier'])) {
                            $item->modifier()->create([
                                'type' => $itemData['Modifier']['Type'],
                                'sum' => $itemData['Modifier']['Sum']
                            ]);
                        }
                    }
                }

                $this->operation->received_detailed = true;
                $this->operation->save();
            });


        } catch (Throwable $e) {
            Log::error("ProSystems: Ошибка при обработке операции ID  #{$this->operation->id}: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Handles the failed state of a job by logging the error details and optionally sending a notification email.
     *
     * @param Throwable $exception The exception that caused the job to fail, containing error details.
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $operation_id = $this->operation->id;
        //Ключ для кэша
        $cacheKey = "pro_systems_error_sent_email";

        if (config('pro-systems-integration.error_log', false)) {
            Log::error(config('pro-systems-integration.mail_subject', 'Job failed ProSystemsProcessOperation'), [
                'operation_id' => $operation_id,
                'code' => $exception->getCode(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }


        // Проверяем, отправляли ли уже email за последний час
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, now()->addHour());
            if (config('pro-systems-integration.error_mail', false)) {
                Mail::to(config('pro-systems-integration.mail_to'))->queue(
                    new ProSystemsJobFailed(
                        "[Операция ID {$operation_id}] " . $exception->getCode() . ': ' . $exception->getMessage(),
                        $exception->getTraceAsString()
                    )
                );
            }
        }
    }
}
