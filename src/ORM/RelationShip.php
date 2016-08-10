<?php
namespace Leno\ORM;

use \Leno\Database\Row\Selector as RowSelector;

/**
 * 多个Entity之间的关系描述, 所有的关系都是在Entity本身通过foreign和foreign_by属性来描述的，这个类充当helper完成关系间的get和set
 *
 * 一对一
 * table_1|          |table_2
 * -------|          |-------
 *   key_1|----------|key_2
 *
 * 一对多
 * table_1|          |table_2
 * -------|          |-------          
 *        | ---------|key_2
 *   key_1|/_|-------|key_2
 *        |\         |
 *        | ---------|key_2
 *
 * 多对多
 * table_1|    |table_3    |    |table_2
 * -------|    |-----|-----|    |-------
 *   key_1|----|key_1|key_2|----| key_2 
 * -------| |  |-----|-----|  | |-------
 *   key_1|----|key_1|key_2|----| key_2
 * -------|    |-----|-----|    |-------
 *
 * 对于多对多的关系，则体现在config中的bridge, 它充当桥的作用将两个entity的关系表示出来
 */
class RelationShip
{
    /**
     * format = [
     *      'hello' => [
     *          'local_key' => '',
     *          'foreign_key' => '',
     *          'entity' => ''
     *      ],
     *      'world' => [
     *          'local_key' => [
     *              'world_key_1',
     *              'world_key_2'
     *          ],
     *          'foreign_key' => [
     *              'world_key_1' => 'entity_key_1',
     *              'world_key_2' => 'entity_key_2',
     *          ],
     *          'entity' => ''
     *      ]
     * ];
     */
    protected $config;

    /**
     * 通常一对多的关系，表结构
     *
     * p_table |           |  f_table
     * --------|           |---------
     *         | ----------| f_k
     *     p_k |/_|--------| f_k
     *         |\          |
     *           ----------| f_k
     *
     *
     * p_table 被映射成P_Entity, f_table 被映射成F_Entity
     *
     * 我们在F_Entity上通过foreign的配置来表明其关系
     *
     * $foreign = [
     *      'p' => [
     *          'entity' => 'P_Entity',
     *          'local_key' => 'f_k',
     *          'foreign_key' => 'p_k'
     *      ]
     * ];
     *
     * 这样我们就可以通过 F_Entity::getP()方法获取P_Entity
     *
     * 但是在P_Entity这边，则没有foreign的配置，因为这种关系
     * 在F_Entity已经描述清楚了。
     *
     * 在P_Entity有foreign_by的配置，它表明被哪些其他Entity引用了
     *
     * $foreign_by = [
     *      'f' => [
     *          'entity' => 'F_Entity',
     *          'attr' => 'p'
     *      ]
     * ];
     *
     * 这样我们就可以通过P_Entity::getF()来查询到所有F_Entity了
     */        
    protected $foreign_by = [];

    /**
     * 保存primary entity
     */
    protected $primary_entity;

    /**
     * 保存secondary entity
     * [
     *      'a' => entityObj,
     *      'b' => [entityObj],
     *      'c' ...
     * ]
     */
    protected $secondary_entities = [];

    /**
     * 对于多对多的关系，需要bridge来关联两个entity，这个变量保存关联两个entity的entity
     */
    protected $bridge_entities = [];

    public function __construct($config, &$primary_entity, array $foreign_by = [])
    {
        $this->config = $config;
        $this->primary_entity = $primary_entity;
        $this->foreign_by = $foreign_by;
    }

