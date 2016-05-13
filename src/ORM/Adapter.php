<?php
namespace Leno\ORM;

use \Leno\Configure;

abstract class Adapter extends \PDO
{
    protected $label;

    protected $dft_port = 3306;

    public function __construct()
    {
        if(empty($this->label)) {
            throw new \Exception('You Miss A Label of PDO');
        }
        $dsn = $this->label . ':' . implode(';', [
            'dbname='.Configure::read('db'),
            'port='. (Configure::read('port') ?? $this->dft_port),
            'host='. (Configure::read('host') ?? 'localhost'),
        ]);
        $user = Configure::read('user') ?? null;
        $password = Configure::read('password') ?? null;
        $options = array_merge([
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ], Configure::read('options') ?? []);
        try {
            parent::__construct($dsn, $user, $password, $options);
        } catch(\Exception $e) {
            echo 'Connection DataBase failed: ' . $e->getMessage();
        }
    }
}
