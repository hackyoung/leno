<?php
namespace Leno\Traits;

Trait Setter
{
    public function __call($method, array $args = null)
    {
        $prefix = substr($method, 0, 3);
        switch($prefix) {
            case 'set':
                $attr = str_replace('set', '', $method);
                $this->attr = $args[0];
                return $this;
        }
        throw new \Leno\Exception (get_called_class() . '::'.$method . ' Not Defined');
    }
}
