<?php

require_once __DIR__ . '/boot.php';
/*
$city = new \Test\Model\City;
$city->setName('重庆');

$user = new \Test\Model\Entity;

$user->setCity($city)
    ->setName('hello')
    ->setAge(25)
    ->setHomePage('hello/world')
    ->setCreated(new \Datetime)
    ->save();
 */

$user = \Test\Model\Entity::find('b903bd69-eee8-de17-686f-7ae6c3f27a92');
var_dump(json_encode($user->getCity()));

// var_dump($user);
var_dump(json_encode($user));

