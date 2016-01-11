<?php
App::uses('Core/Model', 'Model');

class ReturnModel extends Model {
	protected $_table = 'sys_return';
	protected $_fields = array(
		'return_id'=>array(
			'type'=>'int',
			'primary_key'=>true,
			'auto_increment'=>true
		),
		'return_type'=>array(
			'type'=>'nvarchar(16)',
			'null'=>false
		),
		'return_description'=>array(
			'type'=>'nvarchar(256)'
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
