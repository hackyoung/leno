<?php
define('ROOT', dirname(__FILE__));

if(isset($_SERVER["HTTP_USER_AGENT"])) {
	if(preg_match("/win/", php_uname())) {
		define("DS", "\\");
	} else {
		define("DS", "/");
	}
} else {
	define("DS", "/");
}
require_once ROOT.DS."app".DS."index.php";
?>
