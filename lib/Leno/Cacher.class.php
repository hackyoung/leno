<?php
namespace Leno;

class Cacher {
	
	private $file;

	protected static $dir;

	public function __construct($file) {
		$this->file = App::path(self::$dir, $file);
	}

	public function getFile() {
		return $this->file;
	}

	public function save($content) {
		file_put_contents($this->file, $content);
	}

	public static function init($dir) {
		if(!is_dir($dir)) {
			mkdir($dir, 0744, true);
		}
		self::$dir = $dir;
	}
}
?>
