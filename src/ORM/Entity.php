<?php
namespace Leno\ORM;

use \Leno\Database\Row\Selector as RowSelector;
use \Leno\Database\Row\Deletor as RowDeletor;
use \Leno\Database\Row\Updator as RowUpdator;
use \Leno\Database\Row\Creator as RowCreator;
use \Leno\Database\Row;
use \Leno\Database\Adapter;
use \Leno\ORM\Data;
use \Leno\ORM\Mapper;
use \Leno\ORM\Exception\PrimaryMissingException;

class Entity
{
    /**
     * 表名
     */
    public static $table;

    /**
     * 表属性定义, 该属性应该完整的定义表属性结构及类型约束
     * 其数据结构如下
     * [
     *      'field_name' => [
     *          'type' => (string),     // 类型
     *          'default' => (mixed),   // 默认值
     *          'null' => (bool),       // 是否允许为空
     *          'extra' => [],          // 类型验证时需要的字段,
     *          'sensitive' => (bool)   // 该配置定义了属性是不是敏感属性, 敏感信息在toArray的时候会忽略
     *      ]
     * ]
     */
    public static $attributes = [];

    /**
     * 主键定义，如果是单主键，则该变量是个字符串 比如user
     * 如果是多主键，则是个数组不如 [user_id, book_id]
     */
    public static $primary;

    /**
     * 唯一键定义，其格式如下
     * [
     *      'unique_key_name' => [field, field],
     * ]
     */
    public static $unique;

    /**
     * 外键定义，该键不是关联到具体的表，而是关联到Entity, 格式如下
     * [
     *      'field_name' => EntityClass,
     * ]
     */
    public static $foreign;

    protected $dirty;

    protected $values = [];

    public function __construct ($dirty = false)
    {
        $Entity = get_called_class();
        $this->dirty = $dirty;
        if(!is_array($Entity::$primary) && $this->getAttribute($primary)['type'] == 'uuid') {
            $this->set($primary, uuid());
        }
        foreach($Entity::$primary as $primary) {
            if($this->getAttribute($primary)['type'] == 'uuid') {
                $this->set($primary, uuid());
            }
        }
        throw new PrimaryMissingException;
    }

    public function save()
    {
        $mapper = new Mapper();
        Row::beginTransaction();
        try {
            $data = $this->getData();
            if($this->dirty()) {
                $mapper->insert($data);
            } else {
                $by = $this->id();
                if(!is_array($by)) {
                    $by = [$Entity::$primary => $by];
                }
                $mapper->by($by)->update($data);
            }
            Row::commitTransaction();
        } catch(\Exception $e) {
            Row::rollback();
            throw $e;
        }
        return $this->id();
    }

    public function __call($method, $args = null)
    {
        $series = array_filter(explode('_', unCamelCase($method, '_')));
        if(!isset($series[0])) {
            throw new \Exception(get_class() .'::'.$method.' Not Defined');
        }
        $type = $series[0];
        array_splice($series, 0, 1);
        $attr = implode('_', $series);
        switch($type) {
            case 'set':
                return $this->set ($attr, $args[0]);
            case 'get':
                return $this->get ($attr);
            case 'add':
                return $this->add ($attr, $args[0]);
        }
        if ($type == 'dirty') {
            return $this->dirty ($args[0]);
        }
        throw new \Exception(get_called_class() .'::'.$method . ' Not Defined');
    }

    public function get (string $attr)
    {
        return $this->$values[$attr]['value'] ?? null;
    }

    public function setForcely (string $attr, $value, bool $dirty = true)
    {
        $config = $this->getAttribute($attr);
        if(!$config) {
            return $this;
        }
        return $this->pSet($attr, $value, $dirty);
    }

    public function set (string $attr, $value, bool $dirty = true)
    {
        $config = $this->getAttribute($attr);
        if(!$config || ($config['sensitive'] ?? false)) {
            return $this;
        }
        return $this->pSet($attr, $value, $dirty);
    }

    public function add (string $attr, $value)
    {
        $config = $this->getAttribute($attr);
        if(!$config || ($config['sensitive'] ?? false)) {
            return $this;
        }
        return $this->pAdd($attr, $value);
    }

    public function dirty (string $attr = null) : bool
    {
        if ($attr) {
            return $this->values[$attr]['dirty'] ?? false;
        }
        return $this->dirty;
    }

    private function getAttribute(string $attr)
    {
        $Entity = get_called_class();
        return $Entity::$attributes[$attr] ?? null;
    }

    private function pSet(string $attr, $value, bool $dirty)
    {
        $exists_value = $this->values[$attr]['value'] ?? null;
        if($value == $exists_value) {
            return $this;
        }
        $this->values[$attr] = [
            'dirty' => $dirty,
            'value' => $value
        ];
        return $this;
    }

    private function pAdd(string $attr, $value)
    {
        if(!isset($this->values[$attr])) {
            $this->values[$attr] = [
                'dirty' => true,
                'value' => [ $value ] 
            ];
            return $this;
        }
        $exists_value = $this->values[$attr]['value'];
        if(!is_array($exists_value) && $exists_value !== $value) {
            $this->values[$attr] = [
                'dirty' => true,
                'value' => [$exists_value, $value]
            ];
            return $this;
        }
        if(!in_array($value, $exists_value)) {
            $this->values[$attr] = [
                'dirty' => true,
                'value' => $exists_value + $value
            ];
            return $this;
        }
        return $this;
    }

    public static function selector ()
    {
        $class = get_called_class();
        return new RowSelector($class::$table);
    }
}
