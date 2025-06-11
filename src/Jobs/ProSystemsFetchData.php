<?php

namespace webdophp\ProSystemsIntegration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use webdophp\ProSystemsIntegration\Mall\ProSystemsJobFailed;
use webdophp\ProSystemsIntegration\Models\ProSystemsPacket;
use webdophp\ProSystemsIntegration\Services\ProSystemsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;
use Illuminate\Support\Facades\Mail;
use Exception;

class ProSystemsFetchData implements ShouldQueue
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

    /**
     * ID компании чей чек
     * @var int
     */
    protected int $company_id;

    public function __construct(string $login, string $password, int $company_id)
    {
        $this->login = $login;
        $this->password = $password;
        $this->company_id = $company_id;
    }

    /**
     * @throws Throwable
     */
    public function handle(ProSystemsService $service): void
    {

        $dispatchNext = false;
        DB::transaction(function () use ($service, &$dispatchNext) {
            $auth = $service->authorize($this->login,  $this->password);
            // Если ошибка, то прекращаем работу
            if ($auth->has('error')) {
                throw new Exception('ProSystems: '.$auth->get('error').';Code - '.$auth->get('Code'), 1003);
            }
            if ($auth->get('Code') !== '000') {
                throw new Exception('ProSystems: Authorization failed.'.';Code - '.$auth->get('Code'), 1004);
            }


            $resultObject = $auth->get('ResultObject');
            if (!is_array($resultObject) || !isset($resultObject['Token'])) {
                throw new Exception('ProSystems: Missing token in authorization response.', 1005);
            }
            $token = $resultObject['Token'];
            if (!$token) {
                throw new Exception('ProSystems: Token authorization failed.', 1001);
            }

            $data = $service->provideData($token);
            // Если ошибка, то прекращаем работу
            if ($data->has('error')) {
                throw new Exception('ProSystems: '.$data->get('error'). ';Code - '.$data->get('Code'), 1003);
            }
            // Нет новых данных — корректное завершение без исключения
            if ($data->get('Code') === '110') {
                Log::info('ProSystems: Все пакеты успешно получены и подтвержден.');
                return;
            }
            if ($data->get('Code') !== '000') {
                throw new Exception('ProSystems: Error receiving data: '.$data->get('error'). ';Code - '.$data->get('Code'), 1004);
            }
            $soapResponse = $data->get('ResultObject');

            if (
                !isset($soapResponse['Packet']['Guid']) ||
                !isset($soapResponse['Packet']['Content']['Operations']['BaseOperation']) ||
                !is_array($soapResponse['Packet']['Content']['Operations']['BaseOperation'])
            ) {
                throw new Exception('ProSystems: Invalid or missing structure in SOAP response.', 1002);
            }


            // Проверка: если такой пакет уже был получен, не обрабатываем повторно
            if (ProSystemsPacket::where('guid', $soapResponse['Packet']['Guid'])->exists()) {
                Log::info('ProSystems: Пакет с GUID уже существует и будет пропущен: ' . $soapResponse['Packet']['Guid']);
                //Делаем подтверждения данных
                $confirm = $service->confirmData($token, $soapResponse['Packet']['Guid']);

                // Если ошибка, то прекращаем работу
                if ($confirm->has('error')) {
                    throw new Exception('ProSystems: '.$confirm->get('error'). ';Code - '.$data->get('Code'), 1003);
                }
                if ($confirm->get('Code') !== '000') {
                    throw new Exception('ProSystems: Data confirmation error.'. ';Code - '.$data->get('Code'), 1004);
                }
                $dispatchNext = true;

                return;
            }

            // Сохраняем пакет
            $packet = ProSystemsPacket::create([
                'guid' => $soapResponse['Packet']['Guid'],
                'confirmed' => false
            ]);



            if (empty($soapResponse['Packet']['Content']['Operations']['BaseOperation'])) {
                throw new Exception('ProSystems: No operations found in SOAP response.', 1004);
            }

            // Обрабатываем каждую операцию и сохраняем ее в базу
            $operations = $soapResponse['Packet']['Content']['Operations']['BaseOperation'];
            $operations = isset($operations[0]) ? $operations : [$operations];
            foreach ($operations as $operationData) {

                $operation = $packet->operations()->create([
                    'packet_guid' => $packet->guid,
                    'company_id' => $this->company_id,
                    'kkm_code' => $operationData['KKMCode'],
                    'type' => $operationData['Type'],
                    'tax_payer_bin' => $operationData['TaxPayerBIN'],
                    'operation_date' => $operationData['DateTime'],
                    'document_number' => $operationData['DocumentNumber'],
                    'work_session_number' => $operationData['WorkSessionNumber'],
                    'unique_id' => $operationData['UniqueId'],
                    'tag' => $operationData['Tag'] ?? null,
                    'cashier' => $operationData['Cashier'],
                    'amount' => $operationData['Amount'],
                    'operation_type' => $operationData['xsiType'] ?? null
                ]);


                // Сохраняем платежи
                if (isset($operationData['Payments']['Payment'])) {
                    $payments = $operationData['Payments']['Payment'];
                    // Приводим к массиву платежей
                    $payments = isset($payments[0]) ? $payments : [$payments];
                    foreach ($payments as $paymentData) {
                        $operation->payments()->create([
                            'type' => $paymentData['Type'],
                            'sum' => $paymentData['Sum'],
                        ]);
                    }
                }


                // Скидка или наценка на весь чек (если есть)
                if (isset($operationData['Modifier'])) {
                    $operation->modifier()->create([
                        'type' => $operationData['Modifier']['Type'],
                        'sum' => $operationData['Modifier']['Sum']
                    ]);
                }

                // Товары
                if (isset($operationData['Items'])) {
                    foreach ($operationData['Items'] as $itemData) {
                        $item = $operation->items()->create([
                            'code' => $itemData['Code'],
                            'name' => $itemData['Name'],
                            'price' => $itemData['Price'],
                            'quantity' => $itemData['Quantity'],
                            'sum' => $itemData['Sum']
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
            }


            //Делаем подтверждения данных
            $confirm = $service->confirmData($token, $soapResponse['Packet']['Guid']);

            // Если ошибка, то прекращаем работу
            if ($confirm->has('error')) {
                throw new Exception('ProSystems: '.$confirm->get('error'). ';Code - '.$data->get('Code'), 1003);
            }
            if ($confirm->get('Code') !== '000') {
                throw new Exception('ProSystems: Data confirmation error. '. ';Code - '.$data->get('Code'), 1004);
            }

            // Делаем подтверждение, что пакет загружен
            $packet->confirmed = true;
            $packet->confirmed_at = now();
            $packet->save();

            $dispatchNext = true;
        });

        if ($dispatchNext) {
            ProSystemsFetchData::dispatch($this->login, $this->password, $this->company_id);
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