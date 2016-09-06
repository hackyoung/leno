<?php
namespace Leno\ORM;

use \Leno\ORM\DataInterface;
use \Leno\Type;
use \Leno\ORM\Exception\FieldException;

/**
 * Data是一个数据集，通过mapper，可以将它持久化存储，所有数据在写入Data的时候会通过
 * 规则验证其数据完整性,其验证操作依赖Validator
 */
class Data implements DataInterface
{
    /**
     *
     * 保存写入Data的值，其结构为 [
     *  'key' => ['value' => '', 'dirty' => '',],
     * ]
     *
     */
    protected $data = [];

    /**
     *
     * 保存Data的配置信息，这些信息用于参数验证，其结构为 [
     *      'value' => [
     *          'type' => '',
     *          'is_nullable' => '', 
     *          'default' => '',
     *          'sensitive' => '',
     *          'extra' => []
     *      ],
     * ];
     *
     * 见Validator
     *
     */
    protected $config = [];

    /**
     * 保存该Data的主键
     */
    protected $primary;

    /**
     * 构造函数
     *
     * @param array data 当传入该参数
     * @param array config 如果传递该参数
     *
     */
    public function __construct(array $config, string $primary)
    {
        $this->config = $config;
        $this->primary = $primary;
        foreach ($this->config as $field => $attr) {
            if (($attr['default'] ?? null) === null) {
                continue;
            }
            $this->setForcely($field, $attr['default']);
        }
    }

    public function __clone()
    {
        $this->setAllDirty();
    }

    /**
     * 验证值是否合法
     *
     * @return bool
     */
    public function validate() : bool
    {
        foreach($this->config as $field => $config) {
            $type = Type::get($config['type'])
                    ->setRequired(!($config['is_nullable'] ?? false))
                    ->setValueName($field)
                    ->setAllowEmpty(false)
                    ->setExtra($config['extra'] ?? []);
            $value = $this->data[$field]['value'] ?? null;
            try {
                $type->check($value);
            } catch (\Exception $ex) {
                throw new FieldException($field, $type->toDB($value));
            }
        }
        return true;
    }

    public function getDirty() : array
    {
        $this->validate();
        $dirty_data = [];
        foreach($this->data as $field => $value_info) {
            $attr = $this->config[$field] ?? false;
            if(!$attr || !$value_info['dirty']) {
                continue;
            }
            $type = Type::get($attr['type']);
            $dirty_data[$field] = $type->toDB($value_info['value']);
        }
        return $dirty_data;
    }

    public function id() : array
    {
        $value = $this->data[$this->primary]['value'] ?? null;
        return [$this->primary => $value];
    }

    public function get (string $attr)
    {
        return $this->data[$attr]['value'] ?? null;
    }

    public function setForcely (string $attr, $value, bool $dirty = true)
    {
        $this->data[$attr] = [
            'dirty' => $dirty,
            'value' => ($value === '' ? null : $value)
        ];
        return $this;
    }

    public function set (string $attr, $value, bool $dirty = true)
    {
        $config = $this->config[$attr] ?? null;
        if (!$config || ($config['sensitive'] ?? false)) {
            return $this;
        }
        return $this->setForcely($attr, $value, $dirty);
    }

    public function add (string $attr, $value)
    {
        if(!isset($this->data[$attr])) {
            $this->data[$attr] = [
                'dirty' => true,
                'value' => [ $value ] 
            ];
            return $this;
        }
        $exists_value = $this->data[$attr]['value'];
        if(!is_array($exists_value)) {
            $exists_value = [ $exists_value ];
        }
        if(!in_array($value, $exists_value)) {
            $exists_value[] = $value;
            $this->data[$attr] = [
                'dirty' => true,
                'value' => $exists_value
            ];
        }
        return $this;
    }

    public function dirty()
    {
        foreach($this->data as $data) {
            if($data['dirty']) {
                return true;
            }
        }
        return false;
    }

    public function toArray()
    {
        $ret = [];
        foreach($this->data as $field => $data) {
            $attr = $this->config[$field];
            $type = Type::get($attr['type']);
            if ($attr['sensitive'] ?? false) {
                continue;
            }
            $ret[$field] = $type->toDB($data['value']);
        }
        return $ret;
    }

    public function setAllDirty($dirty = true)
    {
        foreach ($this->data as $field => $data) {
            $this->data[$field]['dirty'] = $dirty;
        }
        return $this;
    }

    public function hasAttr($attr)
    {
        return isset($this->config[$attr]);
    }
}
