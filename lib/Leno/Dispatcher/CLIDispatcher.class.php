<?php
namespace Leno\Dispatcher;
use \Leno\App;
App::uses('Dispatcher', 'Leno.Dispatcher');
App::uses('DispatchInterface', 'Leno.LIF');

use \Leno\LIF\DispatchInterface;

class CLIDispatcher extends Dispatcher implements DispatchInterface {

	public function dispatch() {
		echo 'CLIDispatcher';
	}
}
?>
