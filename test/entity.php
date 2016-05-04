<?php

require_once __DIR__ . '/boot.php';

$entity = new \Test\Model\Entity;

$ret = $entity->setName('young')
	->setAge(13)
	->setCreated(new \Datetime)
	->setHomePage('/home/young')
	->save();

var_dump(\Leno\DataMapper\Row::creator('user')->getSql());
