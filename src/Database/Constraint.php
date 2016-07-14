<?php
namespace Leno\Database;

use \Leno\Database\Adapter;

abstract class Constraint
{
    protected $table_name;

    protected $config;

    private $db_info;

    public function __construct($table_name, $config)
    {
        $this->table_name = $table_name;
        $this->config = $config;
    }

    public function save()
    {
        $adapter = self::getAdapter();
        if (!$this->db_info) {
            $this->db_info = $this->getFromDB();
        }
        $add = [];
        $remove = $this->db_info;
        foreach ($this->config as $key => $config) {
            if (!isset($remove[$key]) || !$this->equal($config, $remove[$key])) {
                $add[$key] = $config;
                continue;
            }
            unset($remove[$key]);
        }
        $this->doSave($add, $remove);
    }

    abstract protected function doSave($add, $remove);

    abstract protected function equal($con1, $con2) : bool;

    abstract protected function getFromDB();

    protected static function getAdapter() 
    {
        return Adapter::get();
    }
}
