<?php
namespace Leno\test;
use \Leno\App;
App::uses('WEBDispatcher', 'Leno.Dispatcher');
App::uses('CLIDispatcher', 'Leno.Dispatcher');

use \Leno\Dispatcher\WEBDispatcher;
use \Leno\Dispatcher\CLIDispatcher;

class DispatcherTest {
	public function test() {
		$dispacher = new WEBDispatcher();	
		$dispacher->dispatch();
		$dispacher = new CLIDispatcher();
		$dispacher->dispatch();
	}
}

?>
