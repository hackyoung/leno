<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface;
use \Leno\Type\Exception\ValueNotArrayException;

abstract class ArrayType extends \Leno\Type implements TypeStorageInterface
{
    /**
     * [
     *      'type' => '',
     *      'allow_empty' => true,
     *      'required' => true,
     *      'extra' => [
     *          '__each__' => ['type' => 'int']
     *      ]
     * ]
     */
    protected $config;

    public function setExtra ($extra)
    {
        $this->config = $extra;
        return $this;
    }

    protected function _check($value) : bool
    {
        if(!is_array($value)) {
            throw new ValueNotArrayException($this->value_name, $value);
        }
        $keys_config = $this->config['extra'] ?? [];
        if(isset($keys_config['__each__'])) {
            foreach($value as $key => $each_value) {
                $value_name = $this->value_name . '.' . $key;
                $this->checkValue($each_value, $keys_config['__each__'], $value_name);
            }
        }
        unset($keys_config['__each__']);
        foreach($keys_config as $key => $config) {
            $value_name = $this->value_name . '.' . $key;
            $this->checkValue(($value[$key] ?? null), $config, $value_name);
        }
        return true;
    }

    public function toDbType() : string
    {
        return $this->_toType();
    }

    public function toPHP($value)
    {
        if($value === null) {
            return null;
        }
        return $this->_toPHP($value);
    }

    public function toDB($value)
    {
        if($value === null) {
            return null;
        }
        return $this->_toDB($value);
    }

    private function checkValue($value, $config, $value_name=null)
    {
        if($value_name === null) {
            $value_name = $this->value_name;
        }
        $type = $this->getType($config, $value_name);
        return $type->check($value);
    }

    private function getType($config, $value_name)
    {
        return self::get($config['type'])
            ->setExtra($config['extra'])
            ->setValueName($value_name)
            ->setAllowEmpty(($config['allow_empty'] ?? false))
            ->setRequired(($config['required'] ?? true));
    }

    abstract protected function _toType();

    abstract protected function _toPHP($value);

    abstract protected function _toDB($value);
}
