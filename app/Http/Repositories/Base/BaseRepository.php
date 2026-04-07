<?php

namespace App\Http\Repositories\Base;

use Illuminate\Database\Eloquent\Model;
use App\Http\Repositories\Base\BaseInterface;

class BaseRepository implements BaseInterface
{


    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail($id, $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }
    public function with($relations)
    {
        return $this->model->with($relations);
    }
    // sort
    public function setSortParams($request)
    {
        switch ($request->sort) {
            default:
                $sort = $request->sort ?: 'created_at';
                break;
        }

        switch ($request->order) {
            default:
                $order = $request->order ?: 'desc';
                break;
        }

        return [$sort, $order];
    }
    public function update(array $data, $id)
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete($id)
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    public function where(array $conditions)
    {
        return $this->model->where($conditions);
    }
}
