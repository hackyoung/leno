<?php
namespace Leno\Validator;

interface TypeStorage
{
    public function toStore($value);

    public function fromStore($store);
}
