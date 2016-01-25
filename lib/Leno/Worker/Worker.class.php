<?php
namespace Leno\Worker;

class Worker {

	// 执行成功
	const S_SUCCESS = 100000;
	// 执行失败
	const S_ERROR = 100001;
	// 没有权限访问
	const S_PERMISSION = 100002;
	// 输入不合法
	const S_INPUT = 100003;
	// 文件大小不合法
	const S_FILE_SIZE = 100004;
	// 需要登录
	const S_NEED_LOGIN = 100005;
	// 其他
	const S_OTHER = 000000;
}
?>
