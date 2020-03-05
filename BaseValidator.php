<?php

namespace App\Validators;

use App\Exceptions\BaseException;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseValidator
{

    protected $rules = [];
    protected $messages = [];
    protected $scene = [];
    protected $needRules = [];          // 用以存储将被应用于执行的rules信息，默认为$this->>rules
    protected $customAttributes = [];
    protected $request;
    protected $isExtendRule = true;            // 用以判断是否开启拓展验证
    protected $currentScene;

    abstract protected function setRules();
    protected function setMessages()
    {

    }

    protected function setScene()
    {

    }
    protected function setCustomAttributes()
    {

    }

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
        $this->setMessages();
        $this->setRules();
        $this->setCommonRules();
        $this->setScene();
        $this->setCustomAttributes();
        $this->needRules = $this->rules;
        $this->customValidate();
    }

    public function extendRuleOpen()
    {
        $this->isExtendRule = true;
        return $this;
    }

    public function extendRuleClose()
    {
        $this->isExtendRule = false;
        return $this;
    }

    protected function customValidate()
    {
    }

    private function setCommonRules()
    {
        $this->rules['page'] = 'min:1|integer';
        $this->rules['page_size'] = 'min:1|integer';
    }

    protected function init()
    {
    }

    public function scene($scene)
    {
        $this->checkScene($scene);
        $this->setNeedRulesByScene($scene);
        $this->currentScene = $scene;
        return $this;
    }

    protected function setNeedRulesByScene($scene)
    {
        $tempRules = [];
        collect($this->scene[$scene])->map(function ($v, $k) use (&$tempRules) {
            $isDict = !is_numeric($k);
            if ($isDict) {
                $tempRules[$k] = $v;
            } else {
                $tempRules[$v] = $this->rules[$v];
            }
        });
        $this->needRules = $tempRules;
    }

    protected function checkScene($scene)
    {
        if (!collect($this->scene)->has($scene)) {
            throw new BaseException('场景值不合法');
        }
    }

    public function check(array $data = [])
    {
        if (empty($this->needRules)) {
            $this->needRules = $this->rules;
        }
        $data = empty($data) ? $this->request->all() : $data;
        $validator = Validator::make($data, $this->needRules, $this->messages, $this->customAttributes);
        if ($validator->fails()) {
            $msg = $validator->errors()->toArray();
            $res = response()->json($msg)
                             ->setStatusCode(HTTP_UNPROCESSABLE_ENTITY);
            
            throw new ValidationException($validator, $res);
        }
        if ($this->isExtendRule) {
            $this->extendRules();
        }
    }

    protected function subStrRequired(Array $except = [])
    {
        $tempScene = $this->rules;
        foreach ($tempScene as $k => &$v) {
            if (!is_array($v)) {
                $tempScene[$k] = in_array($k, $except) ? $v : str_replace('required|', '', $v);
                continue;
            }
            if (!in_array($k, $except)) {
                unset($v[array_search('required', $v)]);
            }
            $tempScene[$k] = $v;
        }
        return $tempScene;
    }

    protected function extendRules()
    {
    }
}
