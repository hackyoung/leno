<?php
namespace Leno;

/*
 * @description 该类提供类注册功能，注册的类不需要require引入
 */
require_once dirname(__FILE__).DS."Singleton.class.php";
require_once dirname(__FILE__).DS.'LException'.DS.'LoaderException.class.php';

use \Leno\LException\LoaderException;

class ClassLoader extends Singleton {

	const EXTENSION = '.class.php';

	/*
	 * 类到路径的映射
	 * 使用一个类时，程序会自动根据路径加载使用的类
	 */
	private $map = array();
	/*
	 * Leno搜索类的目录，
	 * APP_ROOT 是应用程序类的搜索跟目录，
	 * 定义类时其名字空间是从APP_ROOT开始的相对目录路径，
	 * 比如，类Test在APP_ROOT/Controller,
	 * 那么Test的名字空间就应该是namespace Controller;
	 * LIB_ROOT同理
	 */
	private $dirs = array(
		APP_ROOT,
		LIB_ROOT
	);

	/*
	 * @name __construct
	 * @description 注册autoload到ClassLoader::autoload
	 */
	protected function __construct() {
		spl_autoload_register(array($this, 'autoload'));
	}

	/*
	 * @name register
	 * @description 添加类到包含类文件路径的映射
	 * @param string class 不带名字空间的类名
	 * @param string dr 用.区分的名字空间，如Leno.Controller,表示该类的名字空间是Leno\Controller,这个类必须在[APP_ROOT|LIB_ROOT]/Leno/Controller 文件夹中
	 * @return void
	 */
	public function register($class, $dr) {
		$idx = str_replace('.', '\\', $dr .'.'.$class);
		foreach($this->dirs as $dir) {
			$d = $dir. DS . str_replace('.', DS, $dr);
			$pathfile = $d . DS . $class . self::EXTENSION;
			if(file_exists($pathfile)) {
				$this->map[$idx] = $dir . DS . str_replace('.', DS, $dr);
				break;
			}
		}
		if($this->map[$idx] == null) {
			throw new LoaderException($idx);
		}
	}

	/*
	 * @name autoload
	 * @description 自动载入类
	 */
	public function autoload($class) {
		$path = $this->map[$class];
		$ca = explode('\\', $class);
		$class = $ca[count($ca) - 1];
		if(!empty($path)) {
			require_once $path . DS . $class . self::EXTENSION;
		}
	}

	public static function instance() {
		return Singleton::getSingleton(get_class());
	}
}
?>
