<?php
namespace Leno;

class Logger {

	const SUFFIX = '.log';

	protected static $dir;

	public static function init($dir) {
		if(!is_dir($dir)) {
			mkdir($dir, 0744, true);
		}
		self::$dir = $dir;
	}

	public static function log($type, $info) {
		$file = 'log-'.date('Y-m-d', time()) . self::SUFFIX;
		$pathfile = App::path(self::$dir, $file);
		$fp = fopen($pathfile, 'a+');
		fwrite($fp, $type . ":  ");
		fwrite($fp, $info . "\t\t");
		fwrite($fp, date('Y-m-d H:i:s', time()) . "\n");
		fclose($fp);
	}
}
?>
