<?php
namespace Leno\ORM;

class Table
{
    protected $name;

    protected $newName;

    protected $db_info;

    /**
     * stract = [
     *      'sample' => ['type' => '', 'size' => null, 'default' => null, 'null' => null],
     *      'sample' => ['type' => '', 'size' => null, 'default' => null, 'null' => null],
     *      'sample' => ['type' => '', 'size' => null, 'default' => null, 'null' => null],
     * ];
     */
    protected $fields = [];

    protected $unique_keys = [];

    protected $foreign_key = [];

    protected $primary_key = [];

    public function __construct($name, $fields = [])
    {
        $this->name = $name;
        $this->fields = array_merge($this->fields, $fields);
    }

    public function setUniqueKeys($unique_keys)
    {
        $this->unique_keys = $unique_keys;
        return $this;
    }

    public function setForeignKeys($foreign_key)
    {
        $this->foreign_key = $foreign_key;
        return $this;
    }

    public function setPrimaryKeys($primary_key)
    {
        $this->primary_key = $primary_key;
        return $this;
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
        $this->fields[$column] = array_merge(
            $this->fields[$column] ?? [], $attr
        );
        return $this;
    }

    public function removeColumn($column)
    {
        if(isset($this->fields[$column])) {
            unset($this->fields[$column]);
        }
        return $this;
    }

    public function getDbAttr()
    {
        $adapter = self::getAdapter();
        return $adapter->getFieldsInfo($this->name);
    }

    protected function alterTable()
    {
        $dbInfo = $this->getDbAttr();
        $add = [];
        $remove = [];
        foreach($this->fields as $field=>$attr) {
            if(!isset($dbInfo[$field])) {
                $add[$field] = $attr;
                continue;
            }
            if(!$this->isFieldEqual($attr, $dbInfo[$attr])) {
                $alter[$field] = $attr;
                unset($dbInfo[$field]);
                continue;
            }
            unset($dbInfo[$field]);
        }
        $alter = $dbInfo;
    }

    private function isFieldEqual($field1, $field2)
    {
        foreach($field1 as $attr => $val) {
            if($val === $field2[$attr] ?? false) {
                unset($field2[$attr]);
                continue;
            }
            return false;
        }
        return count($field2) === 0;
    }

    private function handleAdd($add_set)
    {
        $sql_arr = ['ADD'];
        foreach($add_set as $field => $attr) {

        }
    }

    public static function getAdapter()
    {
        return \Leno\ORM\Adapter::get();
    }
}
