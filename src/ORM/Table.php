<?php
namespace Leno\ORM;

class Table
{
    protected $name;

    protected $newName;

    protected $attributes = [];


    public function __construct($name, $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function save()
    {
        $dbInfo = $this->getDbAttr();
        if($dbInfo === false) {
            return $this->addTable();
        }
        return $this->alterTable();
    }

    public function rename($name)
    {
        $this->newName = $name;
        return $this;
    }

    public function addColumn($column, $attr)
    {
        $this->attributes[$column] = array_merge(
            $this->attributes[$column] ?? [], $attr
        );
        return $this;
    }

    public function removeColumn($column)
    {
        if(isset($this->attributes[$column])) {
            unset($this->attributes[$column]);
        }
        return $this;
    }

    public function isDbSetColumn($column)
    {
        $dbInfo = $this->getDbAttr();
    }

    public function getDbAttr()
    {
    }

    protected function alterTable()
    {
        $dbInfo = $this->getDbAttr();
    }
}
