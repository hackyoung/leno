<?php
namespace Leno\ORM;

use \Leno\Database\Row\Selector as RowSelector;
use \Leno\Database\Row;
use \Leno\Database\Adapter;
use \Leno\ORM\Data;
use \Leno\ORM\Mapper;

use \Leno\ORM\Exception\PrimaryMissingException;
use \Leno\Exception\MethodNotFoundException;

/**
 * Entity时程序所用到的实体，这些实体有的可以进行持久化存储
 * 有点不可以(不需要)持久化存储, 比如user时需要持久化存储的
 * anonymous也是一个用户，但是它是不需要持久化存储的(当然也
 * 可以持久化存储,但是这样只会增加程序的复杂性).
 *
 * Entity具有定义好的属性，这些属性可以通过get，set，add方法
 * 操作对应的值，可以持久化存储的实体可以通过save/remove方法
 * 对起存储操作
 *
 * Entity之间有关系，所有的Entity操作都会考虑其和另外的Entity
 * 的依赖关系
 *
 * ### example
 * 定义一个User实体，该实体有id，name属性，user_id为其primary
 * ```php
 * class User extends Entity
 * {
 *      public static $attrbutes = [
 *          'user_id' => ['type' => 'uuid'],
 *          'name' => ['type' => 'string', 'extra' => [
 *              'max_length' => 64
 *          ]]
 *      ];
 *
 *      public static $primary = 'user_id';
 * }
 * ```
 * 
 * 定义一个Book实体，该实体有id，name和author_id, book_id为其primay
 * 定义了一个外键依赖关系author
 * ```php
 * class Book extends Entity
 * {
 *      public static $attributes = [
 *          'book_id' => ['type' => 'uuid'],
 *          'name' => ['type' => 'string', 'extra' => [
 *              'max_length' => 128
 *          ]],
 *          'author_id' => ['type' => 'uuid']
 *      ];
 *
 *      public static $primary = 'book_id';
 *
 *      public static $foreign = [
 *          'author' => [
 *              'entity' => 'User',
 *              'local_key' => 'author_id',
 *              'foreign_key' => 'user_id'
 *          ]
 *      ]
 * }
 * ```
 * 生成两个实体对象
 *
 * ```php
 * $user = (new User)->setName('young');
 * $book = new Book;
 * ```
 * 我们没有定义一个author的属性，但是我们在foreign中有一个外部关系定义 author
 * set方法会发现，然后设置author，并且在保存时，先保存user，然后取出user_id放在author_id
 * 字段, 然后保存book
 * ```php
 * $book->setAuthor($user);
 * ```
 * 该方法同set, 且，如果get发现有author的关系定义，且values中没有author，则get会去数据库
 * 取值，放在values中，然后返回给用户
 * ```php
 * var_dump($book->getAuthor());
 * ```
 */
class Entity implements \JsonSerializable
{
    /**
     * 表名, 标识该Entity对应存储的哪一张表
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
     *          'sensitive' => (bool)   // 该配置定义了属性是不是敏感属性, 敏感信息在toArray的时候会忽略且仅能通过setForcely方法设置其值
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
     *          'foreign_key' => 'entity_field_name',
     *          'local_key' => 'self_field_name'
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

    /**
     * 构造函数，设置主键值，设置默认值,
     * 标记该Entity在数据库中时候有对应的存储存在
     *
     * @param bool dirty 该值为true则表示该Entity已经在数据库中有存储记录
     * 因此，save的时候做更新操作，反之，做插入操作
     */
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

    /**
     * 魔术方法定义可以方便的使用set，get，add方法,该方法有一些性能开销，那么请直接使用
     * set，get，add方法
     *
     * ### example
     * ```php
     * $this->setHelloWorld('hello world') === $this->set('hello_world', 'hello world')
     * $this->getHelloWorld() === $this->get('hello_world')
     * $this->addHelloWorld('hello world') === $this->add('hello_world', 'hello world')
     * ```
     */
    public function __call($method, $args = null)
    {
        $self = get_called_class();
        $series = array_filter(explode('_', unCamelCase($method, '_')));
        if(!isset($series[0])) {
            throw new MethodNotFoundException($self . '::' . $method);
        }
        $type = $series[0];
        array_splice($series, 0, 1);
        $attr = implode('_', $series);
        switch($type) {
            case 'set':
                return $this->set ($attr, $args[0]);
            case 'get':
                $all_attr = array_merge(
                    array_keys($self::$foreign), array_keys($this->values)
                );
                if(in_array($attr, $all_attr)) {
                    return $this->get ($attr);
                }
                break;
            case 'add':
                return $this->add ($attr, $args[0]);
        }
        throw new MethodNotFoundException($self . '::' . $method);
    }

    /**
     * save将Entity进行持久化存储，该方法有几个回调会调用
     *  - beforeSave        任何保存操作都会调用该方法，如果该方法返回false，则终止保存操作，返回false
     *  - beforeInsert      向数据库中插入数据时，回调用该方法，如果该方法返回false，怎终止保存操作
     *  - beforeUpdate      向数据库中更新数据时，回调用该方法，如果该方法返回false，怎终止保存操作
     * 保存数据时，应该解决Entity之间的依赖关系
     *
     * @see self::getDataWithSave
     * 
     * @return id|false
     */
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

