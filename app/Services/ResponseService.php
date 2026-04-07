<?php

namespace App\Services;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseService
{
    public function json($message = 'Success!', $data = [], $status = 200, $errors = null, $paginate = null)
    {

        if ($data instanceof Collection && $data->resource instanceof LengthAwarePaginator) {
            $pagination = $this->paginate($data);
        }

        if (is_array($data) && $paginate && $data[$paginate] instanceof Collection && $data[$paginate]->resource instanceof LengthAwarePaginator) {
            $pagination = $this->paginate($data[$paginate]);
        }

        $arrayMes = $errors ? ['message' => $errors['error'][0], 'errors' => $errors['error']] : null;
        return response()->json([
            'status' => $message,
            'data' => $data,
            'pagination' => $pagination ?? null,
            'errors' => $arrayMes
        ], $status);
    }


    public function paginate(Collection $collection)
    {
        return [
            'per_page' => $collection->perPage(),
            'path' => $collection->path(),
            'total' => $collection->total(),
            'current_page' => $collection->currentPage(),
            'next_page_url' => $collection->nextPageUrl(),
            'previous_page_url' => $collection->previousPageUrl(),
            'last_page' => $collection->lastPage(),
            'has_more_pages' => $collection->hasMorePages()
        ];
    }
}
