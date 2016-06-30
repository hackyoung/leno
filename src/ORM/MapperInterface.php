<?php
namespace Leno\ORM;

use \Leno\ORM\DataInterface;

interface MapperInterface
{
    public function selectTable(string $table_name);

    public function insert (DataInterface $data);

    public function update (DataInterface $data);

    public function remove (DataInterface $data);

    public function find ($id);
}
