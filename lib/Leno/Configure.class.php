<?php
namespace Leno;
use \Leno\App;
App::uses('LException', 'Leno.LException');
use \Leno\LException\LException;

class Configure {

	const filename = 'Config.cfg.php';

	const direname = 'Config';

	public static $_conf = array();

	public static function addConfigFile($file) {
		if(file_exists($file)) {
			$config = include($file);
			self::$_conf = array_merge(self::$_conf, $config);
		}
	}

	public static function read($key) {
		$key = strtoupper($key);
		if(!self::is($key)) {
			throw new LException('Configure ' . $key . ' not found');
		}
		return self::$_conf[$key];
	}

	public static function write($key, $value) {
		$key = strtoupper($key);
		self::$_conf[$key] = $value;
	}

	public static function is($key) {
		return isset(self::$_conf[$key]) ? true : false;
	}

	public static function init() {
		$files = array(
			APP_ROOT . '/Config/Config.cfg.php',
			LIB_ROOT . '/Config/Config.cfg.php'
		);
		foreach($files as $file) {
			self::addConfigFile($file);
		}
	}
}
?>
