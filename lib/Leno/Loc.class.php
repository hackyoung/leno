<?php
namespace Leno;
App::uses('LObject', 'Leno');

class Loc extends LObject {
	
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
