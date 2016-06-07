<?php
namespace Leno\Core;

interface TypeStorage
{
    public function toStore($value);

    public function fromStore($store);
}
