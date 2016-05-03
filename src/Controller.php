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

    protected function initialize()
    {
    }

    protected function set($key, $val)
    {
        $this->data[$key] = $val;
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
	protected function input($rule, $key=null, $message = null)
	{
		$source_map = [
			'GET'  => $_GET,
			'POST' => $_POST,
			'DELETE' => $_POST,
			'PUT' => $_POST,
		];
		$method = $this->request->getMethod();
		$source = $source_map[strtoupper($method)];
		$val = $key ? ($source[$key] ?? null) : $source;
		try {
			if((new \Leno\Validator($rule, $key ?? 'input'))->check($val)) {
				return $source[$key];
			}
		} catch(\Exception $ex) {
			if(!$message) {
				$message = $ex->getMessage();
			}
			$this->response(400, $message);
		}
	}

    protected function response($code, $message)
    {
        $response = $this->response->withStatus($code);
        $response->write($message);
        $this->response = $response;
    }
}
