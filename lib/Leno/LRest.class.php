<?php
namespace Leno;

class LRest {

	private $_url;

	private $_curl;

	private $_params = array();

	public function __construct($url) {
		$this->_url = $url;
		$this->open();
	}

	public function setOpt($curlopt, $val) {
		curl_setopt($this->_curl, $curlopt, $val);
		return $this;
	}

	public function addParam($key, $val) {
		$this->_params[$key] = $val;
		return $this;
	}

	public function mergeParam($params) {
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}

	public function delParam($key) {
		if($key == null) {
			return false;
		}
		unset($this->_params[$key]);
	}

	public function post($json=true) {

		$curl = $this->_curl;

		// 设置提交的url
		curl_setopt($curl, CURLOPT_URL, $this->_url);

		// 设置为post提交
		curl_setopt($curl, CURLOPT_POST, 1);

		// 启用时会将头文件的信息作为数据流输出关闭
		curl_setopt($curl, CURLOPT_HEADER, 0);

		// 返回原生的（Raw）输出
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		// 设置post数据
		curl_setopt(
			$curl, CURLOPT_POSTFIELDS, http_build_query($this->_params)
		);
		// echo $this->_url;die;		
		$data = curl_exec($curl);
		curl_close($curl);

		if($json) {
			return json_decode($data,1);
		}
		return $data;
	}

	public function get($json=true) {
		$params = array();
		foreach($this->_params as $k=>$v) {
			$params[] = $k.'='.$v;
		}
		$url_arr = array(
			$this->_url,
			implode('&', $params)
		);
		if(preg_match('/\?/', $this->_url)) {
			$url = implode('&', $url_arr);
		} else {
			$url = implode('?', $url_arr);
		}
//		die($url);
		$curl = $this->_curl;
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		$data = curl_exec($curl);
		curl_close($curl);
		if($json) {
			return json_decode($data, 1);
		}
		return $data;
	}

	/*
	 * filenames = array( 'filekey'=>'real_path' );
	 */
	public function postFile($filenames) {

		$curl = $this->_curl;

		$data = array();

		foreach($filenames as $k=>$filename) {
			try {
				$data[$k] = new CURLFile($filename);  
			} catch(Exception $e) {
				$data[$k] = '@'.$filename;
			}
		}

		curl_setopt($curl, CURLOPT_URL, $this->_url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$info = curl_exec($curl);

		curl_close($curl);

		return $info;
	}

	public function open() {
		$this->_curl = curl_init();
		return $this;
	}

	public function info() {
		return curl_getinfo($this->_curl);
	}

	public function close() {
		curl_close($this->_curl);
		return $this;
	}
}
?>
