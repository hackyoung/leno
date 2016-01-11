<?php
namespace Leno;
use \Leno\ClassLoader;
use \Leno\Singleton;
use \Leno\Factory\DispatcherFactory;
use \Leno\Configure;
use \Leno\Debugger;
use \Leno\Logger;
use \Leno\View\View;
require_once dirname(__FILE__).DS."ClassLoader.class.php";

class App extends Singleton {

	/*
	 * 当前框架的版本
	 */
	const VERSION = '0.0.1';

	/*
	 * WEB模式，WEB模式指从浏览器访问的方式执行的模式
	 */
	const M_WEB = 'web';

	/*
	 * CLI模式，CLI模式指从命令行访问的方式执行的模式
	 */
	const M_CLI = 'cli';

	protected function __construct() {

		// 初始化Configure
		App::uses('Configure', 'Leno');
		Configure::init();

		// 初始化Debuger
		App::uses('Debugger', 'Leno');
		Debugger::init(Configure::read('tmp').DS.'debug');

		// 初始化Logger
		App::uses('Logger', 'Leno');
		Logger::init(Configure::read('tmp').DS.'log');

		// 初始化Cacher
		App::uses('Cacher', 'Leno');
		Cacher::init(Configure::read('tmp').DS.'cache');

		// 初始化WebRoot
		App::uses('WebRoot', 'Leno');
		\Leno\WebRoot::init(Configure::read('WEB_ROOT'));
		// 初始化View
		App::uses('View', 'Leno.View');
		View::init(
			Configure::read('VIEW_ROOT'), 
			Configure::read('VIEW_COMMON')
		);

		$this->dispatch();
	}

	/*
	 * @description 详见ClassLoader::register方法
	 * @access public
	 * @return void
	 */
	public static function uses($class, $namespace) {
		$classloader = ClassLoader::instance();
		$classloader->register($class, $namespace);
	}

	/*
	 * @description 兼容windows和类unix系统的文件路径拼接
	 * @access public
	 * @return string
	 */
	public function path($before, $after) {
		$ds = DS;
		$before = preg_replace('/[\/\\\]$/', '', $before);
		$after = preg_replace('/^[\/\\\]/', '', $after);
		return $before.$ds.$after;
	}

	/*
	 * @description 返回当前的操作系统信息
	 * @access public
	 * @return string
	 */
	public function os() {
		return php_uname();
	}

	/*
	 * @description 返回当前的执行模式App::M_WEB|App::M_CLI
	 * @access public
	 * @return string
	 */
	public function mode() {
		if($_SERVER["HTTP_USER_AGENT"] == null) {
			return self::M_CLI;
		}
		return self::M_WEB;
	}

	public function error_handler($serverity, $message, $file, $line) {
		if (!(error_reporting() & $severity)) {
			// This error code is not included in error_reporting
			return;
		}
		throw new ErrorException($message, 0, $severity, $file, $line);
	}

	public function dispatch() {
		self::uses('DispatcherFactory', 'Leno.Factory');
		$mode = $this->mode();
		$factory = DispatcherFactory::instance();
		switch($mode) {
			case self::M_WEB:
				$dispatcher = $factory->createWebDispatcher();
				break;
			case self::M_CLI:
				$dispatcher = $factory->createCliDispatcher();
				break;
		}
		$dispatcher->dispatch();
	}

	public static function base_url() {
		if (isset($_SERVER['HTTP_HOST'])) {
			$base_url = isset($_SERVER['HTTPS']) &&
				strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
			$base_url .= '://'. $_SERVER['HTTP_HOST'];
			$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']),
											'', $_SERVER['SCRIPT_NAME']);
		} else {
			$base_url = 'http://localhost/';
		}
		return $base_url;
	}

	public static function instance() {
		return Singleton::getSingleton(get_class());
	}
}
?>
