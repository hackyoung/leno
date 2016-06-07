<?php

namespace Leno\Core;

class Validator extends \Leno\Core\Type
{
    /**
     * @var [
     *      'type' => '',
     *      'allow_empty' => true,
     *      'required' => true,
     *      'onError' => callback,
     *      'custom' => callback,
     *      'extra' => [],
     * ]
     */
    protected $rules;

    public function __construct($rules, $value_name=null)
    {
        $this->rules = $rules;
        if($value_name !== null) {
            $this->setValueName($value_name);
        }
    }

    public function check($value)
    {
        $array = ['array', 'json'];
        $type = $this->rules['type'] ?? false;
        if(!$type) {
            throw new \Exception('Rule Error: Type Not Found');
        } else if(is_array($type)) {
            foreach($type as $t) {
                $this->rules['type'] = $t;
                try {
                    $this->checkSimple($value);
                } catch(\Exception $ex) {
                    continue;
                }
                break;
            }
            if(isset($ex) && $ex instanceof \Exception) {
                throw $ex;
            }
            return true;
        } elseif(in_array($type, $array)) {
            $value = is_string($value) ? json_decode($value, true) : $value;
            return $this->checkArray($value);
        } else {
            return $this->checkSimple($value);
        }
    }

    protected function checkSimple($value)
    {
        if(isset($this->rules['custom'])) {
            try {
                return call_user_func_array($this->rules['custom'], $this->rules);
            } catch(\Exception $ex) {
                if(isset($this->rules['onError'])) {
                    return call_user_func_array(
                        $this->rules['onError'], $value, $this->rules, $ex
                    );
                }
                throw $ex;
            }
        }
        return $this->typeToCheck($value, $this->rules);
    }

    protected function checkArray($value)
    {
        if(!is_array($value)) {
            throw new \Exception($this->value_name . ' Not A Array');
        }
        $this->setAllowEmpty($this->rules['allow_empty'] ?? null);   
        $this->setRequired($this->rules['required'] ?? null);
        if(!parent::check($value)) {
            return true;
        }
        foreach($value as $key => $val) {
            if(isset($this->rules['__each__'])) {
                (new \Leno\Validator($this->rules['__each__'], $key))->check($val);
            }
            if(isset($this->rules['extra']) && isset($this->rules['extra'][$key])) {
                (new \Leno\Validator($this->rules['extra'][$key], $key))->check($val);
            }
        }
        return true;
    }

    protected function typeToCheck($value, $rule)
    {
        extract($rule['extra'] ?? []);
        $Type = self::get($rule['type']);
        switch($rule['type']) {
            case 'int':
            case 'integer':
            case 'number':
                $type = new $Type($min ?? null, $max ?? null);
                break;
            case 'string':
                $type = new $Type(
                    $regexp ?? null, 
                    $min_length ?? null,
                    $max_length ?? null
                );
                break;
            case 'enum':
                $type = new $Type($enum_list ?? []);
                break;
            default:
                $type = new $Type;
        }
        $type->setAllowEmpty($rule['allow_empty'] ?? null);
        $type->setRequired($rule['required'] ?? null);
        try {
            return $type->setValueName($this->value_name)->check($value);
        } catch(\Exception $ex) {
            if(isset($rule['onError'])) {
                return call_user_func_array($rule['onError'], $value, $rule, $ex);
            }
            throw $ex;
        }
    }
}
