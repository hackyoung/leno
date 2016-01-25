<?php
namespace Leno\View;
use Leno\App;
use Leno\LObject;
use Leno\Exception\ViewException;
use Leno\View\Template;
use Leno\Debugger;
App::uses('LObject', 'Leno');
App::uses('ViewException', 'Leno.Exception');
App::uses('Template', 'Leno.View');
/*
 * @name View
 * @description Leno的视图功能类
 */
class View extends LObject {
	// 模板的后缀
	const suffix = '.lpt.php';
	// 模板的根目录
	protected static $dir;
	// common文件是公有的，View会先在根目录查找模板文件，如果没找到再到common目录查找
	protected static $common;
	// 模板文件名
	private $file;
	// 用于继承的临时view名字
	private $_temp_name;
	// 可在模板中访问的数据
	public $data = array();
	// 解析模板到View文件的对象
	private $template;
	// 子View列表
	private $_view = array();

	/*
	 * @name init
	 * @description 初始化View的方法，指定其View模板文件的根目录，common文件的根目录名
	 * @param string dir View模板文件的根目录
	 * @param common common文件的根目录名
	 */
	public static function init($dir, $common) {
		self::$dir = $dir;
		self::$common = $common;
	}

	/*
	 * @name __construct
	 * @description 查找View模板文件,初始化data和template
	 */
	public function __construct($file=null, $data=array()) {
		$array = array(
			self::$dir,
			App::path(self::$dir, self::$common),
			App::path(LIB_ROOT, 'Leno/View/view')
		);
		$file = str_replace('.', DS, $file);
		foreach($array as $dir) {
			$pathfile = App::path($dir, $file).self::suffix;
			if(file_exists($pathfile)) {
				$this->file = $pathfile;
				break;
			}
		}
		if(empty($this->file) || !file_exists($this->file)) {
			throw new ViewException($file, $array);
		}
		$this->data = $data;
		$this->template = new Template($this);
	}

	/*
	 * @name set
	 * @description 设置View显示的变量,self::set('hello', 'world'),则可以在模板文件中通过{$hello}访问其hello的值,支持<?php echo $hello; ?>
	 * @param string index 变量名
	 * @param mixed value 变量值
	 */
	public function set($index, $value) {
		$this->data[$index] = $value;
	}

	/*
	 * @name display
	 * @description 显示当前的View
	 */
	public function display() {
		if(gettype($this->data) === 'array') {
			extract($this->data);
		}
		include $this->template->display();
	}

	public function getFile() {
		return $this->file;
	}

	/*
	 * @name start
	 * @description 父View定义的child标签的开始实现标记
	 * @param string name child标签的name属性
	 */
	public function start($name) {
		ob_start();
		$this->_temp_name = $name;
	}

	/*
	 * @name end
	 * @description 取实现child标签的内容，然后赋值
	 */
	public function end() {
		$name = $this->_temp_name;
		$this->data[$name] = ob_get_contents();
		ob_end_clean();
	}

	/*
	 * @name e
	 * @description 获得子View的对象
	 * @param string idx 子View的索引名
	 */
	public function e($idx) {
		return $this->_view[$idx];
	}

	/*
	 * @name view
	 * @description 载入子View
	 * @param string idx 子View的访问索引
	 * @param View view 子View对象
	 */
	public function view($idx, $view, $data=false) {
		if($data) {
			foreach($this->data as $k=>$v) {
				$view->set($k,$v);
			}
		}
		$this->_view[$idx] = $view;
	}

	/*
	 * @name extend
	 * @description 继承一个View,可以实现所有的echo $var中的var
	 */
	public function extend($name, $show=true) {
		$this->view('extends', new View($name), true);
		if($show) {
			$this->e('extends')->display();
		}
		return $this->e('extends');
	}

	public function __toString() {
		return file_get_contents($this->file);
//		return $this->template->__toString();
	}
}
?>
