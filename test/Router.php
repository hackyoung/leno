<?php
namespace Test;

class Router extends \Leno\Routing\Router
{
    protected $base = 'test/controller';

    public function beforeRoute()
    {
        $this->setPath('test');
    }
}
