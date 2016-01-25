<?php
namespace Leno\View;
use \Leno\App;
use \Leno\Cacher;
use \Leno\Debugger;

App::uses('Cacher', 'Leno');
App::uses('View', 'Leno.View');
/*
 * @name LTemplate
 * @description 模板
 */
class Template {

	const suffix = '.cache.php';

	protected $view;

	protected $cacher;

	protected $tag = array(
		// 继承
		'extend'=>'/\<extend.*\>/U',
		// 继承结束标签
		'extend_end'=>'/\<\/extend.*\>/U',
		// 子类实现的标签
		'child'=>'/\<child.*?\>/U',
		// 实现一个预定义child
		'implement'=>'/\<implement.*\>/U',
		// 实现结束标签
		'implement_end'=>'/\<\/implement\>/U',
		// 列表标签
		'llist'=>'/\<llist.*\>/U',
		// 列表标签结束
		'llist_end'=>'/\<\/llist.*\>/U',
		// 变量
		'var'=>'/.*\{\$.*\}.*/', // 如{$hello.world.world}
		// 类静态变量
		'classattr'=>'/.*\{\%.*\}.*/', // 如{%Hello.w} 生成 Hello::$w
		// 对象的属性
		'objectattr'=>'/.*\{\&.*\}.*/', // 如{&view.w} 生成view->w
		// 类常数
		'classconst'=>'/.*\{\!.*\}.*/', // 如{!Hello.w} 生成 Hello::w
		'function'=>'/\.*\{\|.*\}.*/', // 执行方法
		// 等于标签
		'eq'=>'/\<eq.*\>/U',
		// 不等于标签
		'neq'=>'/\<neq.*\>/U',
		// 在列表中标签
		'in'=>'/\<in\s.*\>/U',
		// 变量为空
		'empty'=>'/\<empty.*\>/U',
		// 变量为空
		't'=>'/\<t.*\>/U',
		// 变量不为空
		'notempty'=>'/\<notempty.*\>/U',
		// 不在列表中标签
		'nin'=>'/\<nin.*\>/U',
		'in_end'=>'/\<\/in\s.*\>/U',
		'nin_end'=>'/\<\/nin.*\>/U',
		'eq_end'=>'/\<\/eq.*\>/U',
		'neq_end'=>'/\<\/neq.*\>/U',
		'empty_end'=>'/\<\/empty.*\>/U',
		'notempty_end'=>'/\<\/notempty.*\>/U',
		// 加载子View标签
		'view'=>'/\<view.*\>/',
		// dump变量,调试使用
		'dump'=>'/\<dump.*\>/'
	);

	protected $extend_stack = array();

	public function __construct($view) {
		$this->view = $view;
		$file = $this->view->getFile();
		$cache = md5($file) . self::suffix;
		$this->cacher = new Cacher($cache);
	}

	/*
	 * @name pass1
	 * @description 编译模板需要执行两遍，这是第一遍，其作用是将所有需要解析的标签放在一行，第二遍仅仅替换一行的标签即可,目前第一遍未实现，所以用户必须保证所有待解析的标签在一行
	 */
	public function pass1() {
		$file = $this->view->getFile();
		$content = file_get_contents($file);
		return $content;
	}

