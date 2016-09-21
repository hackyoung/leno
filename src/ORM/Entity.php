<?php
namespace Leno\ORM;

use \Leno\Database\Row\Selector as RowSelector;
use \Leno\Database\Row\Deletor as RowDeletor;
use \Leno\Database\Row\Updator as RowUpdator;
use \Leno\Database\Row\Creator as RowCreator;

use \Leno\Database\Adapter;
use \Leno\ORM\Data;
use \Leno\ORM\Mapper;
use \Leno\Type;
use \Leno\ORM\EntityInterface;
use \Leno\ORM\EntityPool;

use \Leno\ORM\Exception\PrimaryMissingException;
use \Leno\ORM\Exception\EntityNotFoundException;
use \Leno\ORM\Exception\UniqueException;
use \Leno\ORM\Exception\ForeignException;
use \Leno\Exception\MethodNotFoundException;

/**
 * Entity时程序所用到的实体，这些实体有的可以进行持久化存储
 * 有点不可以(不需要)持久化存储, 比如user时需要持久化存储的
 * anonymous也是一个用户，但是它是不需要持久化存储的(当然也
 * 可以持久化存储,但是这样只会增加程序的复杂性).
 *
 * Entity具有定义好的属性，这些属性可以通过get，set，add方法
 * 操作对应的值，可以持久化存储的实体可以通过save/remove方法
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
class Entity implements \JsonSerializable, EntityInterface
{
    /**
     * 主键定义, 仅支持单主键 比如 user_id
     */
    public static $primary;

    /**
     * 表名, 标识该Entity对应存储的哪一张表
     */
    protected static $table;

    /**
     * 表属性定义, 该属性应该完整的定义表属性结构及类型约束
     * 其数据结构如下
     * [
     *      'field_name' => [
     *          'type' => (string),     // 类型
     *          'default' => (mixed),   // 默认值
     *          'is_nullable' => (bool),       // 是否允许为空
     *          'sensitive' => (bool)   // 该配置定义了属性是不是敏感属性, 敏感信息在toArray的时候会忽略且仅能通过setForcely方法设置其值
     *      ]
     * ]
     */
    protected static $attributes = [];

    /**
     * 唯一键定义，其格式如下
     * [
     *      'unique_key_name' => [field_1, field_2],
     * ]
     */
    protected static $unique;

    /**
     * 外键定义，该键不是关联到具体的表，而是关联到Entity, 格式如下
     * [
     *      'name' => [
     *          'entity' => EntityClass,
     *          'foreign_key' => 'entity_field_name',
     *          'local_key' => 'self_field_name',
     *      ],
     *      'name' => [
     *          'entity' => EntityClass,
     *          'local_key' => ['local_key_1', 'local_key_2'],
     *          'foreign_key' => [
     *              'local_key_1' => 'foreign_key_1',
     *              'local_key_2' => 'foreign_key_2'
     *          ]
     *      ],
     *      'name' => [
     *          'entity' => EntityClass,          
     *          'local_key' => 'self_field_name',
     *          'foreign_key' => 'entity_field_name'
     *          'bridge' => [
     *              'entity' => EntityClass,
     *              'local' => 'bridge_field_name',
     *              'foreign' => 'bridge_field_name'
     *          ]
     *      ],
     *      'name' => [
     *          'entity' => EntityClass,          
     *          'local_key' => 'self_field_name',
     *          'foreign_key' => 'entity_field_name'
     *          'is_array' => true
     *      ],
     *      'name' => [
     *          'entity' => EntityClass,
     *          'local_key' => ['local_key_1', 'local_key_2'],
     *          'foreign_key' => 'foreign_key',
     *          'bridge' => [
     *              'entity' => EntityClass,
     *              'local' => [
     *                  'local_key_1' => 'bridge_local_1',
     *                  'local_key_2' => 'bridge_local_2',
     *              ],
     *              'foreign' => 'foreign' 
     *          ]
     *      ]
     * ]
     */
    protected static $foreign;

    protected static $foreign_by = [];

    /**
     * 该Entity在数据库中有没有对应的存储记录，如果有，该字段为true
     */
    protected $fresh = false;

    /**
     * 构造函数，设置主键值，设置默认值,
     * 标记该Entity在数据库中时候有对应的存储存在
     *
     * @param bool dirty 该值为true则表示该Entity已经在数据库中有存储记录
     * 因此，save的时候做更新操作，反之，做插入操作
     */
    protected $relation_ship;

    protected $clone_relation_ship = false;

    public function __construct ($fresh = true)
    {
        $this->fresh = $fresh;
        $Entity = get_called_class();
        if(!isset($Entity::$primary)) {
            throw new PrimaryMissingException($Entity);
        }
        $this->data = new Data($Entity::getAttributes(), $Entity::$primary);
        $this->data->set($Entity::$primary, $Entity::createPrimaryValue());
        $this->relation_ship = new RelationShip(
            $Entity::getForeign(), $this, $Entity::getForeignBy()
        );
    }

    /**
     * clone一个Entity会将其关系，数据都复制成另一个Entity对象.注意，如果clone_relation_ship为true，则关系也会被复制一份出来，
     * 如果在clone之前其已经有Entity关系存在，如果对clone出来的Entity进行save，那么这些关系也会save进数据库，慎行
     */
    public function __clone()
    {
        $this->fresh = true;
        $Entity = get_called_class();
        $this->data = clone $this->data;
        $this->data->set($Entity::$primary, $Entity::createPrimaryValue());
        if ($this->clone_relation_ship) {
            $this->clone_relation_ship = false;
            $this->relation_ship = (clone $this->relation_ship)->setPrimaryEntity($this);
            return;
        }
        $this->relation_ship = new RelationShip(
            $Entity::getForeign(), $this, $Entity::getForeignBy()
        );
    }

    public function cloneAll($all = true)
    {
        $this->clone_relation_ship = $all;
        return $this;
    }

    /**
     * 魔术方法定义可以方便的使用set，get，add, inc, dec方法,该方法有一些性能开销，
     * 那么请直接使用 set，get，add, inc, dec方法
     *
     * ### example
     *
     * ```php
     * $this->setHelloWorld('hello world') === $this->set('hello_world', 'hello world')
     * $this->getHelloWorld() === $this->get('hello_world')
     * $this->addHelloWorld('hello world') === $this->add('hello_world', 'hello world')
     * ```
     */
    public function __call($method, array $args = [])
    {
        $series = array_filter(explode('_', unCamelCase($method, '_')));
        if(!isset($series[0])) {
            throw new MethodNotFoundException(get_called_class() . '::' . $method);
        }
        $type = array_splice($series, 0, 1)[0];
        array_unshift($args, implode('_', $series));
        $supports = ['set', 'get', 'add', 'inc', 'dec'];
        if (in_array($type, $supports)) {
            return call_user_func_array([$this, $type], $args);
        }
        throw new MethodNotFoundException(get_called_class() . '::' . $method);
    }

    public function __tostring()
    {
        return json_encode($this);
    }

    /**
     * save将Entity进行持久化存储，该方法有几个回调会调用
     *
     *  - beforeSave     任何保存操作都会调用该方法，如果该方法返回false，则终止保存操作，返回false
     *  - beforeInsert   向数据库中插入数据时，回调用该方法，如果该方法返回false，怎终止保存操作
     *  - beforeUpdate   向数据库中更新数据时，回调用该方法，如果该方法返回false，怎终止保存操作
     * 保存数据时，应该解决Entity之间的依赖关系
     *
     * @return self|false
     */
    public function save()
    {
        if($this->beforeSave() === false) {
            return false;
        }
        if ($this->insert() ?? $this->update()) {
            $this->dirty = true;
            $this->data->setAllDirty(false);
        }
        return $this;
    }

    /**
     * 将Entity从数据库中移除，类似于save，该方法有一个回调方法
     *  -beforeRemove       如果该方法返回false，则终止删除操作且返回false
     * 同样的，它也需要保证数据完整性
     */
    public function remove()
    {
        if($this->beforeRemove() === false) {
            return false;
        }
        $Entity = get_called_class();
        $mapper = (new Mapper())->selectTable($Entity::getTableName());
        return $mapper->remove($this->data);
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
    public function get (string $attr, $cached = true, callable $callback = null)
    {
        if ($this->data->hasAttr($attr)) {
            return $this->data->get($attr);
        }
        return $this->relation_ship->get($attr, $cached, $callback);
    }

    /**
     * 强制设置属性值，该方法会忽略sensitive，直接设置value
     */
    public function setForcely (string $attr, $value, bool $dirty = true) : EntityInterface
    {
        $this->data->setForcely($attr, $value, $dirty);
        return $this;
    }

    /**
     * 如果attr的属性sensitive为真，则表明这是个敏感属性
     * set会忽略它
     */
    public function set (string $attr, $value, bool $dirty = true) : EntityInterface
    {
        if ($this->data->hasAttr($attr)) {
            $this->data->set($attr, $value, $dirty);
            return $this;
        }
        $this->relation_ship->set($attr, $value);
        return $this;
    }

    /**
     * 设置多个属性
     */
    public function setMulti(array $multiple) : EntityInterface
    {
        array_walk($multiple, function($value, $attr) {
            $this->set($attr, $value);
        });
        return $this;
    }
    /**
     * 有两种类型的属性值可以通过add方法添加
     *
     *  1. 属性的类型为array类型的
     *  2. value为Entity类型，且与this是一对多关系
     */
    public function add (string $attr, $value) : EntityInterface
    {
        if ($this->data->hasAttr($attr)) {
            $this->data->add($attr, $value);
            return $this;
        }
        $this->relation_ship->add($attr, $value);
        return $this;
    }

    public function inc(string $attr, int $num = 1)
    {
        $this->data->set($attr, $this->data->get($attr) + $num);
        return $this;
    }

    public function dec(string $attr, int $num = 1)
    {
        $this->data->set($attr, $this->data->get($attr) - $num);
        return $this;
    }

    public function id()
    {
        $primary_pair = $this->data->id();
        foreach ($primary_pair as $id) {
            return $id;
        }
    }

    public function dirty () : bool
    {
        return $this->data->dirty();
    }

    public function toArray() : array
    {
        return $this->data->toArray();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * 返回一个绑定该Entity的selector
     */
    public static function selector()
    {
        $self = get_called_class();
        return (new RowSelector($self::getTableName()))->setEntityClass($self);
    }

    public static function updator()
    {
        $self = get_called_class();
        return (new RowUpdator($self::getTableName()))->setEntityClass($self);
    }

    public static function deletor()
    {
        $self = get_called_class();
        return (new RowDeletor($self::getTableName()))->setEntityClass($self);
    }

    public static function creator()
    {
        $self = get_called_class();
        return (new RowCreator($self::getTableName()))->setEntityClass($self);
    }

    /**
     * 通过id值查找Entity
     *
     * @return Entity|false
     */
    public static function find($id)
    {
        $self = get_called_class();
        $entity_pool = EntityPool::instance();
        $cache_key = $entity_pool->getKey($self::getTableName(), $id);
        if ($entity_pool->is($cache_key)) {
            return $entity_pool->get($cache_key);
        }
        $entity = (new Mapper)->selectTable($self::getTableName())->find([
            $self::$primary => $id
        ], $self);
        if ($entity) {
            $entity_pool->set($cache_key, $entity);
        }
        return $entity;
    }


    /**
     * 从数组中获取值，生成Entity, 该方法会假设row是从数据库查询
     * 的结果，请不要直接使用该方法，通过Entity::selector()->find()
     * 的方式，它会自动将row包装称Entity
     *
     * 有时候我们需要关联其他表获取某些字段，我们允许将这些字段附带到这个entity上面
     * 这样我们能够获取到更多的灵活性
     *
     * ###example
     *
     * ArticleEntity 拥有属性
     *  title content
     *
     * PublishedArticleEntity 拥有属性
     * atl_id like_num
     *
     *```php
     * $article = PublishedArticleEntity::find('xx')->getArticle(function($selector) {
     *      $published_selector = PublishedArticleEntity::Selector()
     *          ->feildLikeNum('like_number')               // 通过left join抓取like_num字段的值
     *          ->onAtlId($selector->getFeildExpr('atl_id'));
     *      return $selector->join($published_selector);
     * });
     *
     * $total_like_number = $article->getLikeNumber();      // 获取PublishedArticleEntity的属性值
     *```
     *
     * 这样我们可以通过标准的get方法取值，我们把这种通过left join抓取的值称之为伪属性
     *
     * @param array row 从数据库中查询出来的row信息
     *
     * @return Entity
     */
    public static function newFromDB(array $row)
    {
        $self = get_called_class();
        $entity = new $self(false);
        $attrs = $self::getAttributes();
        foreach($row as $field => $value) {
            if ($attrs[$field] ?? false) {
                $value = Type::get($attrs[$field]['type'])->toPHP($value);
            }
            $entity->setForcely($field, $value, false);
        }
        $entity_pool = EntityPool::instance();
        $key = $entity_pool->getKey($self::getTableName(), $entity->id());
        $entity_pool->set($key, $entity);
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

    public static function getForeignKeyName($attr)
    {
        $self = get_called_class();
        $foreigns = $self::getForeign();
        $table_name = $foreigns[$attr]['entity']::getTableName();
        return $self::$table . '_' . $attr . '_' . $table_name . '_fk';
    }

    protected function handleException(\Exception $e)
    {
        $self = get_called_class();
        logger()->err((string)$e);
        if (!($e instanceof \PDOException)) {
            throw $e;
        }
        $cks = $this->getKeyToConstraint();
        foreach ($cks as $key => $field) {
            if (strpos($key, $e->getMessage()) == -1) {
                continue;
            }
            if ($field['type'] == 'unique') {
                throw new UniqueException($self, $field['name']);
            }
            if ($field['type'] == 'foreign') {
                throw new ForeignException($self, $field['name']);
            }
        }
    }

    /**
     * 创建主键值
     */
    public static function createPrimaryValue()
    {
        return uuid();
    }

    /**
     * 以方法的形式获取属性，让子类可以重写这个方法
     */
    public static function getAttributes()
    {
        $self = get_called_class();
        return $self::$attributes ?? null;
    }

    public static function getTableName()
    {
        $self = get_called_class();
        return $self::$table ?? null;
    }

    public static function getUnique()
    {
        $self = get_called_class();
        return $self::$unique ?? null;
    }

    public static function getForeign()
    {
        $self = get_called_class();
        return $self::$foreign ?? null;
    }

    public static function getForeignBy()
    {
        $self = get_called_class();
        return $self::$foreign_by ?? null;
    }

    private function getKeyToConstraint()
    {
        $self = get_called_class();
        $unique = $self::getUnique() ?? [];
        $foreign = $self::getForeign() ?? [];
        $ret = [];
        foreach ($unique as $name => $field) {
            $ret[$name . '_' . $self::getTableName() . '_uk'] = [
                'type' => 'unique', 'name' => $field
            ];
        }
        foreach ($foreign as $name => $config) {
            $ret[$self::getForeignKeyName($name)] = $config['local_key'];
        }
        return $ret;
    }

    private function insert()
    {
        if (!$this->fresh) {
            return;
        }
        RowCreator::beginTransaction();
        try {
            if ($this->beforeInsert() === false) {
                RowCreator::rollback();
                return false;
            }
            $this->relation_ship->save();
            $Entity = get_called_class();
            (new Mapper())->selectTable($Entity::getTableName())->insert($this->data);
            $this->relation_ship->saveBridge();
            RowCreator::commitTransaction();
        } catch (\Exception $ex) {
            RowCreator::rollback();
            $this->handleException($ex);
            return false;
        }
        return true;
    }

    private function update()
    {
        RowUpdator::beginTransaction();
        try {
            if ($this->beforeUpdate() === false) {
                RowUpdator::rollback();
                return false;
            }
            $this->relation_ship->save();
            $Entity = get_called_class();
            (new Mapper())->selectTable($Entity::getTableName())->update($this->data);
            $this->relation_ship->saveBridge();
            RowUpdator::commitTransaction();
        } catch (\Exception $ex) {
            RowUpdator::rollback();
            $this->handleException($e);
            return false;
        }
        return true;
    }

    protected function beforeSave() { }

    protected function beforeInsert() { }

    protected function beforeUpdate() { }

    protected function beforeRemove() { }
}
