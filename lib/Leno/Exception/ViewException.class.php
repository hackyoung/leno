<?php
namespace Leno\Exception;
use Leno\App;
App::uses('Exception', 'Leno.Exception');
class ViewException extends Exception {

	public function __construct($viewfile, $searchdir) {
	
	}
}
?>
