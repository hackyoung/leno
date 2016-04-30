<?php
namespace Leno;

class Configure
{
	public static $_conf = array();

	public static function addConfigFile($file) {
		if(file_exists($file)) {
			$config = include($file);
			self::$_conf = array_merge(self::$_conf, $config);
		}
	}

	public static function read($key) {
		$key = strtoupper($key);
        if(isset(self::$_conf[$key])) {
		    return self::$_conf[$key];
        }
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
			ROOT . '/config/default.php',
		);
		foreach($files as $file) {
			self::addConfigFile($file);
		}
	}
}
?>
