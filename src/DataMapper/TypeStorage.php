<?php
namespace Leno\DataMapper;

interface TypeStorage
{
	public function toStore($value);

	public function fromStore($store);
}
