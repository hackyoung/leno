<?php
namespace Leno\Model;

class FieldManager {

	private $_model;

	private $_fields;

	public function __construct($model) {
		$this->_model = $model;
	}

	public function is($fieldname) {
		if(empty($this->_fields)) {
			$this->_fields = $this->_model->query(
				'show columns from ' . $this->_model->table()
			);
		}
		foreach($this->_fields as $field) {
			if($field['Field'] == $fieldname) {
				return $field;
			}
		}
		return false;
	}

	public function typeMatch($fieldname, $type) {
		$field = $this->is($fieldname);
		if($field && $field['Type'] == $type) {
			return true;
		}
		return false;
	}

	public function delete($field) {
		if(!$this->is($field)) {
			return true;
		}
		$sql = 'ALTER TABLE `' . $this->_model->table() .'`';
		$sql .= ' DROP COLUMN '.$field;
		return $this->_model->exec($sql);
	}

	public function add($field) {
		if(gettype($field) !== 'array' || $this->is($field['name'])) {
			return false;
		}
		$sql = 'ALTER TABLE `'.$this->_model->table().'`';
		$sql .= ' ADD COLUMN '.$field['name'];
		$sql .= ' '.$field['type'];
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
			if(gettype($field['default']) == 'string') {
				$sql .= " DEFAULT '" .$field['default'] ."' ";
			} else {
				$sql .= ' DEFAULT ' .$field['default'] .' ';
			}
		}
		return $this->_model->exec($sql);
	}

	public function update($fieldname, $field) {
		if(!$this->is($fieldname)) {
			return $this->add($field);
		}
		$sql = 'ALTER TABLE `'.$this->_model->table().'`';
		$sql .= ' CHANGE '.$fieldname . ' ' . $field['name'];
		$sql .= ' '.$field['type'];

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
			if(gettype($field['default']) == 'string') {
				$sql .= " DEFAULT '" .$field['default'] ."' ";
			} else {
				$sql .= ' DEFAULT ' .$field['default'] .' ';
			}
		}
		return $this->_model->exec($sql);
	}
}
?>