	/*
	 * @name pass2
	 * @description 编译模板的第二遍，替换标签生成
	 */
	public function pass2() {
		$file = $this->cacher->getFile(); 
		$fp = fopen($file, 'r');
		$content = '';
		while($line = fgets($fp)) {
			$this->state = false;
			foreach($this->tag as $stat=>$reg) {
				if(preg_match($reg, $line)) {
					$this->state = $stat;
					switch($stat) {
						case 'extend':
							$resultLine = $this->parseExtend($line);
							break;
						case 'extend_end':
							$resultLine = $this->parseExtendEnd($line);
							break;
						case 'implement':
							$resultLine = $this->parseImplement($line);
							break;
						case 'implement_end':
							$resultLine = $this->parseImplementEnd($line);
							break;
						case 'var':
							$resultLine = $this->parseVar($line);
							break;
						case 'child':
							$resultLine = $this->parseChild($line);
							break;
						case 'llist':
							$resultLine = $this->parseLlist($line);
							break;
						case 'eq':
							$resultLine = $this->parseEq($line);
							break;
						case 'neq':
							$resultLine = $this->parseNeq($line);
							break;
						case 'in':
							$resultLine = $this->parseIn($line);
							break;
						case 'nin':
							$resultLine = $this->parseNin($line);
							break;
						case 'view':
							$resultLine = $this->parseView($line);
							break;
						case 'dump':
							$resultLine = $this->parseDump($line);
							break;
						case 'classattr':
							$resultLine = $this->parseClassAttr($line);
							break;
						case 'function':
							$resultLine = $this->parseFunction($line);
							break;
						case 'classconst':
							$resultLine = $this->parseClassConst($line);
							break;
						case 'objectattr':
							$resultLine = $this->parseObjectAttr($line);
							break;
						case 'empty':
							$resultLine = $this->parseEmpty($line);
							break;
						case 'notempty':
							$resultLine = $this->parseNotempty($line);
							break;
						case 't':
							$resultLine = $this->parseT($line);
							break;
						case 'llist_end':
						case 't_end':
						case 'empty_end':
						case 'notempty_end':
						case 'eq_end':
						case 'neq_end':
						case 'in_end':
						case 'nin_end':
							$resultLine = $this->parseEnd($line);
							break;
					}
				}
			}
			if($resultLine == null) {
				$resultLine = $line;
			}
			$content .= $resultLine;
			$resultLine = null;
		}
		fclose($fp);
		return $content;
	}

	protected function parseT($line) {
		$val = $this->parseAttr('value', $line);
		return preg_replace('/<t.*\>/U', $val, $line);
	}

	protected function parseDump($line) {
		$name = $this->parseAttr('name', $line);
		$const = $this->parseAttr('const', $line);
		$ret = '<?php \Leno\Debugger::dump(';
		if($const == 'true') {
			$ret .= '"'.$name.'"); ?>'."\n";
		} else {
			$ret .= $this->varString($name) . '); ?>' . "\n";
		}
		return $ret;
	}

	protected function parseEmpty($line) {
		$name = $this->parseAttr('name', $line);
		return '<?php if(empty('.$this->varString($name).')) { ?>'."\n";
	}

	protected function parseNotempty($line) {
		$name = $this->parseAttr('name', $line);
		return '<?php if(!empty('.$this->varString($name).')) { ?>'."\n";
	}

	protected function parseView($line) {
		$name = $this->parseAttr('name', $line);
		$data = $this->parseAttr('data', $line);
		$extend_data = $this->parseAttr('extend_data', $line);

		$ret = '<?php $this->view("v", new \Leno\View\View("'.$name.'"';
		if(!empty($data)) {
			$ret .= ', '.$this->varString($data);
		}
		$ret .= ')';
		if($extend_data == 'true') {
			$ret .= ', true';
		}
		$ret .= ') ?>'."\n";
		$ret .= '<?php $this->e("v")->display(); ?>'."\n";
		return $ret;
	}

	protected function parseEq($line) {
		return $this->parse_N_Eq($line, '==');
	}

	protected function parseNeq($line) {
		return $this->parse_N_Eq($line, '!=');
	}

	protected function parse_N_Eq($line, $f) {
		$n = $this->parseAttr('name', $line);
		$v = $this->parseAttr('value', $line);
		$d='<?php if('.$this->varString($n).$f.'"'.$v.'") { ?>';
		return $d . "\n";
	}

	protected function parseIn($line) {
		return $this->parse_N_In($line);
	}

	protected function parseNin($line) {
		return $this->parse_N_In($line, false);
	}

	protected function parse_N_In($line, $in=true) {
		$n = $this->parseAttr('name', $line);
		$v = $this->parseAttr('value', $line);
		if($in) {
			$e = '';
		} else {
			$e = '!';
		}
		$d='<?php if('.$e.'in_array("'.$n.'", '.$this->varString($v).')) { ?>';
		return $d . "\n";
	}

	protected function parseExtend($line) {
		$name = $this->parseAttr('name', $line);
		array_push($this->extend_stack, $name);
		return "\n";
	}

	protected function parseExtendEnd($line) {
		$name = array_pop($this->extend_stack);
		return '<?php $this->extend("'.$name.'"); ?>'."\n";
	}

	protected function parseImplement($line) {
		$name = $this->parseAttr('name', $line);
		return '<?php $this->start("'.$name.'"); ?>'."\n";
	}

