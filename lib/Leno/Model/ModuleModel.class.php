<?php
App::uses('Core/Model', 'Model');

class ModuleModel extends Model {
	protected $_table = 'sys_module';
	protected $_fields = array(
		'module_id'=>array(
			'type'=>'int',
			'primary_key'=>true,
			'auto_increment'=>true,
			'comment'=>'用户模块ID'
		),
		'module_name'=>array(
			'type'=>'nvarchar(32)',
			'null'=>false
		),
		'module_label'=>array(
			'type'=>'nvarchar(64)'
		),
		'module_description'=>array(
			'type'=>'nvarchar(256)'
		)
	);
}
?>
