<?php
namespace Leno\ORM;

class Mapper implements \JsonSerializable
{
    /**
     * [
     *      'name' => ['type' => 'string', 'extra' => ['max_length' => 2015]],
     *      'age' => ['type' => 'integer', 'allow_empty' => true,],
     * ];
     */
    public static $attributes = [];

    /**
     * [
     *      'id', 'name'
     * ]
     */
    public static $unique = [];

    public static $primary;

    /**
     * [
     *        methodName: [
     *            relation: [],
     *            class:
     *        ]
     * ]
     */
    public static $foreign =[];

    /**
     * 存储在哪张表中
     */
    public static $table;

    /**
     * 数据是否在数据库中已经有记录
     */
    protected $fresh = true;

    /**
     * [
     *        city : [obj]
     * ]
     */
    protected $relation = [];

    protected $data;

    public function __construct($data = [])
    {
        $class = get_called_class();
        $this->data = new Data($data, $class::$attributes);
        $primary = self::getPrimary();
        if(is_string($primary)) {
            $primary = [$primary];
        }
        foreach($primary as $primary_key) {
            if(!$this->data->isset($primary_key) && self::getAttribute($primary_key) === 'uuid') {
                $this->data->set($primary_key, uuid());
            }
        }
    }

    public function __call($method, $parameters = null) {
        $series = array_filter(explode('_', unCamelCase($method, '_')));
        if(!isset($series[0])) {
            throw new \Exception(get_class() .'::'.$method . ' Not Defined');
        }
        $type = $series[0];
        array_splice($series, 0, 1);
        $field = implode('_', $series);
        switch($type) {
            case 'set':
                return $this->set($field, $parameters[0]);
            case 'get':
                return $this->get($field);
            case 'add':
                return $this->add($field, $parameters[0]);
        }
        throw new \Exception(get_class() .'::'.$method . ' Not Defined');
    }

    public function get($key)
    {
        $data = $this->data;
        if($data->isset($key)) {
            return $data->get($key);
        }
        return $this->getRelateObjs($key);
    }

    public function set($key, $val)
    {
        $foreign = self::getForeign($key);
        if($foreign) {
            $this->relation[$key] = [];
            if(is_array($val)) {
                foreach($val as $val) {
                    $this->add($key, $val);
                }
            } else {
                $this->add($key, $val);
            }
            return $this;
        }
        $this->data->set($key, $val);
        return $this;
    }

    public function setAll(array $data)
    {
        foreach($data as $field => $value) {
            $this->data->set($field, $value);
        }
        return $this;
    }

    /**
     * 添加一个关系实体, 如$user->addBook($book);
     * @param string key 
     * @param mapper val
     * @return this
     */
    public function add($key, $val)
    {
        if(!($foreign = self::getForeign($key))) {
            throw new \Exception('Method Not Allow');
        }
        if(!$val instanceof $foreign['class']) {
            throw new \Exception('Method Not Allow');
        }
        if(!isset($this->relation[$key])) {
            $this->relation[$key] = [];
        }
        $this->relation[$key][] = $val;
        return $this;
    }

    public function isFresh()
    {
        return $this->fresh;
    }

    public function setFresh($fresh = true)
    {
        $this->fresh = $fresh;
        return $this;
    }

    public function id()
    {
        return $this->get(self::getPrimary());
    }

    /**
     * 将一个实体持久化存储,会保存其有关系的其他实体信息
     */
    public function save()
    {
        \Leno\ORM\Row::beginTransaction();
        try {
            if($this->beforeSave() === false) {
                return false;
            }
            foreach($this->relation as $key => $obj) {
                $this->saveRelateObjs($key, $obj);
            }
            if(!$this->isFresh()) {
                $this->update();
                return \Leno\ORM\Row::commitTransaction();
            }
            $this->create();
            return \Leno\ORM\Row::commitTransaction();
        } catch(\Exception $e) {
            \Leno\ORM\Row::rollback();
            throw $e;
        }
    }

    protected function update()
    {
        if($this->beforeUpdate() === false || !$this->data->validateAll()) {
            return false;
        }
        $updator = self::updator();
        $primary = self::getPrimary();
        $updator->by('eq', $primary, $this->data->get($primary));
        $this->data->each(function($key, $data) use ($updator){
            if($data->isDirty($key)) {
                $updator->set($key, $data->forStore($key));
            }
        });
        $updator->update();
    }

    protected function create()
    {
        if($this->beforeInsert() === false || !$this->data->validateAll()) {
            return false;
        }
        $creator = self::creator();
        $this->data->each(function($key, $data) use ($creator) {
            $creator->set($key, $data->forStore($key));
        });
        $creator->create();
    }

