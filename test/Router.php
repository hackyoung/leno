<?php
namespace Test;

class Router extends \Leno\Routing\Router
{
    protected $base = 'test/controller';

    protected $rules = [
        'test/${1}/hello/${2}' => 'test/${1}/${2}',
        'router' => 'Router',
    ];

    public function beforeRoute()
    {
        $this->setPath('test/123/hello/abc');
    }
}
