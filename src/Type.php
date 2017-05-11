<?php
namespace Leno;

use \Leno\Type\Exception\TypeMissingException;
use \Leno\Type\Exception\ValueNotAllowEmptyException;
use \Leno\Type\Exception\ValueRequiredException;
use \Leno\Type\TypeCheckInterface;

abstract class Type implements TypeCheckInterface
{
    protected static $adapter = 'mysql';

    protected static $types = [
        'mysql' => [
            'array' => '\\Leno\\Type\\Mysql\\ArrayType',
            'datetime' => '\\Leno\\Type\\Mysql\\DatetimeType',
            'uuid' => '\\Leno\\Type\\Mysql\\UuidType',
            'int' => '\\Leno\\Type\\Mysql\\IntegerType',
            'integer' => '\\Leno\\Type\\Mysql\\IntegerType',
            'number' => '\\Leno\\Type\\Mysql\\NumberType',
            'bool' => '\\Leno\\Type\\Mysql\\BoolType',
            'boolean' => '\\Leno\\Type\\Mysql\\BoolType',
            'blob' => '\\Leno\\Type\Mysql\\BlobType'
        ],
        'pgsql' => [
            'array' => '\\Leno\\Type\\Pgsql\\ArrayType',
            'datetime' => '\\Leno\\Type\\Pgsql\\DatetimeType',
            'uuid' => '\\Leno\\Type\\Pgsql\\UuidType',
            'int' => '\\Leno\\Type\\Pgsql\\IntegerType',
            'integer' => '\\Leno\\Type\\Pgsql\\IntegerType',
            'number' => '\\Leno\\Type\\Pgsql\\NumberType'
        ],
        'enum' => '\\Leno\\Type\\EnumType',
        'string' => '\\Leno\\Type\\StringType',
        'uri' => '\\Leno\\Type\\UriType',
        'url' => '\\Leno\\Type\\UrlType',
        'ip' => '\\Leno\\Type\\Ipv4Type',
        'ipv4' => '\\Leno\\Type\\Ipv4Type',
        'email' => '\\Leno\\Type\\EmailType',
        'phone' => '\\Leno\Type\PhoneType',
        'json' => '\\Leno\\Type\\JsonType',
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

    public function check($val) : bool
    {
        if ($val === null) {
            if ($this->required) {
                throw new ValueRequiredException($this->value_name, $val);
            }
            return false;
        }
        if (($val === '' || $val === [])) {
            if (!$this->allow_empty) {
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
        $type = self::$types[self::$adapter][$idx] ?? self::$types[$idx] ?? null;
        if ($type == null) {
            throw new TypeMissingException($idx, $idx);
        }
        return new $type;
    }

    public static function register($type, $class, $adapter = null)
    {
        if ($adapter !== null) {
            self::$types[$adapter][$type] = $class;
        }
        self::$types[$type] = $class;
    }

    abstract protected function _check($value) : bool;
}
