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
        $this->beforeRender();
        !isset($data['__head__']) && $data['__head__'] = [];
        $head = &$data['__head__'];
        !empty($this->title) && $head['title'] = $this->title;
        !empty($this->description) && $head['description'] = $this->description;
        !empty($this->keywords) && $head['keywords'] = $this->keywords;
        !empty($this->js) && $head['js'] = $this->js;
        !empty($this->css) && $head['css'] = $this->css;
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

    /**
     * @description 获取一组输入
     * @param array rules = [
     *      'hello', // 不验证可用性
     *      'world', // 不验证可用性
     *      'hell' => ['type' => 'string'], // 通过规则检查其可用性
     *      'password'=> ['type' => 'password'] // 通过规则检查其可用性
     * ];
     */
    protected function inputs($rules)
    {
        $source = $this->getInputSource();
        $ret = [];
        foreach($rules as $k=>$rule) {
            if(is_int($k)) {
                $ret[$rule] = $source[$rule] ?? null;
                continue;
            }
            try {
                (new \Leno\Validator($rule, $k))->check($source[$k] ?? null);
            } catch(\Exception $e) {
                $message = $rule['message'] ?? $e->getMessage();
                throw new \Leno\Http\Exception(400, $message);
            }
            $ret[$k] = $source[$k] ?? null;
        }
        return $ret;
    }

    /**
     * @description 输出数据,采用该方法包裹所有需要界面展示的数据都应该用该方法包裹
     * 方便以后拓展国际化
     */
    protected function output(string $output)
    {
        return $output;
    }

    /**
     * @description 输出数组
     */
    protected function outputs(array $outputs)
    {
        return $outputs;
    }

    protected function getService($name, $args=[])
    {
        return \Leno\Service::getService($name, $args);
    }

    protected function beforeRender()
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
        $method = strtoupper($this->request->getMethod());
        return $source_map[$method];
    }
}
