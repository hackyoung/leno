<?php
namespace Leno\Database\Adapter;

use \Leno\Database\Adapter;

class MysqlAdapter extends Adapter
{
    protected function quote(string $value) : string
    {
        return '`'.$value.'`';
    }
}
