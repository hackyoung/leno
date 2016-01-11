<?php
App::uses('Core/Model', 'Model');

class ActionModel extends Model {

	protected $_table = 'sys_action';

	protected $_fields = array(
		'action_id'=>array(
			'type'=>'int',
			'primary_key'=>true,
			'auto_increment'=>true
		),
		'action_name'=>array(
			'type'=>'nvarchar(32)',
			'null'=>false
		),
		'action_label'=>array(
			'type'=>'nvarchar(32)'
		),
		'action_description'=>array(
			'type'=>'nvarchar(1024)'
		),
		'action_example'=>array(
			'type'=>'nvarchar(256)'
		),
		'action_access'=>array(
			'type'=>'nvarchar(32)'
		),
		'controller_id'=>array(
			'type'=>'int',
			'null'=>false
		)
	);

	public function getByControllerId($id) {
		return $this->where(array(
			'controller_id'=>$id
		))->select();
	}
}
?>
