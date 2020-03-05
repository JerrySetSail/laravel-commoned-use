<?php

namespace App\Services;


use App\Repositories\BaseRepository;

abstract class BaseService
{
    protected static $instance = [];
    protected $_repo;

    public function __construct(BaseRepository $baseRepository = null)
    {
        $this->_repo = $baseRepository;
    }

    public static function getInstance()
    {
        $className = get_called_class();
        if (!isset(self::$instance[$className])) {
            self::$instance[$className] = app($className);
        }
        return self::$instance[$className];
    }

    public function __call($method, $parameters)
    {
        if (is_null($this->_repo)) {
            throw new \Exception('方法不存在，或未绑定 repository');
        }
        return call_user_func_array([$this->_repo, $method], $parameters);
    }
}
