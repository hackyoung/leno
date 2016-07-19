<?php
namespace Leno\ORM;

use \Leno\Database\Row\Selector as RowSelector;

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

    protected $primary_entity;

    protected $secondary_entities = [];

    protected $bridge_entities = [];

    public function __construct($config, &$primary_entity)
    {
        $this->config = $config;
        $this->primary_entity = $primary_entity;
    }

    public function get (string $attr, $cached = true, $callback = null)
    {
        if (is_callable($cached)) {
            $callback = $cached;
            $cached = true;
        }
        if ($cached && $this->secondary_entities[$attr] ?? false) {
            return $this->secondary_entities[$attr];
        }
        $foreign = $this->config[$attr] ?? false;
        if (!$foreign) {
            throw new \Leno\Exception ($attr.'\'s config not found');
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

    public function set (string $attr, $value)
    {
        $config = $this->config[$attr] ?? false;
        if (!$config) {
            throw new \Leno\Exception ($attr.'\'s config not found');
        }
        if (!($value instanceof $config['entity'])) {
            throw new \Leno\Exception ('value is not a Entity');
        }
        $this->secondary_entities[$attr] = $value;
        $value_key = $value->get($config['foreign_key']);
        $this->primary_entity->set($config['local_key'], $value_key);
        return $this;
    }

    public function add (string $attr, $value)
    {
        $config = $this->config[$attr] ?? false;
        if (!$config || !($value instanceof $config['entity'])) {
            throw new \Leno\Exception ($attr.'\'s config not found');
        }
        if (!($value instanceof $config['entity'])) {
            throw new \Leno\Exception ('value is not a Entity');
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

    public function save(array $entities = null)
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

    private function getNoBridge($config, $callback)
    {
        $selector = $config['entity']::selector();
        if (is_array($config['local_key'])) {
            foreach ($config['local_key'] as $local) {
                $selector->by(
                    RowSelector::EXP_EQ,
                    $config['foreign_key'][$local],
                    $this->primary_entity->get($local)
                );
            }
        } else {
            $selector->by(
                RowSelector::EXP_EQ,
                $config['foreign_key'],
                $this->primary_entity->get($config['local_key'])
            );
        }
        if (is_callable($callback)) {
            $selector = call_user_func($callback, $selector);
        }
        if (!($selector instanceof RowSelector)) {
            return $selector;
        }
        $result = $selector->find();
        if (count($result) == 1) {
            return $result[0];
        }
        return $result;
    }

    private function getBridge($config, $callback)
    {
        $selector = $config['entity']::selector();
        $bridge = $config['bridge'];

        $bridge_selector = $bridge['entity']::selector();

        if (is_array($config['local_key'])) {
            foreach ($config['local_key'] as $local) {
                $bridge_selector->by(
                    RowSelector::EXP_EQ,
                    $bridge['local'][$local],
                    $this->primary_entity->get($local)
                );
            }
        } else {
            $bridge_selector->by(
                RowSelector::EXP_EQ,
                $bridge['local'],
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
        $config = $this->config[$attr];

        if ($entity->dirty()) {
            $entity->save();
        }
        if (!isset($config['bridge'])) {
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
}
