<?php
namespace Leno\Model;
use \Leno\App;
use \Leno\Configure;
use \Leno\Debugger;
use \Leno\Exception\ModelException;
App::uses('TableManager', 'Leno.Model');
App::uses('FieldManager', 'Leno.Model');
App::uses('ModelException', 'Leno.Exception');
App::uses('LObject', 'Leno');

class Model extends \Leno\LObject {

	// 保存pdo对象
	public static $db;

	// 表管理对象
	public $tableManager;

	// 字段管理的对象
	public $fieldManager;

	// 表名
	protected $_table;

	// 表前缀
	private $_prefix;

	// 待查找或写入的字段名
	private $_field = array();

	// 查询时限制其长度
	private $_limit = '';

	// 关联的其他Model
	private $_left_join = array();

	// 条件
	protected $_where = array();

	// 排序
	private $_order = '';

	// 保存生成的sql语句
	private $_sql;

	// 分组的字段
	private $_group = '';

	// true: 仅仅返回sql, false: 执行sql
	private $_getSql = false;

	// 是否distinct
	private $_distinct = false;

	// 是否在取出数据时忽略前缀
	private $_drop_prefix = true;

	// 保存待更新的字段和值
	protected $_data = array();

	// 表的所有字段描述
	protected $_fields = array();

	// 字段的前缀
	protected $_field_prefix;

	// 执行sql语句的结果
	public $result = true;

	/*
	 * @name __construct
	 * @description 如果pdo对象为空，则读取配置，创建pdo对象，所有的Model共享一个PDO对象
	 * @param string table 子Model继承Model，可以通过两种形式指定表明，一种是从新声明table属性，第二种是构造函数提供该参数
	 */
	public function __construct($table=null) {
		// db为单例，所有的Model共享一个db
		if(self::$db == null) {
			$pdo = Configure::read('db_dsn');
			$user = Configure::read('db_user');
			$password = Configure::read('db_password');
			$persistent = Configure::read('db_persistent');
			self::$db = new \PDO( $pdo, $user, $password, array(
				\PDO::ATTR_PERSISTENT=>$persistent
			));
			self::$db->query('set names utf8');
		}
		if($table != null) {
			$this->_table = $table;
		}
		$this->_prefix = Configure::read('db_prefix');
		$this->tableManager = new TableManager($this);
		$this->fieldManager = new FieldManager($this);
	}

	/*
	 * @name group
	 * @description 分组查询
	 * @param 分组查询的字段
	 * @return Model
	 */
	public function group($group) {
		if(!preg_match('/`/', $group)) {
			$group = $this->getField($group);
		}
		$this->_group .= ' GROUP BY '.$group.' ';
		return $this;
	}

	/*
	 * @name field
	 * @description 初始化Model::field属性，该属性决定select将要返回的字段
	 * @param array _field select时要查询的字段
	 * @return Model
	 */
	public function field($_field) {
		$this->_field = $_field;
		return $this;
	}

	/*
	 * @name limit
	 * @description 初始化Model::limit属性
	 * @param int limit
	 * @return Model
	 */
	public function limit($limit) {
		$this->_limit = ' LIMIT ' . $limit;
		return $this;
	}

	/*
	 * @name page
	 * @description 对查询进行分页
	 * @param int ps 页的大小
	 * @param int pn 页号
	 * @return Model
	 */
	public function page($ps, $pn) {
		$begin = $ps*($pn - 1);
		$end = $ps;
		$this->_limit = ' LIMIT ' . $begin . ',' . $end;
		return $this;
	}

	/*
	 * @name order
	 * @description 对查询进行排序
	 * @param array order 如 array('id'=>'desc', 'created'=>'asc')
	 * @return Model
	 */
	public function order($order) {
		$tmp = ' ORDER BY ';
		foreach($order as $k=>$v) {
			$tmp .= $this->getField($v) . ' ' . $k . ', ';
			break;
		}
		$this->_order = $tmp;
		return $this;
	}

	/*
	 * @name where
	 * @description 查询/删除/更新的条件
	 * @param array where 
	 * @return Model
	 */
	public function where($where) {
		if(gettype($where) != 'array') {
			throw new ModelException('NOT SUPPORTED NO ARRAY');
		}
		$this->_where = $where;
		return $this;
	}

	// 不推荐使用，请使用relate代替
	public function left_join($table, $where) {
		return $this->relate($table, $where);
	}

