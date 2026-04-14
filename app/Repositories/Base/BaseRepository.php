<?php

namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Base\BaseInterface;

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
    public function setSortParams($request, string $defaultSort = 'created_at', string $defaultOrder = 'desc')
    {
        return [
            $request->input('sort', $defaultSort),
            $request->input('order', $defaultOrder),
        ];
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