    /**
     * 通过primary_entity和配置查找secondary_entities
     *
     * @param string attr 配置名
     * @param callable\bool cached 
     * @param callbale callback
     *
     * @return array
     */
    public function get (string $attr, $cached = true, $callback = null)
    {
        if (is_callable($cached)) {
            $callback = $cached;
            $cached = true;
        }
        if ($cached && ($this->secondary_entities[$attr] ?? false)) {
            return $this->secondary_entities[$attr];
        }
        $foreign = $this->config[$attr] ?? false;
        if (!$foreign) {
            $foreign_by = $this->getForeiginBy($attr);
            $ferc = new \ReflectionClass($foreign_by['entity']);
            $this->secondary_entities[$attr] = $this->getBy($ferc, $foreign_by['attr'], $callback);
            return $this->secondary_entities[$attr];
        }
        if (!isset($foreign['bridge'])) {
            $this->secondary_entities[$attr] = $this->getNoBridge($foreign, $callback);
            return $this->secondary_entities[$attr];
        }
        $this->secondary_entities[$attr] = $this->getBridge($foreign, $callback);
        return $this->secondary_entities[$attr];
    }

    public function setPrimaryEntity(&$primary_entity)
    {
        $this->primary_entity = $primary_entity;
        return $this;
    }

    /**
     * 通过config和primary_entity设置secondary_entities
     */
    public function set (string $attr, $value)
    {
        $config = $this->config[$attr] ?? false;
        if (!$config) {
            $foreign_by = $this->getForeiginBy($attr);
            if (!($value instanceof $foreign_by['entity'])) {
                throw new \Leno\Exception ('value is not a '.$foreign_by['entity']);
            }
            $ferc = new \ReflectionClass($foreign_by['entity']);
            $config = $ferc->getStaticPropertyValue('foreign')[$foreign_by['attr']] ?? false;
            if (!$config) {
                throw new \Leno\Exception ($attr.'\'s config of '.$foreign_by['entity']. ' not found');
            }
            if (is_array($config['local_key'])) {
                foreach ($config['local_key'] as $key) {
                    $value->set($key, $this->primary_entity->get($config['foreign_key'][$key]));
                }
            } else {
                $value->set($config['local_key'], $this->primary_entity->get($config['foreign_key']));
            }
        } elseif (!($value instanceof $config['entity'])) {
            throw new \Leno\Exception ('value is not a instance of '.$config['entity']);
        } elseif ($config['is_array'] ?? false) {
            foreach ($value as $v) {
                $this->primary_entity->add($config['local_key'], $v->get($config['foreign_key']));
            }
        } else {
            $value_key = $value->get($config['foreign_key']);
            $this->primary_entity->set($config['local_key'], $value_key);
        }
        $this->secondary_entities[$attr] = $value;
        return $this;
    }

    public function add (string $attr, $value)
    {
        $config = $this->config[$attr] ?? false;
        if (!$config) {
            $foreign_by = $this->getForeiginBy($attr);
            if (!($value instanceof $foreign_by['entity'])) {
                throw new \Leno\Exception ('value is not a instance of '.$foreign_by['entity']);
            }
            $ferc = new \ReflectionClass($foreign_by['entity']);
            $config = $ferc->getStaticPropertyValue('foreign')[$foreign_by['attr']] ?? false;
            if (!$config) {
                throw new \Leno\Exception ($attr.'\'s config of '.$foreign_by['entity']. ' not found');
            }
            if (is_array($config['local_key'])) {
                foreach ($config['local_key'] as $key) {
                    $value->set($key, $this->primary_entity->get($config['foreign_key'][$key]));
                }
            } else {
                $value->set($config['local_key'], $this->primary_entity->get($config['foreign_key']));
            }
        } elseif (!($value instanceof $config['entity'])) {
            throw new \Leno\Exception ('value is not a Entity');
        } elseif ($config['is_array'] ?? false) {
            $this->primary_entity->add($config['local_key'], $value->get($config['foreign_key']));
        } else {
            $value->set($config['foreign_key'], $this->primary_entity->get($config['local_key']));
        }
        $exists = $this->secondary_entities[$attr] ?? false;
        if (!$exists) {
            $this->secondary_entities[$attr] = $value;
            return $this;
        }
        if (is_array($exists)) {
            $this->secondary_entities[$attr][] = $value; 
            return $this;
        }
        $this->secondary_entities[$attr] = [$exists, $value];
        return $this;
    }

