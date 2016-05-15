<?php
define('TEST_MVC', false);
require_once __DIR__ . "/boot.php";
use \Leno\ORM\Table;

$table = new Table('hello');

$table->setField('hello_id', [
    'type' => 'char(36)',
    'key' => 'primary key',
    'null' => 'NOT NULL',
])->setField('name', [
    'type' => 'varchar(32)',
    'null' => 'NOT NULL',
])->setField('description', [
    'type' => 'varchar(256)',
])->setField('deleted', [
    'type' => 'datetime',
])->setField('extra', [
    'type' => 'varchar(31)'
])->setField('extra_1', [
    'type' => 'varchar(33)'
])->unsetField('extra_1')
->save();
