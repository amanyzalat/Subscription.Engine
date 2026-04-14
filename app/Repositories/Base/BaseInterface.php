<?php

namespace App\Repositories\Base;

interface BaseInterface
{
  public function all($columns = ['*']);
  public function find($id, $columns = ['*']);
  public function findOrFail($id, $columns = ['*']);
  public function create(array $data);
  public function update(array $data, $id);
  public function delete($id);
  public function where(array $conditions);
  public function setSortParams($request, string $defaultSort, string $defaultOrder);
  public function with($relations);
}
