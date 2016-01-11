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
		foreach($paths as $k=>$path) {
			$class = $this->loadController($path, $namespace);
			if(!$class) {
				$namespace .= '.' . $path;
				continue;
			}
			App::uses($path, $namespace);
			$action = $paths[$k+1];
			if(empty($action)) {
				$action = 'index';
			}
			array_splice($paths, 0, $k+1);
			$params = $paths;
			$rc = new \ReflectionClass($class);
			if($rc->hasMethod($action)) {
				$method = $rc->getMethod($action);
				$controller = $rc->newInstance();
				$method->invokeArgs($controller, $params);
			}
			return;
		}
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