    /**
     * 在该实体被保存前，该方法会保存与该实体有关系的其他实体
     * @param string key 关联的外键
     * @param [] objs 一系列通过key关联的对象
     */
    protected function saveRelateObjs($key, $objs)
    {
        $foreign = self::getForeign($key);
        if(!$foreign) {
            return;
        }
        $primaryVal = $this->id();
        foreach($objs as $obj) {
            if(!$obj instanceof $foreign['class']) {
                continue;
            }
            $obj->save();
            if(!$foreign['next'] ?? true) {
                $this->data->set($foreign['foreign'], $obj->id());
                continue;
            }
            $next = $foreign['next'];
            if($next['foreign'] !== self::getPrimary()) {
                throw new \Leno\Exception ('Foreign Relation Define Error: '.$key);
            }
            $relationClass = $next['class'];
            (new $next['class'])->set($foreign['foreign'], $obj->id())
                ->set($next['local'], $primaryVal)
                ->save();
        }
    }

    /**
     * 获取该mapper对象关联的mapper对象，依赖于maper配置的foreign属性
     */
    protected function getRelateObjs($key) 
    {
        $foreign = self::getForeign($key);
        if(!$foreign) {
            return;
        }
        $theClass = $foreign['class'];
        $selector = $theClass::selector();
        if($foreign['next'] ?? false) {
            $next = $foreign['next'];
            $class = $next['class'];
            $joinSelector = $class::selector()->field(false)
                ->on('eq', $foreign['foreign'], $selector->getFieldExpr($foreign['local']))
                ->by('eq', $next['local'], $this->get($next['foreign']));
            $selector->join($joinSelector);
        } else {
            $selector->by('eq', $foreign['local'], $this->get($foreign['foreign']));
        }
        $ret = $selector->find();
        if(count($ret) === 1) {
            return $ret[0];
        }
        return $ret;
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

    /**
     * 获取mapper的唯一键信息
     */
    public static function getUnique()
    {
        $class = get_called_class();
        return $class::$unique ?? [];
    }

    /**
     * 获取mapper的主键信息
     */
    public static function getPrimary()
    {
        $class = get_called_class();
        return $class::$primary ?? null;
    }

    /**
     * 获取一个mapper的外键关联信息
     */
    public static function getForeign($key = null)
    {
        $class = get_called_class();
        if($key) {
            return $class::$foreign ? ($class::$foreign[$key] ?? null) : null;
        }
        return $class::$foreign ?? null;
    }

    /**
     * 获取一个mapper的具体字段的属性
     */
    public static function getAttribute($key, $inner = 'type')
    {
        if($key  === null) {
            return null;
        }
        $attributes = self::getAttributes();
        return $attributes[$key] ? ($attributes[$key][$inner] ?? null) : null;
    }

    /**
     * 获得一个mapper的属性(行信息)
     */
    public static function getAttributes()
    {
        $class = get_called_class();
        return $class::$attributes ?? [];
    }

    public static function find($pk)
    {
        $selector = self::selector();
        if(is_array($pk)) {
            foreach($the as $field => $value) {
                $selector->by('eq', $field, $value);
            }
        } else {
            $selector->by('eq', self::getPrimary(), $pk);
        }
        return $selector->findOne();
    }

    /**
     * 通过唯一键查找一个mapper实例,没有找到则抛异常
     * @param string|array pk 唯一键
     * @return Mapper
     */
    public static function findOrFail($pk)
    {
        $entity = self::find($pk);
        if(!$entity instanceof self) {
            throw new \Exception('Entity Not Found');
        }
        return $entity;
    }

    /**
     * 返回一个关联到该mapper的行删除器
     */
    public static function deletor()
    {
        $class = get_called_class();
        return \Leno\ORM\Row::deletor($class::$table)
            ->setMapper($class);
    }

    /**
     * 返回一个关联到该mapper的行更新器
     */
    public static function updator()
    {
        $class = get_called_class();
        return \Leno\ORM\Row::updator($class::$table)
            ->setMapper($class);
    }

    /**
     * 返回一个关联到该mapper的行创建器
     */
    public static function creator()
    {
        $class = get_called_class();
        return \Leno\ORM\Row::creator($class::$table)
            ->setMapper($class);
    }

    /**
     * 返回一个关联到该mapper的行选择器
     */
    public static function selector()
    {
        $class = get_called_class();
        return \Leno\ORM\Row::selector($class::$table)
            ->setMapper($class);
    }

    public function jsonSerialize()
    {
        return $this->data->jsonSerialize();
    }

    public function getData()
    {
        return $this->data;
    }
}
