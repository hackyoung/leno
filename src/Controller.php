<?php
namespace Leno;

use \Leno\View\View as View;
use \Leno\View\Template as Template;

abstract class Controller
{

    protected $request;

    protected $response;

    protected $title;

    protected $keyword;

    protected $data = [];

    public function __construct($request, $response)
    {
    }

    protected function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    protected function loadView($view, $data=[])
    {
        View::addViewDir(ROOT.'/View');
        Template::setCacheDir(ROOT . '/tmp/view');
        $data = array_merge($this->data, $data);
        (new View($view, $data))->display();
    }

    protected function checkParameters($var, $rules)
    {
        return (new Validator)->execute($var, $rules);
    }
}
