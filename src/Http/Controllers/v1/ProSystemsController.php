<?php

namespace webdophp\ProSystemsIntegration\Http\Controllers\v1;


use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use webdophp\ProSystemsIntegration\Http\Resources\v1\ProSystemsCollection;
use webdophp\ProSystemsIntegration\Models\ProSystemsOperation;


class ProSystemsController
{
    /**
     * Проверка работает сервис или нет
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => 'ProSystems API Controller is working!']);
    }


    /**
     * Взять пачку записей
     * @return JsonResponse|ProSystemsCollection
     */
    public function data(): JsonResponse|ProSystemsCollection
    {
        try{
            $records = ProSystemsOperation::where('received_data', false)
                ->with([
                    'packet' => function ($query) {
                        $query->select('guid','created_at');
                    },
                    'payments' => function ($query) {
                        $query->select('id','pro_systems_operation_id','type','sum','created_at');
                    },
                    'items' => function ($query) {
                        $query->select('id','pro_systems_operation_id','code','name','price','quantity','sum','created_at');
                    },
                    'modifier' => function ($query) {
                        $query->select('id','pro_systems_operation_id','type','sum','created_at');
                    }
                ])
                ->orderBy('id', 'ASC')
                ->limit(100)
                ->get();


            //Если нет строк, то статус код 204 нет данных
            if ($records->isEmpty()) {
                return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
            }
            //Берем только id
            $ids = $records->pluck('id');
            //Обновляем данные и говорим, что показали данные в запросе
            ProSystemsOperation::whereIn('id', $ids)->update([
                'sent_data' => true,
                'date_sent_data' => now(),
            ]);

            return new ProSystemsCollection($records);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Подтвердить получение данных
     * @return JsonResponse
     */
    public function confirm(): JsonResponse
    {
        try{
            //Обновляем данные и говорим, что мы показали и приняли данные и больше их не показываем
            ProSystemsOperation::where('sent_data', true)->update(['received_data' => true]);
            return response()->json(['status' => 'success', 'message' => Response::$statusTexts[Response::HTTP_OK]]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