	/*
	 * @name relate
	 * @description 关联其他模型
	 * @param Model model 待关联的其他模型名
	 * @param array on 关联条件
	 * @return Model
	 */
	public function relate($model, $on) {
		$t = $model->table();
		/*
		foreach($on as $k=>$v) {
			if(!preg_match('/\./', $v)) {
				$on[$k] = $table->getField($v);
			}
		}
		 */
		$this->_left_join[$t] = array(
			'model'=>$model,
			'where'=>$this->_handleWhere($on)
		);
		return $this;
	}

	/*
	 * @name data
	 * @description 待修改/添加的数据
	 * @param array data
	 * @return Model
	 */
	public function data($data=null) {
		if($data == null) {
			return $this->_data;
		}
		if(!isset($this->_data[0])) {
			$this->_data[] = $data;
		} else {
			$this->_data[0] = array_merge($this->_data[0], $data);
		}
		return $this;
	}

	private function _is($var) {
		return empty($var) ? false : true;
	}

	/*
	 * @name distinct
	 * @description 去重
	 */
	public function distinct() {
		$this->_distinct = true;
		return $this;
	}

	/*
	 * @name select
	 * @description
	 */
	public function select($err=true) {
		$sql = ' SELECT ';
		if($this->_distinct) {
			$sql .= ' DISTINCT ';
		}
		$sql .= $this->handleField();
		if(count($this->_left_join) > 0) {
			foreach($this->_left_join as $join) {
				$sql .= ', '. $join['model']->handleField();
			}
		}
		$sql .= ' FROM ' . $this->table() . ' ';
		if(count($this->_left_join) !== 0) {
			foreach($this->_left_join as $k=>$join) {
				$sql .= 'LEFT JOIN `'.$k. '` ON '.$join['where']. ' ';
			}
		}
		if($this->_is($this->_where)) {
			$sql .= ' WHERE ' . $this->_handleWhere();
		}
		if($this->_is($this->_group)) {
			$sql .= $this->_group;
		}
		if($this->_is($this->_order)) {
			$sql .= $this->_order;
		}
		if($this->_is($this->_limit)) {
			$sql .= $this->_limit;
		}
		$this->_sql = $sql;
		$this->query(null, $err);
		$this->_field = array();
		$this->_limit = '';
		$this->_order = '';
		$this->_group = '';
		$this->_where = array();
		$this->_data = array();
		$this->_distinct = array();
		$this->_left_join = array();
		$this->_drop_prefix = true;
		return $this->result;
	}

	public function find() {
		$this->limit(1)->select();
		if(count($this->result) > 0) {
			$this->result = $this->result[0];
		}
		return $this->result;
	}

	public function create($data=null, $err=true) {
		if(!empty($data)) {
			$this->data($data);
		}
		$sql = 'INSERT INTO `' . $this->table() . '` ';
		foreach($this->_data as $key=>$data) {
			$data = $this->_handleData($data);
			if($key == 0) {
				$sql .= '(' . implode(',', $data['field']) . ' ) ';
			}
			$sql .= ' VALUES( ' . implode(',', $data['value']) . ' ),';
		}
		$sql = substr($sql, 0, strlen($sql) - 1);
		$this->_sql = $sql;
		$this->exec(null, $err);
		$this->result = self::$db->lastInsertId();
		return $this->result;
	}

	public function update($data=null, $err=true) {
		$sql = 'UPDATE '. $this->table() . ' SET ';
		$this->_sql = $sql;
		if($data != null) {
			$this->_data = array();
			$this->data($data);
		}
		$data = $this->_handleData($this->_data[0]);
		$field = $data['field'];
		$value = $data['value'];
		$field_len = count($field);
		$value_len = count($value);
		for($i = 0; $i < $field_len; ++$i) {
			$sql .= $field[$i] . '=' . $value[$i];
			if($i !== $field_len - 1) {
				$sql .= ',';
			}
		}
		if($this->_is($this->_where)) {
			$sql .= ' WHERE ' . $this->_handleWhere();
		}
		$this->_sql = $sql;
		$ret = $this->exec(null, $err);
		$this->_data = array();
		$this->_field = array();
		$this->_where = array();
		return $ret;
	}

	public function delete($err=true, $param=array()) {
		$sql = 'DELETE FROM '. $this->table();
		if($this->_is($this->_where)) {
			$sql .= ' WHERE ' . $this->_handleWhere();
		}
		$this->_sql = $sql;
		return $this->exec(null, $err);
	}

