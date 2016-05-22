<?php
namespace Controller;

class Index extends \Controller\App
{
    public function index()
    {
        $this->set('hello', "hello world");
        $this->render('index');
    }
}
