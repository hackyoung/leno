<?php
namespace Leno\ORM;

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

    public function get ($attr)
    {
        if ($this->secondary_entities[$attr] ?? false) {
            return $this->secondary_entities[$attr];
        }
        $foreign = $this->config[$attr] ?? false;
        if (!$foreign) {
            throw new \Leno\Exception ('没有找到配置: '.$attr);
        }
        if (!isset($foreign['bridge'])) {
            $this->secondary_entities[$attr] = $this->getNoBridge($foreign);
            return $this->secondary_entities[$attr];
        }

        $this->secondary_entities[$attr] = $this->getBridge($foreign);
        return $this->secondary_entities[$attr];
    }

    public function set (string $attr, Entity $value)
    {
        $config = $this->config[$attr] ?? false;
        if (!$config || !($value instanceof $config['entity'])) {
            throw new \Leno\Exception ('没有找到配置');
        }
        $this->secondary_entities[$attr] = $value;
        $this->primary_entity->set($config['local_key'], $value->get($config['foreign_key']));
        return $this;
    }

    public function add (string $attr, Entity $value)
    {
        $config = $this->config[$attr] ?? false;
        if (!$config || !($value instanceof $config['entity'])) {
            throw new \Leno\Exception ('没有找到配置');
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

    private function getNoBridge($config)
    {
        $selector = $config['entity']::selector();
        if (!is_array($config['local_key'])) {
            foreach ($config['local_key'] as $local) {
                $local_value = $this->primary_entity->get($local);
                $selector->by('eq', $config['foreign_key'][$local], $local_value);
            }
            return $selector->find();
        }
        $local_value = $this->primary_entity->get($config['local_key']);
        $selector->by('eq', $config['foreign_key'], $local_value);
        return $selector->find();
    }

    private function getBridge($config)
    {
        $selector = $config['entity']::selector();
        $bridge = $config['bridge'];

        $bridge_selector = $bridge['entity']::selector();

        if (is_array($config['local_key'])) {
            foreach ($config['local_key'] as $local) {
                $local_value = $this->primary_entity->get($local);
                $bridge_selector->by('eq', $bridge['local'][$local], $local_value);
            }
        } else {
            $local_value = $this->primary_entity->get($config['local_key']);
            $bridge_selector->by('eq', $bridge['local'], $local_value);
        }

        if (is_array($config['foreign_key'])) {
            foreach ($config['foreign_key'] as $foreign) {
                $bridge_selector->on(
                    'eq', $bridge['foreign'][$foreign],
                    $selector->getFieldExpr($foreign)
                );
            }
        } else {
            $bridge_selector->on(
                'eq', $bridge['foreign'],
                $selector->getFieldExpr($config['foreign_key'])
            );
        }

        return $selector->join($bridge_selector)->find();
    }

    private function saveEntity (string $attr, Entity $entity)
    {
        $config = $this->config[$attr];

        if ($entity->dirty()) {
            $entity->save();
        }
        if (isset($config['bridge'])) {
            $bridge = new $config['bridge']['entity'];
            $bridge->set($config['bridge']['local'], $this->primary_entity->get($config['local_key']));
            $bridge->set($config['bridge']['foreign'], $entity->get($config['foreign_key']));
            $this->bridge_entities[] = $bridge;
        }
    }
}
