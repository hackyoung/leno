<?php
namespace Controller;
use \Leno\Controller\Controller;
use \Leno\Debugger;

class Home extends Controller {

	public function index() {
		$m = $this->loadModel('Test', 'Model', 't');
		$this->set('hello', 'word');
		$this->loadView('test');
	}

	public function Article($p=null, $pa=null, $par=null) {
	}
}
?>
