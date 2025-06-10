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

class ProSystemsFetchAllData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Логин для авторизации
     * @var string
     */
    protected string $login;

    /**
     * Пароль для авторизации
     * @var string
     */
    protected string $password;


    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
    }


    /**
     * @throws Throwable
     */
    public function handle(ProSystemsAllDataService $service): void
    {
        $proSystemsOperation = ProSystemsOperation::where('received_detailed', false)->get();
        foreach ($proSystemsOperation as $operation) {
            try {
                $auth = $service->authorize($this->login, $this->password);
                if ($auth->has('error')) {
                    throw new Exception('ProSystems: ' . $auth->get('error'), 1003);
                }
                if ($auth->get('Code') !== '000') {
                    throw new Exception('ProSystems: Authorization failed.', (int)$auth->get('Code'));
                }
                $resultObject = $auth->get('ResultObject');
                if (!is_array($resultObject) || !isset($resultObject['Token'])) {
                    throw new Exception('ProSystems: Missing token in authorization response.', 1005);
                }
                $token = $resultObject['Token'];
                if (!$token) {
                    throw new Exception('ProSystems: Token authorization failed.', 1001);
                }

                $data = $service->provideOperationDetailsByUniqueId($token, $operation);
                // Если ошибка, то прекращаем работу
                if ($data->has('error')) {
                    throw new Exception('ProSystems: ' . $data->get('error'), 1003);
                }
                if ($data->get('Code') !== '000') {
                    throw new Exception('ProSystems: ' . $data->get('error') . '  - ' . $data->get('Code'), 1002);
                }
                $soapResponse = $data->get('ResultObject');


                DB::transaction(function () use ($soapResponse, $operation) {

                    // Скидка или наценка на весь чек (если есть)
                    if (isset($soapResponse['Operation']['Modifier'])) {
                        $operation->modifier()->create([
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
                            $item = $operation->items()->create([
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

                    $operation->received_detailed = true;
                    $operation->save();
                });
            } catch (\Exception $e) {
                Log::error('ProSystems: Ошибка при обработке операции ID ' . $operation->id . ': ' . $e->getMessage());
                continue;
            }
        }
    }




    /**
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        if(config('pro-systems-integration.error_log', false)){
            Log::error(config('pro-systems-integration.mail_subject'), [
                'code' => $exception->getCode(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        if(config('pro-systems-integration.error_mail', false)) {
            Mail::to(config('pro-systems-integration.mail_to'))->send(
                new ProSystemsJobFailed(
                    $exception->getCode().': '.$exception->getMessage(),
                    $exception->getTraceAsString()
                )
            );
        }
    }
}