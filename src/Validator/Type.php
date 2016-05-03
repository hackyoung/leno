<?php
namespace Leno\Validator;

abstract class Type
{
    public static $types = [
        'int' => '\Leno\Validator\Type\IntNumber',
        'integer' => '\Leno\Validator\Type\IntNumber',
        'number' => '\Leno\Validator\Type\Number',
        'enum' => '\Leno\Validator\Type\Enum',
        'string' => '\Leno\Validator\Type\Stringl',
        'uuid' => '\Leno\Validator\Type\Uuid',
        'uri' => '\Leno\Validator\Type\Uri',
        'url' => '\Leno\Validator\Type\Url',
        'ip' => '\Leno\Validator\Type\Ipv4',
        'ipv4' => '\Leno\Validator\Type\Ipv4',
        'email' => '\Leno\Validator\Type\Email',
        'phone' => '\Leno\Validator\Type\Phone',
        'datetime' => '\Leno\Validator\Type\Datetime',
        'json' => '\Leno\Validator\Type\Json',
    ];

    protected $allow_empty = false;

    protected $required = true;

    protected $value_name = 'Value';

    public function check($val) {
        if($val === null) {
            if($this->required) {
                throw new \Exception($this->value_name . ' Required');
            }
            return false;
        }
        if(($val === '' || $val === [])) {
            if(!$this->allow_empty) {
                throw new \Exception($this->value_name .' Not Allow Empty');
            }
            return false;
        }
        return true;
    }

    public function setAllowEmpty($allow)
    {
        $this->allow_empty = $allow;
        return $this;
    }

    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    public function setValueName($value_name)
    {
        $this->value_name = $value_name;
        return $this;
    }

    public static function get($idx)
    {
        if(!isset(self::$types[$idx])) {
            throw new \Exception('Type ' . $idx . ' Not Surpported');
        }
        return self::$types[$idx];
    }

    public static function register($idx, $class)
    {
        self::$types[$idx] = $class;
    }
}
