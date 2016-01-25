<?php
namespace Leno\Exception;

class Exception extends \Exception {

	public function __toString() {
		echo "<pre>";
		return parent::__toString();
	}
}
?>
