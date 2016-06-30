<?php
namespace Leno\ORM;

use \Leno\Database\Row\Creator as RowCreator;
use \Leno\Database\Row\Updator as RowUpdator;
use \Leno\Database\Row\Deletor as RowDeletor;
use \Leno\Database\Row\Selector as RowSelector;

class Mapper implements MapperInterface
{
    private $table_name;

    public function insert(DataInterface $data)
    {
        $creator = new RowCreator($this->table_name);
        $dirty_data = $data->getDirty();
        foreach($dirty_data as $field => $value) {
            $creator->set($field, $value);
        }
        return $creator->create();
    }

    public function update(DataInterface $data)
    {
        $updator = new RowUpdator($this->table_name);
        $dirty_data = $data->getDirty();
        foreach($dirty_data as $field => $value) {
            $updator->set($field, $value);
        }
        foreach($data->id() as $field => $value) {
            $updator->by('eq', $field, $value);
            break;
        }
        return $updator->update();
    }

    public function deletor(DataInterface $data)
    {
        $deletor = new RowDeletor($this->table_name);
        foreach($data->id() as $field => $value) {
            $deletor->by('eq', $field, $value);
            break;
        }
        return $deletor->delete();
    }

    public function find($id)
    {
        $selector = new RowSelector($this->table_name);
        foreach($id as $field => $value) {
            $selector->by('eq', $field, $value);
        }
        return $selector->getOne();
    }

    public function selectTable($table_name)
    {
        $this->table_name = $table_name;
        return $this;
    }
}
