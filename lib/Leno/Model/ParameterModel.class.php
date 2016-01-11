<?php
App::uses('Core/Model', 'Model');

class ParameterModel extends Model {

	protected $_table = 'sys_parameter';

	protected $_fields = array(
		'parameter_id'=>array(
			'type'=>'int',
			'primary_key'=>true,
			'auto_increment'=>true
		),
		'parameter_type'=>array(
			'type'=>'nvarchar(32)'
		),
		'parameter_name'=>array(
			'type'=>'nvarchar(32)'
		),
		'parameter_description'=>array(
			'type'=>'nvarchar(512)'
		),
		'action_id'=>array(
			'type'=>'int',
			'null'=>false
		)
	);

	public function getByActionId($id) {
		return $this->where(array(
			'action_id'=>$id
		))->select();
	}
}
?>
