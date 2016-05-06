<?php
namespace Test\Controller;

class Test extends \Leno\Controller
{
    public function index($hello, $world)
    {
        echo $hello . "\n";
        echo $world . "\n";
        /*
        $param = $this->inputs(['hello' => [
            'type' => 'uuid'
        ], 'world']);
        var_dump($param);
         */
        $this->getService('hello', ['hello world'])->output();
    }
}
