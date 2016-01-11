<?php
namespace Leno;
/*
 * @name WebRoot
 * @description 管理webroot目录的类，webroot是存放js, css, img, lib已经静态HTML文件的地方
 */
class WebRoot {

	/*
	 * @name dir webroot的文件夹根目录
	 */
	protected static $dir;

	/*
	 * @name uriroot webroot的documentroot
	 */
	protected static $uriroot;

	/*
	 * @name init
	 * @description 设置dir和uriroot, 该初始化操作应该在应用初始化时执行
	 * @param string dirname webroot相对于ROOT的目录名
	 */
	public static function init($dirname) {
		self::$dir = ROOT . DS . $dirname;
		self::$uriroot = App::base_url().$dirname;
	}

	/*
	 * @name js
	 * @description 返回self::dir/js中的js文件访问链接
	 * @param string js 不带后缀的js文件名
	 * @return string js文件的访问链接
	 */
	public static function js($js) {
		return self::$uriroot.'/js/'.$js.'.js';
	}

	/*
	 * @name css
	 * @description 返回self::dir/css中的css文件访问链接
	 * @param string css 不带后缀的css文件名
	 * @return string css文件的访问链接
	 */
	public static function css($css) {
		return self::$uriroot.'/css/'.$css.'.css';
	}

	/*
	 * @name css
	 * @description 返回self::dir/lib中的css/js/img及其他文件访问链接
	 * @param string lib libname/css/*.css|libname/js/*.js...
	 * @return string lib资源的访问链接
	 */
	public static function lib($lib) {
		return self::$uriroot.'/lib/'.$lib;
	}

	/*
	 * @name img
	 * @description 返回self::dir/img中的img文件访问链接
	 * @param string img 带后缀的img文件名
	 * @return string img文件的访问链接
	 */
	public static function img($img) {
		return self::$uriroot.'/img/'.$img;
	}
}
?>
