<?php
namespace Leno\Database\Adapter;

use \Leno\Database\Adapter;

class PgsqlAdapter extends Adapter
{
    protected function quote(string $value) : string
    {
        return '"'.$value.'"';
    }

    protected function _describeTable(string $table_name)
    {
    }
}
