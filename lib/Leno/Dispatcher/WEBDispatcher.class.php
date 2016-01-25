<?php
namespace Leno\Dispatcher;
use \Leno\App;
App::uses('Dispatcher', 'Leno.Dispatcher');
App::uses('DispatchInterface', 'Leno.LIF');
App::uses('Controller', 'Leno.Controller');
use \Leno\LIF\DispatchInterface;
use \Leno\Debugger;
use \Leno\Controller\Controller;
use \Leno\Configure;
class WEBDispatcher extends Dispatcher implements DispatchInterface {

	protected $params;

	protected $paths;

	public function dispatch() {
		$path_info = array_key_exists('PATH_INFO', $_SERVER) ?
										$_SERVER['PATH_INFO'] : null;
		if($path_info !== null && $path_info !== '') {
			$paths = array_filter(explode('/', $path_info));
		} else {
			$paths = array(
				Configure::read('DFT_CONTROLLER'),
				Configure::read('DFT_ACTION')
			);
		}
		$namespace = 'Controller';
		$cp = [];
		foreach($paths as $k=>$path) {
			$class = $this->loadController($path, $namespace);
			if(!$class) {
				$namespace .= '.' . $path;
				continue;
			}
			$cp[] = str_replace(
				'Controller/', '', str_replace('\\', '/', $class)
			);
			App::uses($path, $namespace);
			$action = $paths[$k+1];
			$cp[] = $action;
			if(empty($action)) {
				$action = 'index';
			}
			array_splice($paths, 0, $k+1);
			$this->params = $paths;
			$rc = new \ReflectionClass($class);
			$this->paths = $cp;
			if($rc->hasMethod($action)) {
				$method = $rc->getMethod($action);
				$controller = $rc->newInstance(implode('/', $cp));
				$method->invokeArgs($controller, $this->params);
			}
			return;
		}
	}

	public function path() {
		return $this->paths;
	}

	public function param() {
		return $this->params;
	}

	public function get() {
		return $_GET;
	}

	protected function loadController($class, $namespace) {
		$p = str_replace('.', '/', $namespace);
		$pathfile = APP_ROOT . DS. $p . DS . $class . Controller::suffix; 
		if(file_exists($pathfile)) {
			return $namespace . '\\' . $class;
		}
		return false;
	}
}
?>
