<?php
define('LIB_ROOT', dirname(__FILE__));
require_once LIB_ROOT.DS."Leno".DS."App.class.php";
use \Leno\App;
$app = App::instance();
$app->dispatcher->dispatch();
?>
