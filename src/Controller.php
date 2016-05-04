<?php
namespace Leno;

use \Leno\View as View;
use \Leno\View\Template as Template;

abstract class Controller
{
    protected $view_dir = ROOT . '/View';

    protected $request;

    protected $response;

    protected $title = 'leno';

    protected $keywords = '';

    protected $description = '';

    protected $js = [];

    protected $css = [];

    protected $data = [];

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->initialize();
    }

    public function __call($method, $parameters = null)
    {
        $series = array_filter(explode('_', unCamelCase($method)));
        if(empty($series[0])) {
            throw new \Leno\Exception('Controller::'.$method.' Not Defined');
        }
        switch($series[0]) {
            case 'set':
                array_splice($series, 0, 1);
                $key = implode('_', $series);
                return $this->set($key, $parameters[0]);
                break;
            case 'get':
                array_splice($series, 0, 1);
                $key = implode('_', $series);
                return $this->get($key);
                break;
        }
        throw new \Leno\Exception('Controller::'.$method.' Not Defined');
    }

    protected function initialize()
    {
    }

    protected function get($key)
    {
        return $this->data[$key] ?? null;
    }

    protected function set($key, $val)
    {
        $this->data[$key] = $val;
        return $this;
    }

    protected function render($view, $data=[])
    {
        if(!isset($data['__head__'])) {
            $data['__head__'] = [];
        }
        if(!empty($this->title)) {
            $data['__head__']['title'] = $this->title;
        }
        if(!empty($this->description)) {
            $data['__head__']['description'] = $this->description;
        }
        if(!empty($this->keywords)) {
            $data['__head__']['keywords'] = $this->keywords;
        }

        if(!empty($this->js)) {
            $data['__head__']['js'] = $this->js;
        }

        if(!empty($this->js)) {
            $data['__head__']['css'] = $this->css;
        }
        foreach($this->data as $k=>$d) {
            $data[$k] = $d;
        }
        (new View($view, $data))->display();
    }

    /**
     * @description 获取前端传递上来的参数
     */
    protected function input($key, $rule=null, $message = null)
    {
        $source = $this->getInputSource();
        if(!empty($rule)) {
            try {
                (new \Leno\Validator($rule, $key))->check($source[$key] ?? null);
            } catch(\Exception $e) {
                throw new \Leno\Http\Exception(400, $message ?? $e->getMessage());   
            }
        }
        return $source[$key] ?? null;
    }

    protected function inputs($rules)
    {
        $source = $this->getInputSource();
        $ret = [];
        foreach($rules as $k=>$rule) {
            try {
                (new \Leno\Validator($rule, $k))->check($source[$k] ?? null);
                $ret[$k] = $source[$k] ?? null;
            } catch(\Exception $e) {
                $message = $rule['message'] ?? $e->getMessage();
                throw new \Leno\Http\Exception(400, $message);
            }
        }
        return $ret;
    }

    private function getInputSource()
    {
        $source_map = [
            'GET'  => $_GET,
            'POST' => $_POST,
            'DELETE' => $_POST,
            'PUT' => $_POST,
        ];
        $method = $this->request->getMethod();
        return $source_map[strtoupper($method)];
    }
}
