<?php
namespace Leno;
use \Leno\Worker\Worker;

class LObject {

	protected static $worker;

	public static function setWorker($worker) {
		self::$worker = $worker;
	}

	protected function param($key, $msg=null, $reg=null, $dft=null) {
		return self::$worker->param($key, $msg, $reg, $dft);
	}

	protected function toClient($status, $msg='', $data=array()) {
		return self::$worker->info($status, $msg, $data);
	}

	protected function error($msg, $data=array()) {
		return self::$worker->info(Worker::S_ERROR, $msg, $data);
	}

	protected function success($msg, $data=array()) {
		return self::$worker->info(Worker::S_SUCCESS, $msg, $data);
	}

	protected function url($action=null, $param=null, $merge=false) {
		$app = App::instance();
		if($action == null) {
			$path = $app->dispatcher->path();
			$p = $app->dispatcher->param();
			$action = implode('/', array_merge($path, $p));
		}
		$base = App::path(App::base_url(), 'index.php');
		$p = [
			App::path($base, $action)
		];
		if(gettype($param) == 'array') {
			if($merge) {
				$param = array_merge($app->dispatcher->get(), $param);
			}
			if(count($param) > 0) {
				$_p = [];
				foreach($param as $k=>$v) {
					$_p[] = $k .'='. $v;
				}
				$p[] = implode('&', $_p);
				return implode('?', $p);
			}
		}
		return $p[0];
	}
}
?>
