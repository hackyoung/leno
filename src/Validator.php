<?php

namespace Leno;

class Validator extends \Leno\Validator\Type
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
        if(in_array($this->rules['type'], $array)) {
            return $this->checkArray($value);
        } else {
            return $this->checkSimple($value);
        }
    }

    protected function checkSimple($value)
    {
        if(isset($this->rules['custom'])) {
            try {
                return $this->rules['custom']($value, $this->rules);
            } catch(\Exception $ex) {
                if(isset($this->rules['onError'])) {
                    return $this->rules['onError']($value, $this->rules, $ex);
                }
                throw $ex;
            }
        }
        return $this->typeToCheck($value);
    }

    protected function checkArray($value)
    {
        if(!is_array($value)) {
            throw new \Exception($this->value_name . ' Not A Array');
        }
        if(isset($this->rules['allow_empty'])) {
            $this->setAllowEmpty($this->rules['allow_empty']);   
        }
        if(isset($this->rules['required'])) {
            $this->setRequired($this->rules['required']);
        }
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

    protected function typeToCheck($value)
    {
        extract($this->rules['extra'] ?? []);
        $Type = self::get($this->rules['type']);
        switch($this->rules['type']) {
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
        if(isset($this->rules['allow_empty'])) {
            $type->setAllowEmpty($this->rules['allow_empty']);   
        }
        if(isset($this->rules['required'])) {
            $type->setRequired($this->rules['required']);
        }
        try {
            return $type->setValueName($this->value_name)->check($value);
        } catch(\Exception $ex) {
            if(isset($this->rules['onError'])) {
                return $this->rules['onError']($value, $this->rules, $ex);
            }
            throw $ex;
        }
    }
}