	public function count($reset = false) {
		$sql = 'SELECT COUNT(*) as count';
		$sql .= ' FROM ' . $this->table() . ' ';
		if(count($this->_left_join) !== 0) {
			foreach($this->_left_join as $k=>$join) {
				$sql .= 'LEFT JOIN `'.$k. '` ON '.$join['where'];
			}
		}
		if($this->_is($this->_where)) {
			$sql .= ' WHERE ' . $this->_handleWhere();
		}
		$sql .= ' LIMIT 1';
		$this->_sql = $sql;
		$this->query();
		if($reset) {
			$this->_field = array();
			$this->_limit = '';
			$this->_order = '';
			$this->_group = '';
			$this->_data = array();
			$this->_where = array();
			$this->_left_join = array();
			$this->_drop_prefix = true;
		}
		return intval($this->result[0]['count']);
	}

	public function lastSql() {
		return $this->_sql;
	}

	public static function begin() {
		new Model();
		if(empty(self::$db)) {
			throw new Exception('DB NOT INITIAL');
		}
		if(self::$db->inTransaction()) {
			return true;
		}
		return self::$db->beginTransaction();
	}

	public static function end() {
		if(empty(self::$db)) {
			throw new Exception('DB NOT INITIAL');
		}
		if(self::$db->inTransaction()) {
			return self::$db->commit();
		}
		return true;
	}

	public function getSql($enable=true) {
		$this->_getSql = $enable;
	}

	public function exec($sql = null, $err=true) {
		if($sql == null) {
			$sql = $this->_sql;
		} else {
			$this->_sql = $sql;
		}
		if($this->_getSql) {
			return $this->_sql;
		}
		$this->result = self::$db->exec($this->_sql);
		if(!$this->right()) {
			if(Configure::read('debug')) {
				$errorInfo = self::$db->errorInfo();
				throw new ModelException($this, $this->_sql);
			} else {
				if($err) {
					$this->onError(self::$db->errorInfo(), $this->_sql);
				}
				$this->result =  false;
			}
		}
		$this->_limit = '';
		$this->_field = array();
		$this->_order = '';
		$this->_group = '';
		$this->_where = array();
		$this->_left_join = array();
		return $this->result;
	}

	public function dropPrefix($drop=true) {
		$this->_drop_prefix = $drop;
		return $this;
	}

	public function prefix() {
		$this->_drop_prefix = false;
		return $this;
	}

	public function query($sql = null, $err=true) {
		if($sql == null) {
			$sql = $this->_sql;
		} else {
			$this->_sql = $sql;
		}
		if($this->_getSql) {
			return $this->_sql;
		}
		$this->result = array();
		$ret = self::$db->query($this->_sql, \PDO::FETCH_ASSOC);
		if(!$this->right()) {
			if(Configure::read('debug')) {
				$errorInfo = self::$db->errorInfo();
				throw new Exception(
					I18n::text($errorInfo[2]. ':' . $this->_sql)
				);
			} else {
				if($err) {
					$this->onError(self::$db->errorInfo(), $this->_sql);
				}
				$this->result = false;
			}
		}
		if(gettype($ret) == 'object') {
			foreach($ret as $row) {
				$this->result[] = $row;
			}
		}
		return $this->result;
	}

	public function right() {
		if(intval(self::$db->errorCode()) != 0) {
			return false;
		}
		return true;
	}

	public function fields() {
		return $this->_fields;
	}

	public function f($f) {
		if(!empty($this->_field_prefix)) {
			$f = $this->_field_prefix . '_' . $f;
		}
		return $f;
	}

	public function getField($field, $alias=null, $table=null) {
		if($table == null) {
			$table = $this->_table;
		}
		if($this->_field_prefix) {
			$field = $this->_field_prefix . '_' . $field;
		}
		$field = $this->table($table) .'.`'. $field.'`';
		if($alias) {
			$field .= " AS '" . $alias . "'";
		}
		return $field;
	}

	public function table($_t = null) {
		if($_t == null) {
			$_t = $this->_table;
		}
		$table = $this->_prefix . '_' . $_t;
		if(Configure::read('debug')) {
			$table .= '_test';
		}
		return $table;
	}

