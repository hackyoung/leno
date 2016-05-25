<?php
namespace Leno\ORM;

class Table
{
    protected $name;

    protected $newName;

    protected $db_info;

    /**
     * struct = [
     *      'sample' => [
     *          'type' => '', 
     *          'default' => null, 
     *          'null' => null, 
     *          'key' => null, 
     *          'extra' => null
     *      ],
     * ];
     */
    protected $fields = [];

    protected $sql;

    protected $unique_keys = [];

    protected $foreign_keys = [];

    protected $primary_key;

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

    public function setField($field, $attr)
    {
        $this->fields[$field] = array_merge(
            $this->fields[$field] ?? [], $attr
        );
        return $this;
    }

    public function unsetField($field)
    {
        if(isset($this->fields[$field])) {
            unset($this->fields[$field]);
        }
        return $this;
    }

    public function getDbAttr()
    {
        $adapter = self::getAdapter();
        return $adapter->getTableInfo($this);
    }

    public function lastSql()
    {
        return $this->sql;
    }

    protected function alterTable()
    {
        $dbInfo = $this->getDbAttr();
        $add = [];
        $alter = [];
        foreach($this->fields as $field=>$attr) {
            if(!isset($dbInfo[$field])) {
                $add[$field] = $attr;
                continue;
            }
            if(!$this->isFieldEqual($attr, $dbInfo[$field])) {
                $alter[$field] = $attr;
                unset($dbInfo[$field]);
                continue;
            }
            unset($dbInfo[$field]);
        }
        $remove = $dbInfo;
        if(empty($add) && empty($alter) && empty($remove)) {
            return true;
        }
        $fixed_part = [];
        if(!empty($add)) {
            $fixed_part[] = $this->handleAdd($add);
        }
        if(!empty($alter)) {
            $fixed_part[] = $this->handleAlter($alter);
        }
        if(!empty($remove)) {
            $fixed_part[] = $this->handleRemove($remove);
        }
        $this->sql = sprintf('ALTER TABLE %s %s', $this->name,
            implode(', ', $fixed_part)
        );
        $adapter = self::getAdapter();
        $result = $adapter->exec($this->sql);
        if($result === false) {
            throw new \Exception($adapter->errorInfo()[2]);
        }
        return $result;
    }

    protected function addTable()
    {
        $tmp = 'CREATE TABLE %s (%s)';
        $fields = [];
        foreach($this->fields as $field => $attr) {
            $fields[] = $field . ' ' .implode(' ', array_values($attr)) ;
        }
        $this->sql = sprintf($tmp, $this->name, implode(', ', $fields));
        $adapter = self::getAdapter();
        $result = $adapter->exec($this->sql);
        if(!$result) {
            throw new \Exception($adapter->errorInfo()[2]);
        }
        return $result;
    }

    private function isFieldEqual($field1, $field2)
    {
        if($field1['type'] !== $field2['type']) {
            return false;
        }
        $null1 = $field1['null'] ?? 'NULL';
        $null2 = $field2['null'] ?? 'NULL';
        if($null1 !== $null2) {
            return false;
        } 
        $dft1 = $field1['default'] ?? false;
        $dft2 = $field2['default'] ?? false;
        if($dft1 !== $dft2) {
            return false;
        }
        return true;
    }

    private function handleAdd($add_set)
    {
        $ret = [];
        foreach($add_set as $field => $attr) {
            $ret[] = sprintf('ADD COLUMN %s %s', $field, 
                implode(' ', array_values($attr))
            );
        }
        return implode(', ', $ret);
    }

    private function handleAlter($alter_set)
    {
        $ret = [];
        foreach($alter_set as $field => $attr) {
            $ret[] = sprintf('CHANGE %s %s %s', $field, $field,
                implode(' ', array_values($attr))
            );
        }
        return implode(', ', $ret);
    }

    private function handleRemove($remove_set)
    {
        $ret = [];
        foreach($remove_set as $field => $attr) {
            $ret[] = sprintf('DROP %s', $field);
        }
        return implode(', ', $ret);
    }

    public static function getAdapter()
    {
        return \Leno\ORM\Connector::get();
    }
}
