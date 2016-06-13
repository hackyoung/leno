<?php
namespace Leno\Traits;

Trait Magic
{
    private function __magic_call($method, array $args = null)
    {
        $prefix = substr($method, 0, 3);
        switch($prefix) {
            case 'set':
                $attr = unCamelCase(str_replace('set', '', $method));
                $this->$attr = $args[0];
                return $this;
            case 'get':
                $attr = unCamelCase(str_replace('get', '', $method));
                return $this->$attr;
        }
        throw new \Leno\Exception (get_called_class() . '::'.$method . ' Not Defined');
    }
}
