<?php
require_once __DIR__ . '/boot.php';

$user = \Test\Model\Entity::find('b903bd69-eee8-de17-686f-7ae6c3f27a92');

var_dump($user->getData());
