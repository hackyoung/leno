<?php
namespace Leno\Traits;

Trait Setter
{
    public function __call($method, array $args = null)
    {
        $prefix = substr($method, 0, 3);
        switch($prefix) {
            case 'set':
                $attr = unCamelCase(str_replace('set', '', $method));
                $this->$attr = $args[0] ?? $args;
                return $this;
            case 'get':
                $attr = unCamelCase(str_replace('set', '', $method));
                return $this->$attr;
        }
        throw new \Leno\Exception (get_called_class() . '::'.$method . ' Not Defined');
    }
}
