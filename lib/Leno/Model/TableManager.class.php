<?php
namespace Leno\Model;

class TableManager {

	private $_model;

	public function __construct($model) {
		$this->_model = $model;
	}

	public function create() {
	
		$fields = $this->_model->fields();
		$table = $this->_model->table();
		if(count($fields) == 0 || $this->is()) {
			return false;
		}
		$sql = 'CREATE TABLE '. $table .'(';
		foreach($fields as $k=>$field) {
			$k = $this->_model->f($k);
			$sql .= $k . ' ' . $field['type'];
			if(!empty($field['auto_increment'])) {
				$sql .= ' auto_increment ';
			}
			if(!empty($field['primary_key'])) {
				$sql .= ' primary key ';
			}
			if(isset($field['null']) && !$field['null']) {
				$sql .= ' not null ';
			} else {
				$sql .= ' null ';
			}
			if(!empty($field['default'])) {
				$sql .= ' DEFAULT ' .$field['default'] .' ';
			}

			$sql .= ',';
		}
		$sql = substr($sql, 0, strlen($sql) - 1);
		$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8';
		return $this->_model->exec($sql);
	}

	public function drop() {
		$table = $this->_model->table();
		return $this->_model->exec('DROP TABLE `'.$table.'`');
	}

	public function is() {
		$ret = Model::$db->query('select * from '.$this->_model->table());
		$error = Model::$db->errorInfo();
		if($error[0] == '00000') {
			return true;
		}
		return false;
	}
}
?>
