<?php
namespace Leno\Controller;
use \Leno\App;
use \Leno\View\View;
use \Leno\Debugger;
use \Leno\WebRoot;
App::uses('LObject', 'Leno');

class Controller extends \Leno\LObject {

	const suffix = '.class.php';

	protected $title = 'leno';

	protected $keyword = 'leno';

	protected $css = array();

	protected $js = array();

	protected $data = array();

	protected $view;

	public function __construct() {
		$this->addJs(array(
			\Leno\WebRoot::lib('leno/js/jquery.js'),
			\Leno\WebRoot::lib('leno/js/leno.js')
		));
		$this->addCss(array(
			\Leno\WebRoot::lib('leno/css/leno.css')
		));
	}

	protected function set($key, $val=null) {
		if(gettype($key) == 'array') {
			$this->data = array_merge($this->data, $key);
		} else {
			$this->data[$key] = $val;
		}
	}

	protected function addJs($js) {
		if(gettype($js) == 'array') {
			$this->js = array_merge($this->js, $js);
			return;
		}
		array_push($this->js, $js);
	}

	protected function addCss($css) {
		if(gettype($css) == 'array') {
			$this->css = array_merge($this->css, $css);
			return;
		}
		array_push($this->css, $css);
	}

	protected function loadView($view, $data=array()) {
		$this->data = array_merge($this->data, $data);
		$this->data['__head__'] = array(
			'__title__'=>$this->title,
			'__keyword__'=>$this->keyword,
			'__js__'=>$this->js,
			'__css__'=>$this->css
		);
		$this->view = new View($view, $this->data);
		$this->view->display();
	}

	protected function loadModel($_model, $namespace, $alias=null) {
		App::uses($_model, $namespace);	
		$model = str_replace('.', '\\', $namespace) . '\\' . $_model;
		$rc = new \ReflectionClass($model);
		$m = $rc->newInstance();
		if($alias) {
			$this->$alias = $m;
		} else {
			$this->$_model = $m;
		}
		return $m;
	}

	public function __set($key, $value) {
		$this->$key = $value;
	}
}
?>
