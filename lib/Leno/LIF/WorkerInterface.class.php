<?php
namespace Leno\LIF;

interface WorkerInterface {

	/*
	 * @name param
	 * @description 获取从客户端请求的参数
	 * @param string key 参数名
	 * @param string msg 如果没有找到参数或者参数和reg不匹配，则提示错误
	 * @param string reg 验证参数的正则表达式，如果正则为空，则不验证
	 */
	public function param($key, $msg, $reg);

	/*
	 * @name response
	 * @description 返回给客户端信息
	 * @param int status 状态
	 * @param string msg 消息
	 * @param object data 有结构的数据
	 */
	public function info($status, $msg, $data);

}
?>
