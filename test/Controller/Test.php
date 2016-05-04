<?php
namespace Test\Controller;

class Test extends \Leno\Controller
{
    public function index()
    {
        $this->input('hello');
        var_dump('hello world');
    }
}
