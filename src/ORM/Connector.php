<?php
namespace Leno\ORM;

use \Leno\Configure;

class Connector
{
    use \Leno\Traits\Setter; 

    protected $label;

    protected $port;

    protected $host;

    protected $username;

    protected $db;

    protected $user;

    protected $password;

    protected $options;

    protected static $executor_map = [
        'mysql' => '\\Leno\\ORM\\Adapter\\Mysql\\Executor',
        'pgsql' => '\\Leno\\ORM\\Adapter\\Pgsql\\Executor',
    ];

    public function getExecutor()
    {
        $class = self::getExecutorClass($this->label);
        if(!$class) {
            throw new \Leno\Exception('unknown database label：'.$this->label);
        }
        $dsn = $this->label . ':' . implode(';', [
            'dbname='. $this->db,
            'port='. $this->port,
            'host='. $this->host,
        ]);
        try {
            return new $class($dsn, $this->user, $this->password, $this->options);
        } catch(\Exception $ex) {
            throw new \Leno\Exception('connect database error:'.$ex->getMessage());
        }
    }

    public static function get($label = null)
    {
        $label = $label ?? Configure::read('label') ?? 'mysql';
        $Executor = self::getExecutorClass($label);

        $host = Configure::read('host') ?? 'localhost';
        $db = Configure::read('db') ?? 'test_db';
        $port = Configure::read('port') ?? $Executor::DFT_PORT;
        $password = Configure::read('password') ?? null;
        $options = Configure::read('options') ?? [];
        $user = Configure::read('user') ?? 'root';

        return (new self)->setName($user)->setLabel($label)
            ->setPassword($password)->setHost($host)
            ->setPort($port)->setDb($db)
            ->setOptions($options)->getExecutor();
    }

    public static function getExecutorClass($label)
    {
        if(!isset(self::$executor_map[$label])) {
            return false;
        }
        return self::$executor_map[$label];
    }
}
