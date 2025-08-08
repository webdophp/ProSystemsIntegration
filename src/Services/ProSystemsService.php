<?php

namespace webdophp\ProSystemsIntegration\Services;

use Illuminate\Support\Collection;
use SoapClient;
use SoapFault;

class ProSystemsService extends ProSystemsBaseService
{

    /**
     * @throws SoapFault
     */
    public function __construct()
    {

        $options = [
            'trace' => true,
            'exceptions' => true,
        ];

        if (!config('pro-systems-integration.ssl')) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);
            $options['stream_context'] = $context;
        }

        $this->client = new SoapClient(config('pro-systems-integration.base_url').'?WSDL', [
            $options
        ]);

    }

    /**
     * Получение данных (ProvideData).
     */
    public function provideData(string $token): Collection
    {
        try {
            $response = $this->client->__soapCall('ProvideData', [[
                'token' => $token,
            ]]);

            $result = $this->parseResponse($response->ProvideDataResult);

            return collect($result)->merge([
                'Operations' => collect($result['ResultObject']['Packet']['Content']['Operations'] ?? [])
            ]);
        } catch (SoapFault $e) {
            return collect(['error' => $e->getMessage()]);
        }
    }

    /**
     * Подтверждение получения пакета данных (ConfirmData).
     */
    public function confirmData(string $token, string $packetGuid): Collection
    {
        try {
            $response = $this->client->__soapCall('ConfirmData', [[
                'token' => $token,
                'packetGuid' => $packetGuid,
            ]]);

            return collect($this->parseResponse($response->ConfirmDataResult));
        } catch (SoapFault $e) {
            return collect(['error' => $e->getMessage()]);
        }
    }

}
