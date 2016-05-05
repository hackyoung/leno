<?php
namespace Test\Controller;

class Test extends \Leno\Controller
{
    public function index($hello, $world)
    {
        echo $hello . "\n";
        echo $world . "\n";
        $this->getService('hello', ['hello world'])->output();
    }
}
