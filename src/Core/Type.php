<?php
namespace Leno\Core;

abstract class Type
{
    public static $types = [
        'int' => '\Leno\Core\Type\IntNumber',
        'integer' => '\Leno\Core\Type\IntNumber',
        'number' => '\Leno\Core\Type\Number',
        'enum' => '\Leno\Core\Type\Enum',
        'string' => '\Leno\Core\Type\Stringl',
        'uuid' => '\Leno\Core\Type\Uuid',
        'uri' => '\Leno\Core\Type\Uri',
        'url' => '\Leno\Core\Type\Url',
        'ip' => '\Leno\Core\Type\Ipv4',
        'ipv4' => '\Leno\Core\Type\Ipv4',
        'email' => '\Leno\Core\Type\Email',
        'phone' => '\Leno\Core\Type\Phone',
        'datetime' => '\Leno\Core\Type\Datetime',
        'json' => '\Leno\Core\Type\Json',
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
