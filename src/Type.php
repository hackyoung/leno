<?php
namespace Leno;

use \Leno\Type\Exception\TypeMissingException;
use \Leno\Type\Exception\ValueNotAllowEmptyException;
use \Leno\Type\Exception\ValueRequiredException;
use \Leno\Type\TypeCheckInterface;

abstract class Type implements TypeCheckInterface
{
    public static $types = [
        'int'       =>    '\Leno\Type\IntegerType',
        'integer'   =>    '\Leno\Type\IntegerType',
        'number'    =>    '\Leno\Type\NumberType',
        'enum'      =>    '\Leno\Type\EnumType',
        'string'    =>    '\Leno\Type\StringType',
        'uuid'      =>    '\Leno\Type\UuidType',
        'uri'       =>    '\Leno\Type\UriType',
        'url'       =>    '\Leno\Type\UrlType',
        'ip'        =>    '\Leno\Type\Ipv4Type',
        'ipv4'      =>    '\Leno\Type\Ipv4Type',
        'email'     =>    '\Leno\Type\EmailType',
        'phone'     =>    '\Leno\Type\PhoneType',
        'datetime'  =>    '\Leno\Type\DatetimeType',
        'json'      =>    '\Leno\Type\JsonType',
    ];

    protected $allow_empty = false;

    protected $required = true;

    protected $extra = [];

    protected $value_name = 'Value';

    public function __construct($required = true, $allow_empty = false)
    {
        $this->required = $required;
        $this->allow_empty = $allow_empty;
    }

    public function check($val) {
        if($val === null) {
            if($this->required) {
                throw new ValueRequiredException($this->value_name, $val);
            }
            return false;
        }
        if(($val === '' || $val === [])) {
            if(!$this->allow_empty) {
                throw new ValueNotAllowEmptyException($this->value_name, $val);
            }
            return false;
        }
        return $this->_check($val);
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

    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    public static function get($idx)
    {
        if(!isset(self::$types[$idx])) {
            throw new TypeMissingException($idx);
        }
        return self::$types[$idx];
    }

    public static function register($idx, $class)
    {
        self::$types[$idx] = $class;
    }

    abstract protected function _check($value) : bool;
}
