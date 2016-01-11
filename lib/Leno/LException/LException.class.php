<?php
namespace Leno\LException;

class LException extends \Exception {

	public function __toString() {
		echo "<pre>";
		return parent::__toString();
	}
}
?>
