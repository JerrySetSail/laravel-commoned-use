<?php

namespace App\Repositories;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $_model;

    public function __construct(Model $model = null)
    {
        $this->_model = $model;
    }
}
