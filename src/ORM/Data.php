<?php
namespace Leno\ORM;

use \Leno\ORM\DataInterface;
use \Leno\Type;

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
     *          'null' => '', 
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
    }

    /**
     * 验证值是否合法
     *
     * @return bool
     */
    public function validate() : bool
    {
        foreach($this->config as $field => $config) {
            $type = Type::get($config['type']);
            $type->setRequried($config['null'] ?? true)
                ->setAllowEmpty(true)
                ->setExtra($config['extra'])
                ->check($this->data[$field]['value'] ?? null);
        }
        return true;
    }

    public function getDirty() : array
    {
        $dirty_data = [];
        foreach($this->data as $field => $value_info) {
            if(!$value_info['dirty']) {
                continue;
            }
            $attr = $this->config[$field];
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

    public function get(string $attr)
    {
        return $this->data[$attr]['value'] ?? null;
    }

    public function set(string $attr, $value, bool $dirty)
    {
        $exists_value = $this->data[$attr]['value'] ?? null;
        if($value == $exists_value) {
            return $this;
        }
        $this->data[$attr] = [
            'dirty' => $dirty,
            'value' => $value
        ];
        return $this;
    }

    public function add(string $attr, $value)
    {
        if(!isset($this->data[$attr])) {
            $this->data[$attr] = [
                'dirty' => true,
                'value' => [ $value ] 
            ];
            return $this;
        }
        $exists_value = $this->values[$attr]['value'];
        if(!is_array($exists_value)) {
            $exists_value = [ $exists_value ];
        }
        if(!in_array($value, $exists_value)) {
            $this->data[$attr] = [
                'dirty' => true,
                'value' => $exists_value + $value
            ];
            return $this;
        }
        return $this;
    }
}
