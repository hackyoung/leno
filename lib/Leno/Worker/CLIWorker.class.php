<?php
namespace Leno\Worker;
use \Leno\Worker\Worker;
use \Leno\LIF\WorkerInterface;
use \Leno\App;
App::uses('Worker', 'Leno.Worker');
App::uses('WorkerInterface', 'Leno.LIF');

class CLIWorker extends Worker implements WorkerInterface {

	public function param($key, $msg, $reg) {
	
	}

	public function info($status, $msg, $data) {
	
	}
}
?>
