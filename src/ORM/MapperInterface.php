<?php
namespace Leno\ORM;

use \Leno\ORM\DataInterface;

interface MapperInterface
{
    public function insert (DataInterface $data);

    public function update (DataInterface $data);

    public function remove (DataInterface $data);

    public function find ($id);
}
