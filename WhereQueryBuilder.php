<?php

namespace App\Common;

use Illuminate\Database\Eloquent\Builder;
use Closure;

class WhereQueryBuilder
{
    protected $where = [];
    protected $whereIn = [];
    protected $whereLike = [];
    protected $whereBetween = [];           // example: ['created_at' => ['created_at_start', 'create_at_end']]
    protected $needWhereColumns = [];
    protected $request;
    protected $builder;

    public function __construct(array $whereList = [])
    {
        if (!empty($whereList)) {
            $this->init($whereList);
        }
    }

    protected function init(array $whereList)
    {
        foreach ($whereList as $column => $wheres) {
            $funcName = 'set'.ucfirst($column);
            if (method_exists($this, $funcName)) {
                $this->$funcName($wheres);
            }
        }
    }

    public function setWhere(array $where)
    {
        $this->setNeedWhereColumn('where');
        $this->where = $where;
        return $this;
    }

    public function setWhereIn(array $where)
    {
        $this->setNeedWhereColumn('whereIn');
        $this->whereIn = $where;
        return $this;
    }

    public function setWhereLike(array $where)
    {
        $this->setNeedWhereColumn('whereLike');
        $this->whereLike = $where;
        return $this;
    }

    public function setWhereBetween(array $where)
    {
        $this->setNeedWhereColumn('whereBetween');
        $this->whereBetween = $where;
        return $this;
    }

    protected function setNeedWhereColumn($column)
    {
        array_push($this->needWhereColumns, $column);
    }

    protected function buildQuery($whereColumns, Closure $closure)
    {
        $columns = array_keys($this->request);
        foreach ($whereColumns as $column) {
            if (in_array($column, $columns)) {
                $closure($column);
            }
        }
    }

    protected function buildWhereLike()
    {
        $this->buildQuery($this->whereLike, function ($column) {
            $this->builder->where($column, 'like', '%'.$this->request[$column].'%');
        });
    }

    protected function buildWhere()
    {
        $this->buildQuery($this->where, function ($column) {
            $this->builder->where($column, $this->request[$column]);
        });
    }

    protected function buildWhereIn()
    {
        $this->buildQuery($this->whereIn, function ($column) {
            $this->builder->whereIn($column, $this->request[$column]);
        });
    }

    protected function buildWhereBetween()
    {
        foreach ($this->whereBetween as $column => $betweenColumns) {
            $startColumn = current($betweenColumns);
            $endColumn = next($betweenColumns);
            if ($this->whereBetweenIsVerifySuccess($column, $startColumn, $endColumn)) {
                $this->builder->whereBetween($column, [$this->request[$startColumn], $this->request[$endColumn]]);
            }
        }
    }

    private function whereBetweenIsVerifySuccess($column, $startColumn, $endColumn)
    {
        $checkIsInArray = function ($needle) {
            return in_array($needle, $this->whereBetween);
        };
        return ($checkIsInArray($column)
            && $checkIsInArray($startColumn)
            && $checkIsInArray($endColumn));
    }

    private function addSort()
    {
        if (isset($this->request['sort_by'])) {
            $sortList = explode(',', $this->request['sort_by']);
            foreach ($sortList as $sort) {
                $this->builder->orderBy(trim($sort, '+-'), getSortOrder($sort));
            }
        }
    }

    public function build(array $request, Builder $builder)
    {
        $this->request = $request;
        $this->builder = $builder;
        array_map(function ($column) {
            $funcName = 'build'.ucfirst($column);
            if (method_exists($this, $funcName)) {
                $this->$funcName();
            }
        }, $this->needWhereColumns);
        $this->addSort();
        return $this->builder;
    }
}
