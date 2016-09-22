<?php
namespace Leno;

use \Leno\Type;
use \Leno\Exception\MethodNotFoundException;

abstract class Controller
{
    protected $request;

    protected $response;
    
    protected $title = 'leno';

    protected $view_class = "\\Leno\\View";

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

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function __call($method, array $args = [])
    {
        $series = array_filter(explode('_', unCamelCase($method)));
        $methods = array_splice($series, 0, 1);
        array_unshift($args, implode('_', $series));
        if (in_array($methods[0], ['get', 'set'])) {
            return call_user_func_array([$this, $method], $args);
        }
        throw new MethodNotFoundException('Controller::'.$method);
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

    protected function addJs($js)
    {
        $this->js[] = $js;
        return $this;
    }

    protected function addCss($css)
    {
        $this->css[] = $css;
        return $this;
    }

    /**
     * 呈现一个HTML页面
     */
    protected function render($view, $data=[])
    {
        $view = new $this->view_class($view);
        $this->beforeRender($view);
        $head = [];
        !empty($this->title) && $head['title'] = $this->title;
        !empty($this->description) && $head['description'] = $this->description;
        !empty($this->keywords) && $head['keywords'] = $this->keywords;
        !empty($this->js) && $head['js'] = $this->js;
        !empty($this->css) && $head['css'] = $this->css;
        $view->set('__head__', $head);
        foreach($this->data as $k=>$d) {
            $view->set($k, $d);
        }
        $view->render();
    }

    /**
     * 获取前端传递上来的参数
     */
    protected function input($key, $rule=null, $message = null)
    {
        $source = $this->getInputSource();
        if(empty($rule)) {
            return $source[$key] ?? null;
        }
        $type = Type::get($rule['type'])->setExtra($rule['extra'] ?? []);
        if(isset($rule['required'])) {
            $type->setRequired($rule['required']);
        }
        if(isset($rule['allow_empty'])) {
            $type->setAllowEmpty($rule['allow_empty']);
        }
        try {
            $type->check($source[$key] ?? null);
        } catch(\Exception $e) {
            throw new \Leno\Http\Exception(400, $message ?? $e->getMessage());   
        }
        return $source[$key] ?? null;
    }

    /**
     * 获取从前端传上来的参数
     * @param array rules = [
     *      'hello', // 不验证可用性
     *      'world', // 不验证可用性
     *      'hell' => ['type' => 'string'], // 通过规则检查其可用性
     *      'password'=> ['type' => 'password'] // 通过规则检查其可用性
     * ];
     */
    protected function inputs($rules = null)
    {
        $source = $this->getInputSource();
        if ($rules === null) {
            return $source;
        }
        $ret = [];
        foreach($rules as $k=>$rule) {
            if(is_int($k)) {
                $ret[$rule] = $source[$rule] ?? null;
                continue;
            }
            $type = Type::get($rule['type'])->setValueName($k)
                ->setExtra($rule['extra'] ?? []);
            if(isset($rule['required'])) {
                $type->setRequired($rule['required']);
            }
            if(isset($rule['allow_empty'])) {
                $type->setAllowEmpty($rule['allow_empty']);
            }
            try {
                $type->check($source[$k]);
            } catch(\Exception $e) {
                $message = $rule['message'] ?? $e->getMessage();
                throw new \Leno\Http\Exception(400, $message);
            }
            $ret[$k] = $source[$k] ?? null;
        }
        return $ret;
    }

    /**
     * 输出数据,采用该方法包裹所有需要界面展示的数据都应该用该方法包裹
     * 方便以后拓展国际化
     */
    protected function output(string $output)
    {
        return $output;
    }

    /**
     * 输出数组
     */
    protected function outputs(array $outputs)
    {
        return $outputs;
    }

    protected function getService($name, $args=[])
    {
        return \Leno\Service::getService($name, $args);
    }

    protected function beforeRender(&$view)
    {
    }

    private function getInputSource()
    {
        $source_map = [
            'GET'  => $_GET,
            'POST' => $_POST,
            'DELETE' => $_POST,
            'PUT' => $_POST,
        ];
        $method = strtoupper($_POST['_method'] ?? $this->request->getMethod());
        return $source_map[$method];
    }
}
