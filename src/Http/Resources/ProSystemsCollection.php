<?php

namespace webdophp\ProSystemsIntegration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ProSystemsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into a Collection.
     *
     * @param Request $request
     * @return Collection
     */
    public function toArray(Request $request): Collection
    {
        return $this->collection->transform(function ($item) use ($request) {
            return  [
                'cashbox_unique_number' => $item->kkm_code,
                'shift_number' => $item->work_session_number,
                'operation_type' => $item->type,
                'sum' => $item->amount,
                'date_operation' => $item->operation_date,
                'employee_code' => $item->cashier,
                'number' => $item->document_number,
                'is_offline' => true,

                'tax_payer_bin' => $item->tax_payer_bin,
                'unique_id' => $item->unique_id,
                'tag' => $item->tag,

                'packet' => $item->packet ?? null,
                'payments' => $item->payments ?? null,
                'items' => $item->items ?? null,
                'modifier' => $item->modifier ?? null,
            ];
        });
    }

    /**
     * @param $request
     * @return array
     */
    public function with($request): array
    {
        return [
            'status' => 'success',
            'message' => Response::$statusTexts[Response::HTTP_OK],
        ];
    }
}