    /**
     * 将Entity从数据库中移除，类似于save，该方法有一个回调方法
     *  -beforeRemove       如果该方法返回false，则终止删除操作且返回false
     * 同样的，它也需要保证数据完整性
     *
     * @see self::getDataWithRemove
     */
    public function remove()
    {
        if($this->beforeRemove() === false) {
            return false;
        }
        $Entity = get_called_class();
        $mapper = (new Mapper())->selectTable($Entity::$table);
        return $mapper->remove($this->getDataWithRemove());
    }

    /**
     * 获取属性值，该方法不仅仅获取你设置的Entity本身的值
     * ### example
     * ```php
     * // 生成一个User的实体
     * $user = new User;
     *
     * // 获取user的名字
     * $user->getName() == $user->get('name');
     * ```
     *
     * 如果我们在User上定义了依赖关系，我们可以get其依赖的对象
     * ```php
     * // 这个User依赖Address, 通过address_id关联Address
     * $user = new User;
     *
     * // 返回address的Entity而不是其ID
     * $user->getAddress() == $user->get('address');
     *
     * // 下面的会直接返回其address_id
     * $user->getAddressId() == $user->get('address_id');
     * ```
     */
    public function get (string $attr)
    {
        $self = get_called_class();
        if(!isset($self::$foreign[$attr])) {
            return $this->values[$attr]['value'] ?? null;
        }
        $Entity = $self::getEntityByField($attr);
        $foreign_selector = $Entity::selector()->registerEntity($Entity);
        $current_value = $this->values[$attr]['value'];
        if ($current_value) {
            return $current_value;
        }
        $foreign_key = $self::$foreign[$attr]['foreign_key'];
        $field = $foreign_selector->getFieldExpr($foreign_key);
        $value = $this->get($local_key);
        $entity = $foreign_selector->by('eq', $field, $value)->findOne();
        $this->set($attr, $entity);
        return $entity;
    }

    /**
     * 强制设置属性值，该方法会忽略sentitive，直接设置value
     */
    public function setForcely (string $attr, $value, bool $dirty = true)
    {
        return $this->_set($attr, $value, $dirty);
    }

    /**
     * 如果attr的属性sensitive为真，则表明这是个敏感属性
     * set会忽略它
     */
    public function set (string $attr, $value, bool $dirty = true)
    {
        $config = $this->getAttribute($attr);
        if($config && ($config['sensitive'] ?? false)) {
            return $this;
        }
        return $this->_set($attr, $value, $dirty);
    }

    public function add (string $attr, $value)
    {
        $config = $this->getAttribute($attr);
        if(!$config || ($config['sensitive'] ?? false)) {
            return $this;
        }
        return $this->_add($attr, $value);
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
            $config = $this->getAttribute($key);
            if($config && !($config['sensitive'] ?? false)) {
                $entity_array[$key] = $value_info['value'];
            }
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

    protected function beforeRemove()
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
        $values = [];
        $self = get_called_class();
        foreach($this->values as $field => $value) {
            if($value['value'] instanceof self) {
                $value['value'] = $value['value']->save();
                continue;
            }
            if(is_array($value['value'])) {
                $new_value_list = [];
                foreach($value['value'] as $each_value) {
                    if($each_value instanceof self) {
                        $new_value_list[] = $each_value->save();
                    }
                }
                $Entity = get_called_class();
                $foreign = $Entity::$foreign;

                // 如果是通过数组字段进行表关联的，则需要保存其值
                if(!isset($foreign[$field])) {
                    $value['value'] = $new_value_list;
                }
                continue;
            }
            $values[$field] = $value;
        }
        return new Data($values, $self::$attributes, $self::$primary);
    }

    private function getDataWithRemove()
    {
        $Entity = get_called_class();
        // TODO 解决依赖关系
        return new Data($this->values, $Entity::$attributes, $Entity::$primary);
    }

    private function getAttribute(string $attr)
    {
        $Entity = get_called_class();
        return $Entity::$attributes[$attr] ?? null;
    }

    private function _set(string $attr, $value, bool $dirty)
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

    private function _add(string $attr, $value)
    {
        if($value instanceof self) {
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
        if(!is_array($exists_value)) {
            $exists_value = [ $exists_value ];
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

    /**
     * 返回一个绑定该Entity的selector
     */
    public static function selector ()
    {
        $self = get_called_class();
        return (new RowSelector($self::$table))->selectEntity($self);
    }

    /**
     * 通过id值查找Entity
     *
     * @return Entity|false
     */
    public static function find($id)
    {
        $Entity = get_called_class();
        return (new Mapper)->selectTable($Entity::$table)->find([
            $Entity::$primary => $id
        ], $Entity);
    }

    /**
     * 从数组中获取值，生成Entity, 该方法会假设row是从数据库查询
     * 的结果，请不要直接使用该方法，通过Entity::selector()->find()
     * 的方式，它会自动将row包装称Entity
     *
     * @param array row 从数据库中查询出来的row信息
     *
     * @return Entity
     */
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

    /**
     * 通过ID找到对应的Entity，如果没找到，则抛出异常，在某些如果找不到entity
     * 则视为其出错的情况下使用该方法，反正使用Entity::find,该方法不会抛出异常
     *
     * @param mixed id Entity的id
     *
     * @return Entity
     */
    public static function findOrFail($id)
    {
        $entity = self::find($id);
        if(!$entity) {
            throw new EntityNotFoundException(get_called_class(), $id);
        }
        return $entity;
    }

    public static function getEntityByField(string $attr)
    {
        $Entity = get_called_class();
        foreach($Entity::$foreign as $foreign) {
            if($foreign['local_key'] != $attr) {
                continue;
            }
            return $foreign['entity'];
        }
    }
}
