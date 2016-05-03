<?php

require_once __DIR__ . '/boot.php';

$entity = new \Test\Model\Entity;

$ret = $entity->setName('young')
	->setAge(13)
	->setCreated(new \Datetime)
	->setHomePage('/home/young')
	->save();

vac0407688-09a8-e2d1-ab66-c66f1e126a72r_dump(\Leno\DataMapper\Row::creator('user')->getSql());
