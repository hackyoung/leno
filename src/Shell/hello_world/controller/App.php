<?php
namespace Controller;

class App extends \Leno\Controller
{
    protected $js = [
        '/lib/leno/js/jquery.js',
        '/lib/leno/js/leno.js',
    ];

    protected $css = [
        '/lib/leno/css/leno.css',
    ];

    protected function beforeRender()
    {
    }
}
