<?php
return array(
/**
 * 数据库配置
 */
	'DB_DSN'=>'mysql:dbname=hello;host=localhost',
	'DB_USER'=>'',
	'DB_PASSWORD'=>'',
	'DB_PERSISTENT'=>true,
	'DB_PREFIX'=>'leno',

	// 临时文件夹的根，他们包括log文件，缓存文件，debug记录
	'TMP'=>ROOT.DS.'tmp',
	// View的根目录
	'VIEW_ROOT'=>APP_ROOT.DS.'View',
	// 通用的View目录名
	'VIEW_COMMON'=>'common',
	// 默认的View主题
	'VIEW_THEME'=>'default',
	// 默认的控制器
	'DFT_CONTROLLER'=>'Home',
	// 默认的方法
	'DFT_ACTION'=>'index',
	// 静态文件的根
	'WEB_ROOT'=>'webroot',
	'DEBUG'=>true
);
?>
