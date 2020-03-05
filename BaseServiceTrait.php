<?php

namespace App\Traits;

trait BaseServiceTrait
{
    
    public function create($data)
    {
        $r = $this->getRepo()->create($data);

        return $r;
    }

    protected function getRepo()
    {
        if (is_null($this->_repo)) {
            throw \Exception('请先绑定repository');
        }

        return $this->_repo;
    }
}
