<?php
namespace Leno\Worker;
use \Leno\App;
use \Leno\LIF\WorkerInterface;
App::uses('Worker', 'Leno.Worker');
App::uses('WorkerInterface', 'Leno.LIF');
class WebWorker extends Worker implements WorkerInterface {

	public function info($status=self::SUCCSS, $msg='', $data=array()) {
		$data = array(
			'status'=>$status,
			'msg'=>$msg,
			'data'=>$data
		);
		die(json_encode($data));
	}

	public function param($key, $msg=null, $reg=null, $dft = null) {
		$data = array_merge($_GET, $_POST);
		$value = $data[$key];
		if($msg == null) {
			if($value == null && $dft != null) {
				return $dft;
			}
			return $value;
		}
		if($value === null || $value === '') {
			self::info(self::S_INPUT, $msg, ['param'=>$key]);
		}
		if(empty($reg)) {
			return $value;
		}
		if(!preg_match($reg, $value)) {
			self::info(self::S_INPUT, $msg, ['param'=>$key]);
		}
		return $value;
	}
}
?>
