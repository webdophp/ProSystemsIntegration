<?php

namespace webdophp\ProSystemsIntegration\Services;

use Illuminate\Support\Collection;
use SoapClient;
use SoapFault;
use webdophp\ProSystemsIntegration\Models\ProSystemsOperation;


class ProSystemsAllDataService extends ProSystemsBaseService
{
    /**
     * @throws SoapFault
     */
    public function __construct()
    {
        $this->client = new SoapClient(config('pro-systems-integration.base_url_all').'?WSDL', [
            'trace' => 1,
            'exceptions' => true,
        ]);
    }

    public function provideOperationDetailsByUniqueId(string $token, ProSystemsOperation $operation): Collection
    {
        try {
            $response = $this->client->__soapCall('ProvideOperationDetailsByUniqueId', [[
                'token' => $token,
                'uniqueId' => $operation->unique_id,
            ]]);

            $result = $this->parseResponse($response->ProvideOperationDetailsByUniqueIdResult);

            return collect($result)->merge([
                'Operations' => collect($result['ResultObject']['Packet']['Content']['Operations'] ?? [])
            ]);
        } catch (SoapFault $e) {
            return collect(['Message' =>  $e->getMessage(),'Code' => (string) $e->getCode()]);
        }
    }


}
