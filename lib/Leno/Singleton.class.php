<?php
namespace Leno;

class Singleton {

	private static $instances;

	protected function __construct() {}

	public static function getSingleton($class) {
		if(!isset(self::$instances[$class])) {
			$object = new $class;
			if($object instanceof Singleton) {
				self::$instances[$class] = $object;
			}
		}
		return self::$instances[$class];
	}

	// 阻止子类复制单例
	public function __clone() {
		trigger_error('Singleton can not clone', E_USER_ERROR);
	}
}
?>
