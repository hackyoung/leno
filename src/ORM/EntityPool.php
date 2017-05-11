<?php
namespace Leno\ORM;

use Leno\ORM\Entity;

class EntityPool
{
    use \Leno\Traits\Singleton;

    private $entities = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function set(string $key, Entity &$entity)
    {
        $this->entities[$key] = $entity;
        return $this;
    }

    public function is(string $key)
    {
        return isset($this->entities[$key]) && $this->entities[$key];
    }

    public function get(string $key)
    {
        return $this->entities[$key] ?? null;
    }

    public function getKey($table_name, $primary_key)
    {
        if (is_array($primary_key)) {
            $primary_key = implode('', $primary_key);
        }
        return $table_name . '-' . $primary_key;
    }
}
