<?php
namespace Leno\LException;
use Leno\App;
App::uses('LException', 'Leno.LException');
class ViewException extends LException {

	public function __construct($viewfile, $searchdir) {
	
	}
}
?>
