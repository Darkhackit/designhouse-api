<?php

namespace App\Repositories\Contracts;

interface Ibase
{
    public function all();
    public function find($id);
    public function findWhere($colum , $value);
    public function findWhereFirst($colum , $value);
    public function paginate($perPage = 10);
    public function create(array $array);
    public function update($id, array $array);
    public function delete($id);
}
