<?php
namespace Leno\DataMapper\Driver;
use \Leno\Configure;
class PdoDriver extends \PDO
{
    public function __construct()
    {
        $dsn = Configure::read('dsn');
        if(!$dsn) { 
            throw new \Exception('DSN Not Found');
        }
        $user = Configure::read('user') ?? null;
        $password = Configure::read('password') ?? null;
        $options = array_merge([
        ], Configure::read('options') ?? []);
        try {
            parent::__construct($dsn, $user, $password, $options);
        } catch(\Exception $e) {
            echo 'Connection DataBase failed: ' . $e->getMessage();
        }
    }
}