	public function build() {
		if(empty($this->_table)) {
			return;
		}
		if($this->tableManager->is()) {
			foreach($this->_fields as $k=>$field) {
				if($this->_field_prefix) {
					$k = $this->_field_prefix . '_' . $k;
				}
				if(!$this->fieldManager->typeMatch($k, $field['type'])) {
					if(isset($field['primary_key']) &&
											$field['primary_key']) {
						continue;
					}
					$field['name'] = $k;
					$this->fieldManager->update($k, $field);
				}
			}
		} else {
			$this->tableManager->create();
		}
	}

	protected function _handleWhere($where = null) {
		if($where == null) {
			$where = $this->_where;
		}
		$wheresql = '';
		if(gettype($where) == 'array' && count($where) > 0) {
			$tmp = array();
			foreach($where as $k=>$v) {
				if(!preg_match('/\./', $k) > 0) {
					$k = $this->getField($k);
				}
				if(gettype($v) == 'string') {
					$tmp[] = $k .'='. $this->__quote($v);
				} else if(gettype($v) == 'array') {
					$arr = array();
					foreach($v as $sk=>$sv) {
						switch($sk) {
							case 'gt':
								$arr[] = $k.">".$this->__quote($sv);
								break;
							case 'lt':
								$arr[] = $k."<".$this->__quote($sv);
								break;
							case 'le':
								$arr[] = $k."<=".$this->__quote($sv);
								break;
							case 'ge':
								$arr[] = $k.">=".$this->__quote($sv);
								break;
							case 'ne':
								$arr[] = $k."!=".$this->__quote($sv);
								break;
							case 'in':
								$arr[] = "FIND_IN_SET(".$k.",'".$sv."')";
								break;
							case 'like':
								$arr[] = $k." LIKE '%".$sv."%'";
								break;
							default: {
								throw new Exception(
									'NOT KNOW KEY "'.$sk.'"'
								);
							}
						}
					}
					$tmp[] = implode(' AND ', $arr);
				} else if($v == null) {
					$tmp[] = $k ."=''";
				} else {
					$tmp[] = $k ."=". $v;
				
				}
			}
			$wheresql = join(' AND ', $tmp);
		} else {
			if($where == '' || count($where) == 0) {
				$wheresql = 1;
			}
			$wheresql = $where;
		}
		return $wheresql;
	}

	public function handleField() {
		$field = array();
		if($this->_is($this->_field)) {
			
			foreach($this->_field as $k=>$v) {
				$as = !preg_match('/^\d{1,}$/', $k);
				if($as) {
					$field[] = $this->getField($k, $v);
				} else {
					if($this->_drop_prefix) {
						$field[] = $this->getField($v, $v);
					} else {
						$field[] = $this->getField($v);
					}
				}
			}
		} else {
			foreach($this->_fields as $k=>$f) {
				if($this->_drop_prefix) {
					$field[] = $this->getField($k, $k);
				} else {
					$field[] = $this->getField($k);
				}
			}
		}
		return implode(',', $field);
	}

	private function __quote($str) {
		if(!preg_match('/\`/', $str)) {
			$str = self::$db->quote($str);
		}
		return $str;
	}

	protected function _handleData($data) {
//		$data = $this->_data;
		$field = array();
		$value = array();
		foreach($data as $k=>$v) {
			$k = $this->f($k);
			$field[] = '`'.$k.'`';
			if($v === '') {
				$v = '\'\'';
			} else if($v === false) {
				$v = 0;
			} else if($v === null) {
				$v = 'null';
			} else {
				$v = $this->__quote($v);
			}
			$value[] = str_replace(',', '，', $v);
		}
		return array(
			'field'=>$field,
			'value'=>$value
		);
	}

	protected function onError($e, $sql) {
		return true;
	}

	/*
	 * @name init
	 * @description 创建\更新模型用到的数据表
	 */
	public static function init($dir=null) {
		if($dir == null) {
			$dir = APP_ROOT;
		}
		$directory = dir($dir);
		while($file = $directory->read()) {
			if($file == '.' || $file == '..') {
				continue;
			}
			$pathfile = $dir . DS . $file;
			if(is_dir($pathfile)) {
				self::init($pathfile);
			} else {
				if(preg_match('/\.class\.php$/', $file)) {
					require_once $pathfile;
					$class = str_replace(APP_ROOT.DS, '', $pathfile);
					$class = str_replace('.class.php', '', $class);
					$class = str_replace(DS, '\\', $class);
					$rc = new \ReflectionClass($class);
					if($rc->isAbstract()) {
						continue;
					}
					$m = $rc->newInstance();
					if($m instanceof self) {
						$m->build();
					}
				}
			}
		}
	}
}
?>
