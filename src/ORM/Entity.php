<?php
namespace Leno\ORM;

use \Leno\Database\Selector as RowSelector;
use \Leno\Database\Row;
use \Leno\Database\Adapter;
use \Leno\ORM\Data;
use \Leno\ORM\Mapper;

use \Leno\ORM\Exception\PrimaryMissingException;
use \Leno\ORM\Exception\EntityNotFoundException;
use \Leno\Exception\MethodNotFoundException;

class Entity implements \JsonSerializable
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
     * 主键定义, 仅支持单主键 比如 user_id
     */
    public static $primary;

    /**
     * 唯一键定义，其格式如下
     * [
     *      'unique_key_name' => [field_1, field_2],
     * ]
     */
    public static $unique;

    /**
     * 外键定义，该键不是关联到具体的表，而是关联到Entity, 格式如下
     * [
     *      'name' => [
     *          'entity' => EntityClass,
     *          'foreign' => 'entity_field_name',
     *          'local' => 'self_field_name'
     *      ]
     * ]
     */
    public static $foreign;

    /**
     * 索引定义
     */
    public static $index;

    /**
     * 该Entity在数据库中有没有对应的存储记录，如果有，该字段为true
     */
    protected $dirty;

    /**
     * 保存Entity的属性值, 其格式如下
     * [
     *      'maybe_field' => [
     *          'dirty' => bool,
     *          'value' => mixed | [
     *              mixed
     *          ]
     *      ]
     * ]
     */
    protected $values = [];

    public function __construct ($dirty = false)
    {
        $Entity = get_called_class();
        if(!isset($Entity::$primary)) {
            throw new PrimaryMissingException($Entity);
        }
        $this->dirty = $dirty;
        if($this->getAttribute($Entity::$primary)['type'] == 'uuid') {
            $this->set($Entity::$primary, uuid());
        }
        foreach($Entity::$attributes as $field => $attr) {
            if(($attr['default'] ?? null) === null) {
                continue;
            }
            if($attr['sensitive'] ?? false) {
                $this->setForcely($field, $attr['default']);
                continue;
            }
            $this->set($field, $attr['default']);
        }
    }

    public function __call($method, $args = null)
    {
        $series = array_filter(explode('_', unCamelCase($method, '_')));
        if(!isset($series[0])) {
            throw new MethodNotFoundException(get_called_class() . '::' . $method);
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
        throw new MethodNotFoundException(get_called_class() . '::' . $method);
    }

    public function save()
    {
        if($this->beforeSave() === false) {
            return false;
        }
        $Entity = get_called_class();
        $mapper = (new Mapper())->selectTable($Entity::$table);
        Row::beginTransaction();
        try {
            $data = $this->getDataWithSave();
            if (!$this->dirty()) {
                $this->beforeInsert() !== false && $mapper->insert($data);
            } else {
                $this->beforeUpdate() !== false && $mapper->update($data);
            }
            Row::commitTransaction();
        } catch(\Exception $e) {
            Row::rollback();
            throw $e;
        }
        return $this->id();
    }

    public function remove()
    {
        if($this->beforeRemove() === false) {
            return false;
        }
        $Entity = get_called_class();
        $mapper = (new Mapper())->selectTable($Entity::$table);
        return $mapper->remove($this->getDataWithRemove());
    }

    public function get (string $attr)
    {
        $self = get_called_class();
        if(!isset($self::$foreign[$attr])) {
            return $this->values[$attr]['value'] ?? null;
        }
        $Entity = $self::$foreign[$attr]['entity'];
        $selector = $Entity::selector()->registerEntity($Entity);
        $type = $Entity::$attributes[$self::$primary]['type'];
        $field = $selector->getFieldExpr($self::$foreign[$attr]['foreign']);
        if($type == 'array') {
            $entitys = $selector->byExpr(
                $Type::get('array')->in($this->id(), $field)
            )->find();
            $this->set($self::$foreign[$attr]['local'], $entitys);
            return $entitys;
        }
        $entitys = $selector->by('eq', $field, $this->id())->find();
        $this->set($self::$foreign[$attr]['local'], $entitys);
        return $entitys;
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

    public function id()
    {
        $self = get_called_class();
        return $this->get($self::$primary);
    }

    public function dirty (string $attr = null) : bool
    {
        if ($attr) {
            return $this->values[$attr]['dirty'] ?? false;
        }
        return $this->dirty;
    }

    public function toArray()
    {
        $entity_array = [];
        foreach($this->values as $key => $value_info) {
            $entity_array[$key] = $value_info['value'];
        }
        return $entity_array;
    }

    protected function beforeSave()
    {
    }

    protected function beforeInsert()
    {
    }

    protected function beforeUpdate()
    {
    }

    protected function reforeRemove()
    {
    }

    /**
     * TODO right implement
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    private function getDataWithSave()
    {
        $self = get_called_class();
        $values = [];
        foreach($this->values as $field => $value) {
            if($value['value'] instanceof $self) {
                $value['value'] = $value['value']->save();
            }
            if(is_array($value['value'])) {
                $new_value_list = [];
                foreach($value['value'] as $each_value) {
                    if($each_value instanceof $self) {
                        $new_value_list[] = $each_value->save();
                    }
                }
                $value['value'] = $new_value_list;
            }
            $values[$field] = $value;
        }
        return new Data($values, $self::$attributes, $self::$primary);
    }

    private function getDataWithRemove()
    {
        // TODO 解决依赖关系
        return new Data($value, $Entity::$attributes, $Entity::$primary);
    }

    private function getAttribute(string $attr)
    {
        $Entity = get_called_class();
        return $Entity::$attributes[$attr] ?? null;
    }

    private function pSet(string $attr, $value, bool $dirty)
    {
        $self = get_called_class(); 
        if($value instanceof $self) {
            $Entity = self::getEntityByField($attr);
            if(!$Entity || !($value instanceof $Entity)) {
                throw new \Leno\Exception ('value type error');
            }
        }
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
        $self = get_called_class(); 
        if($this->getAttribute($attr)['type'] == 'array') {
            throw new \Leno\Exception ('You Attempt Add Value To None Array Type : '.$attr);
        }
        if($value instanceof $self) {
            $Entity = self::getEntityByField($attr);
            if(!$Entity || !($value instanceof $Entity)) {
                throw new \Leno\Exception ('value type error');
            }
        }
        if(!isset($this->values[$attr])) {
            $this->values[$attr] = [
                'dirty' => true,
                'value' => [ $value ] 
            ];
            return $this;
        }
        $exists_value = $this->values[$attr]['value'];
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

    public static function find($id)
    {
        $Entity = get_called_class();
        return (new Mapper)->selectTable($Entity::$table)->find([
            $Entity::$primary => $id
        ], $Entity);
    }

    public static function newFromDB(array $row)
    {
        $Entity = get_called_class();
        $entity = new $Entity(true);
        foreach($Entity::$attributes as $field => $attr) {
            $value = $row[$field] ?? $attr['default'] ?? null;
            if($attr['sensitive'] ?? false) {
                $entity->setForcely($field, $value, false);
                continue;
            }
            $entity->set($field, $value, false);
        }
        return $entity;
    }

    public static function findOrFail($id)
    {
        $entity = self::find($id);
        if(!$entity) {
            throw new EntityNotFoundException(get_called_class(), $id);
        }
        return $entity;
    }

    public static function getEntityByField(string $field)
    {
        $Entity = get_called_class();
        foreach($Entity::$foreign as $foreign) {
            if($foreign['local'] != $field) {
                continue;
            }
            return $foreign['entity'];
        }
    }
}
