<?php
namespace Leno;
use \Leno\App;

class Debugger {

	const SUFFIX = '.debug';

	protected static $dir;

	public static function init($dir) {
		if(!is_dir($dir)) {
			mkdir($dir, 0744, true);
		}
		self::$dir = $dir;
	}

	/*
	 * @description 输入变量的值
	 */
	public static function dump($var, $tofile=false) {
		if($tofile) {
			$file = date('Y-m-d H', time()) . self::SUFFIX;
			$pathfile = App::path(self::$dir, $file);	
			$fp = fopen($pathfile, 'a+');
			fwrite($fp, date('Y-m-d H:i:s', time()) . "\n");
			fwrite($fp, var_export($var, true));
			fwrite($fp, "\n");
			fclose($fp);
		} else {
			echo "<pre>";
			var_dump($var);
		}
	}
}
?>
