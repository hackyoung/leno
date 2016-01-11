<?php
namespace Leno\Factory;
use \Leno\App;
App::uses('WEBDispatcher', 'Leno.Dispatcher');
App::uses('CLIDispatcher', 'Leno.Dispatcher');
use \Leno\Dispatcher\WEBDispatcher;
use \Leno\Dispatcher\CLIDispatcher;
use \Leno\Singleton;

class DispatcherFactory extends Singleton {
	public function createWebDispatcher() {
		return new WEBDispatcher();
	}

	public function createCliDispatcher() {
		return new CLIDispatcher();
	}

	public static function instance() {
		return Singleton::getSingleton(get_class());
	}
}
?>
