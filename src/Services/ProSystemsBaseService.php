<?php

namespace webdophp\ProSystemsIntegration\Services;

use Illuminate\Support\Collection;
use SoapClient;
use SoapFault;

class ProSystemsBaseService
{

    protected SoapClient $client;


    /**
     * Авторизация: получение токена.
     * @return Collection
     */
    public function authorize(): Collection
    {
        try {
            $response = $this->client->__soapCall('Authorize', [[
                'login' => config('pro-systems-integration.login'),
                'password' => config('pro-systems-integration.password'),
            ]]);

            return collect($this->parseResponse($response->AuthorizeResult));
        } catch (SoapFault $e) {
            return collect(['error' => $e->getMessage()]);
        }
    }



    /**
     * Вспомогательный метод для обработки структуры ответа
     */
    protected function parseResponse(object $response): array
    {
        return [
            'Code' => $response->Code ?? null,
            'Message' => $response->Message ?? null,
            'ResultObject' => isset($response->ResultObject)
                ? $this->unwrapSoapObject($response->ResultObject)
                : [],
        ];
    }

    /**
     * Рекурсивно разворачивает SOAP-объекты, убирая enc_* обёртки.
     */
    protected function unwrapSoapObject($data)
    {
        // Если это объект — превращаем в массив
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        // Если это enc_value — разворачиваем его
        if (is_array($data) && isset($data['enc_value'])) {
            return $this->unwrapSoapObject($data['enc_value']);
        }

        // Рекурсивно обрабатываем вложенные массивы
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->unwrapSoapObject($value);
            }
        }

        return $data;
    }
}
