<?php
define('TEST_MVC', false);
require __DIR__ . '/boot.php';
/*
(new \Leno\ORM\Row\Creator('test'))->setHello('hello')
    ->setWorld('world')
    ->setAge(13)
    ->newRow()
    ->setHello('another hello')
    ->setWorld('another world')
    ->setAge(23)
    ->create();
 */
/**
$hh = (new \Leno\ORM\Row\Selector('world'));
$hello = (new \Leno\ORM\Row\Selector('test'));
$stmt = $hello->byEqHello('hello')
    ->byGtAge(14)
    ->join($hh->onEqName($hello->getFieldExpr('hello'))->byEqName('hello'))
    ->execute();

var_dump($stmt->fetchAll());
 */
$hello = (new \Leno\ORM\Row\Updator('test'))
    ->setHello('update hello')
    ->byEqAge(13)
    ->update();
