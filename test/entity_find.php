<?php

require_once __DIR__ . '/boot.php';

$entity = \Test\Model\Entity::find('0d56fc94-4793-cbb2-9355-e2f4156d7c37');

var_dump(\Leno\DataMapper\Row::selector('user')->getSql());
var_dump($entity->getName());
var_dump($entity->getAge());
var_dump($entity->getUserId());
var_dump($entity->getHomePage());
var_dump($entity->getCreated());