    public function save (array $entities = null)
    {
        if ($entities === null) {
            $entities = $this->secondary_entities;
        }
        foreach ($entities as $attr=>$entity) {
            if (!is_array ($entity)) {
                $this->saveEntity($attr, $entity);
                continue;
            }
            foreach ($entity as $ett) {
                $this->saveEntity($attr, $ett);
            }
        }
        return $this;
    }

    public function saveBridge()
    {
        foreach ($this->bridge_entities as $entity) {
            // TODO 如果抛出约束不满足的异常，忽略，写日志
            if ($entity->dirty()) {
                $entity->save();
            }
        }
        return $this;
    }

    private function getBy ($ferc, $attr, $callback = null)
    {
        $foreign = $ferc->getStaticPropertyValue('foreign');
        $config = $foreign[$attr] ?? false;
        if (!$config) {
            throw new \Leno\Exception ($attr.'\'s config not found');
        }
        $selector = $ferc->getMethod('selector')->invoke(null);
        $selector->by(
            RowSelector::EXP_EQ, $config['local_key'],
            $this->primary_entity->get($config['foreign_key'])
        );
        if (is_callable($callback)) {
            $selector = call_user_func($callback, $selector);
        }
        if ($selector instanceof RowSelector) {
            return $selector->find();
        }
        return $selector;
    }

    private function getNoBridge ($config, $callback)
    {
        $selector = $config['entity']::selector();
        if (is_array($config['local_key'])) {
            foreach ($config['local_key'] as $local) {
                $selector->by(
                    RowSelector::EXP_EQ, $config['foreign_key'][$local],
                    $this->primary_entity->get($local)
                );
            }
        } else {
            $value = $this->primary_entity->get($config['local_key']);
            $expr = RowSelector::EXP_EQ;
            if (is_array($value)) {
                $expr = RowSelector::EXP_IN;
            }
            $selector->by($expr, $config['foreign_key'], $value);
        }
        if (is_callable($callback)) {
            $selector = call_user_func($callback, $selector);
        }
        if (!($selector instanceof RowSelector)) {
            return $selector;
        }
        return $selector->find();
    }

    private function getBridge ($config, $callback)
    {
        $selector = $config['entity']::selector();
        $bridge = $config['bridge'];
        $bridge_selector = $bridge['entity']::selector();
        if (is_array($config['local_key'])) {
            foreach ($config['local_key'] as $local) {
                $bridge_selector->by(
                    RowSelector::EXP_EQ, $bridge['local'][$local],
                    $this->primary_entity->get($local)
                );
            }
        } else {
            $bridge_selector->by(
                RowSelector::EXP_EQ, $bridge['local'],
                $this->primary_entity->get($config['local_key'])
            );
        }
        if (is_array($config['foreign_key'])) {
            foreach ($config['foreign_key'] as $foreign) {
                $bridge_selector->on(
                    RowSelector::EXP_EQ, $bridge['foreign'][$foreign],
                    $selector->getFieldExpr($foreign)
                );
            }
        } else {
            $bridge_selector->on(
                RowSelector::EXP_EQ, $bridge['foreign'],
                $selector->getFieldExpr($config['foreign_key'])
            );
        }
        $selector = $selector->join($bridge_selector);
        if (is_callable($callback)) {
            $selector = call_user_func($callback, $selector);
        }
        if ($selector instanceof RowSelector) {
            return $selector->find();
        }
        return $selector;
    }

    private function saveEntity (string $attr, Entity $entity)
    {
        if ($entity->dirty()) {
            $entity->save();
        }
        $config = $this->config[$attr] ?? [];
        if ((!isset($config['bridge']))) {
            return;
        }
        $bridge = new $config['bridge']['entity'];
        $bridge->set(
            $config['bridge']['local'],
            $this->primary_entity->get($config['local_key'])
        );
        $bridge->set(
            $config['bridge']['foreign'],
            $entity->get($config['foreign_key'])
        );
        $this->bridge_entities[] = $bridge;
    }

    private function getForeiginBy (string $attr)
    {
        $foreign_by = $this->foreign_by[$attr] ?? false;
        if (!$foreign_by) {
            throw new \Leno\Exception ($attr.'\'s foreign_by not found');
        }
        return $foreign_by;
    }
}
