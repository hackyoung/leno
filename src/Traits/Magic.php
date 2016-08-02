<?php
namespace Leno\Traits;

Trait Magic
{
    private function __magic_call($method, array $args = [])
    {
        $prefix = substr($method, 0, 3);
        $class = get_called_class();
        switch($prefix) {
            case 'set':
                $attr = \unCamelCase(str_replace('set', '', $method));
                $rc = new \ReflectionClass($class);
                if ($rc->hasMethod('set')) {
                    array_unshift($args, $attr);
                    return call_user_func_array([$this, 'set'], $args);
                }
                $this->$attr = $args[0];
                return $this;
            case 'get':
                $attr = \unCamelCase(str_replace('get', '', $method));
                $rc = new \ReflectionClass($class);
                if ($rc->hasMethod('get')) {
                    array_unshift($args, $attr);
                    return call_user_func_array([$this, 'get'], $args);
                }
                return $this->$attr;
        }
        throw new \Leno\Exception (get_called_class() . '::'.$method . ' Not Defined');
    }
}
