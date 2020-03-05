<?php

namespace App\Traits;

trait BaseRepositoryTrait
{
    public function create($data)
    {
        $m = $this->getModel()->create($data);

        return $m;
    }

    public function getDetail($id)
    {
        $m = $this->getModel()->find($id);

        return $m;
    }

    protected function getModel()
    {
        if (is_null($this->_model)) {
            throw \Exception('请先绑定model');
        }

        return $this->_model;
    }
}
