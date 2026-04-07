<?php

namespace App\Http\Repositories\PlanPrice;

use App\Http\Repositories\Base\BaseRepository;
use App\Models\PlanPrice;

class PlanPriceRepository extends BaseRepository
{
    public function __construct(PlanPrice $model)
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
        // $models->withCount(['subscriptions']);
        $models->with(['currency', 'plan', 'billingCycle']);
        $models->orderBy($sort, $order);
        // default per_page = 10
        $perPage = $request->input('per_page', 10);

        $models = $models->paginate($perPage);
        return ['status' => true, 'data' => $models];
    }
    public function create(array $data)
    {
        if (isset($data['price'])) {
            $data['price_cents'] = (int) round($data['price'] * 100);
        }

        $model = parent::create($data);
        return $model->load(['plan', 'currency', 'billingCycle']);
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
}
