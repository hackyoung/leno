<?php

class Controller extends \Leno\Controller
{
    protected $js = [
        '/lib/leno/js/jquery.js',
        '/lib/leno/js/leno.js',
        '/js/base.js'
    ];

    protected $css = [
        '/lib/leno/css/leno.css',
        '/css/style.css'
    ];

    protected function initialize()
    {
    }

    protected function beforeRender()
    {
    }

    public function beforeExecute()
    {
    }

    public function afterExecute()
    {
    }
}
