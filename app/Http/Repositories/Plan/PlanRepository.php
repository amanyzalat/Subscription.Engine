<?php

namespace App\Http\Repositories\Plan;

use App\Http\Repositories\Base\BaseRepository;
use App\Models\Plan;

class PlanRepository extends BaseRepository
{
    public function __construct(Plan $model)
    {
        parent::__construct($model);
    }
    public function models($request)
    {
        [$sort, $order] = $this->setSortParams($request, 'created_at', 'desc');
        $models = $this->model->where(function ($query) use ($request) {
            if ($request->filled('active_only')) {
                $query->where('is_active', $request->active_only);
            }

            if ($request->filled('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }
        });

        $models->orderBy($sort, $order);
        // default per_page = 10
        $perPage = $request->input('per_page', 10);

        $models = $models->paginate($perPage);
        return ['status' => true, 'data' => $models];
    }
    public function subscriptions($id)
    {
        return $this->model->find($id)->subscriptions();
    }
    public function prices($id)
    {
        return $this->model->find($id)->prices();
    }
    public function delete($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            return ['status' => false, 'message' => 'Plan not found'];
        }
        if ($model->subscriptions()->count() > 0) {
            return ['status' => false, 'message' => 'Plan has active subscriptions'];
        }
        $model->prices()->delete();
        $model->delete();
        return ['status' => true];
    }
    public function activePlansWithPrices($id)
    {
        $models = $this->model->where('is_active', true)->with([
            'prices.currency',
            'prices.billingCycle',
        ])->findOrFail($id);
        return ['status' => true, 'data' => $models];
    }
}