	protected function parseImplementEnd($line) {
		return '<?php $this->end(); ?>'."\n";
	}

	protected function parseLlist($line) {
		$name = $this->parseAttr('name', $line);
		$id = $this->parseAttr('id', $line);
		$varName = $this->varString($name);
		$ret = '<?php if(gettype('.$varName.') != "array") { '.$varName.' = array(); } ?>'."\n";
		$ret .= '<?php foreach('.$varName.
					' as '.$this->varString($id).') { ?>'."\n";
		return $ret;
	}

	protected function parseEnd($line) {
		return '<?php } ?>'."\n";
	}

	protected function parseVar($line) {
		preg_match('/\{\$.*\}/U', $line, $attrarr);
		$var = preg_replace('/[\{\}\$]/', '', $attrarr[0]);
		$v = $this->varString($var);
		$v = '<?php echo '.$v.'; ?>';
		return preg_replace('/\{\$.*\}/U', $v, $line);
	}

	protected function parseFunction($line) {
		preg_match('/\{\|.*\}/U', $line, $attrarr);
		$var = preg_replace('/[\{\}\|]/', '', $attrarr[0]);
		$v = '<?php echo '.$var.'; ?>';
		return preg_replace('/\{\|.*\}/U', $v, $line);
	}

	protected function parseClassConst($line) {
		preg_match('/\{\!.*\}/U', $line, $attrarr);
		$var = $this->classConst(
			preg_replace('/[\{\}\!]/', '', $attrarr[0])
		);
		$v = '<?php echo '.$var.'; ?>';
		return preg_replace('/\{\!.*\}/U', $v, $line);
	}

	protected function parseClassAttr($line) {
		preg_match('/\{\%.*\}/U', $line, $attrarr);
		$var = $this->classAttr(
			preg_replace('/[\{\}\%]/', '', $attrarr[0])
		);
		$v = '<?php echo '.$var.'; ?>';
		return preg_replace('/\{\%.*\}/U', $v, $line);
	}

	protected function parseObjectAttr($line) {
		preg_match('/\{\&.*\}/U', $line, $attrarr);
		$var = $this->objectAttr(
			preg_replace('/[\{\}\&]/', '', $attrarr[0])
		);
		$v = '<?php echo '.$var.'; ?>';
		return preg_replace('/\{\&.*\}/U', $v, $line);
	}

	protected function varString($var) {
		$vararr = explode('.', $var);
		$v = '$'.$vararr[0];
		array_splice($vararr, 0, 1);
		foreach($vararr as $val) {
			$v .= '["'.$val.'"]';
		}
		return $v;
	}

	protected function objectAttr($var) {
		$vararr = explode('.', $var);
		$v = '$'.$vararr[0];
		array_splice($vararr, 0, 1);
		foreach($vararr as $val) {
			$v .= '->'.$val;
		}
		return $v;
	}

	protected function classAttr($var) {
		$vararr = explode('.', $var);
		$v = $vararr[0];
		array_splice($vararr, 0, 1);
		foreach($vararr as $val) {
			$v .= '::$'.$val;
		}
		return $v;
	}

	protected function classConst($var) {
		$vararr = explode('.', $var);
		$v = $vararr[0];
		array_splice($vararr, 0, 1);
		foreach($vararr as $val) {
			$v .= '::'.$val;
		}
		return $v;
	}

	protected function parseChild($line) {
		$name = $this->parseAttr('name', $line);
		return '<?php echo '.$this->varString($name).'; ?>'."\n";
	}

	protected function parseAttr($attr, $line) {
		preg_match(
			'/\s+'.$attr.'\=[\'\"].{1,}[\'\"]/U',
			$line, $attrarr
		);
		$att = preg_replace('/'.$attr.'=/', '', $attrarr[0]);
		return preg_replace('/[\'\"\s]/', '', $att);
	}

	public function cache($content) {
		$this->cacher->save($content);
	}

	public function display() {
		$viewfile = $this->view->getFile();
		$cache = $this->cacher->getFile();
		if(!is_file($cache) || filemtime($cache) <= filemtime($viewfile)) {
			$content = $this->pass1();
			$this->cache($content);
			$content = $this->pass2();
			$this->cache($content);
		}
		return $cache;
	}

	public function __toString() {
		$cache = $this->display();
		return file_get_contents($cache);
	}
}
?>
