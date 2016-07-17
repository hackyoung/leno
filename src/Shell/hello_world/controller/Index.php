<?php
namespace Controller;

class Index extends \Controller
{
    public function index()
    {
        $this->set('hello', "hello world");
        $this->render('global.index');
    }
}
