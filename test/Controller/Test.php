<?php
namespace Test\Controller;

class Test extends \Leno\Controller
{
    public function index()
    {
        $this->inputs([
            'hlo' => ['type' => 'string', 'message' => '请上传hello'],
            'world' => ['type' => 'string']
        ]);
        var_dump('hello world');
    }
}
