<?php
App::uses('Core/Model', 'Model');

class ControllerModel extends Model {

	protected $_table = 'sys_controller';

	protected $_fields = array(
		'controller_id'=>array(
			'type'=>'int',
			'primary_key'=>true,
			'auto_increment'=>true
		),
		'controller_name'=>array(
			'type'=>'nvarchar(32)',
			'null'=>false
		),
		'controller_label'=>array(
			'type'=>'nvarchar(32)'
		),
		'controller_description'=>array(
			'type'=>'nvarchar(1024)'
		),
		'module_id'=>array(
			'type'=>'int',
			'null'=>false
		)
	);

	public function getByModuleId($id) {
		return $this->where(array(
			'module_id'=>$id
		))->select();
	}
}
?>
