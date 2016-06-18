<?php
namespace Leno\Database;

use \Leno\Database\Adapter;
use \Leno\Database\Constraint\Foreign;
use \Leno\Database\Constraint\Unique;
use \Leno\Database\Constraint\Primary;

class Table
{
    protected $name;

    protected $db_info;

    /**
     * struct = [
     *      'sample' => [
     *          'type' => '', 
     *          'default' => null, 
     *          'null' => null, 
     *          'extra' => null
     *      ],
     * ];
     */
    protected $fields = [];

    protected $unique_keys = false;

    protected $foreign_keys = false;

    protected $indexes = false;

    protected $primary_key = false;

    public function __construct($name, $fields = [])
    {
        $this->name = $name;
        $this->fields = array_merge($this->fields, $fields);
    }

    public function getName()
    {
        return self::getAdapter()->keyQuote($this->name);
    }

    public function setUniqueKeys($unique_keys)
    {
        $this->unique_keys = $unique_keys;
        return $this;
    }

    public function setForeignKeys($foreign_key)
    {
        $this->foreign_keys = $foreign_key;
        return $this;
    }

    public function setPrimaryKey($primary_key)
    {
        $this->primary_key = $primary_key;
        return $this;
    }

    public function save()
    {
        $dbInfo = self::getAdapter()->describeColumns($this->name);
        self::getAdapter()->beginTransaction();
        try {
            if (empty($dbInfo)) {
                $this->addTable();
            } else {
                $this->alterTable();
            }
            is_string($this->primary_key) && (new Primary($this->name, $this->primary_key))->save();
            is_array($this->unique_keys) && (new Unique($this->name, $this->unique_keys))->save();
            is_array($this->foreign_keys) && (new Foreign($this->name, $this->foreign_keys))->save();
            self::getAdapter()->commitTransaction();
        } catch (\Exception $ex) {
            self::getAdapter()->rollback();
            throw $ex;
        }
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

    protected function alterTable()
    {
        $dbInfo = self::getAdapter()->describeColumns($this->name);
        $add = [];
        $alter = [];
        foreach($this->fields as $field => $attr) {
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
        $sql = sprintf('ALTER TABLE %s %s', $this->getName(),
            implode(', ', $fixed_part)
        );
        $adapter = self::getAdapter();
        return $adapter->execute($sql);
    }

    protected function addTable()
    {
        $tmp = 'CREATE TABLE %s (%s)';
        $fields = [];
        foreach($this->fields as $field => $attr) {
            $fields[] = $this->getExprOfField($field, $attr);
        }
        $sql = sprintf($tmp, $this->getName(), implode(', ', $fields));
        $adapter = self::getAdapter();
        return $adapter->execute($sql);
    }

    private function isFieldEqual($field1, $field2)
    {
        if($field1['type'] !== $field2['type']) {
            return false;
        }
        $null1 = $field1['is_nullable'] ?? false;
        $null2 = $field2['is_nullable'] ?? false;
        if($null1 !== $null2) {
            return false;
        } 
        $dft1 = $field1['default'] ?? false;
        $dft2 = $field2['default'] ?? false;
        if($dft1 != $dft2) {
            return false;
        }
        return true;
    }

    private function handleAdd($add_set)
    {
        $ret = [];
        foreach($add_set as $field => $attr) {
            $ret[] = sprintf('ADD COLUMN %s', $this->getExprOfField($field, $attr));
        }
        return implode(', ', $ret);
    }

    private function handleAlter($alter_set)
    {
        $ret = [];
        foreach($alter_set as $field => $attr) {
            $full_name = self::getAdapter()->keyQuote($field);
            $ret[] = sprintf('CHANGE %s %s', $full_name, 
                $this->getExprOfField($field, $attr)
            );
        }
        return implode(', ', $ret);
    }

    private function handleRemove($remove_set)
    {
        $ret = [];
        foreach($remove_set as $field => $attr) {
            $ret[] = sprintf('DROP %s', self::getAdapter()->keyQuote($field));
        }
        return implode(', ', $ret);
    }

    private function getExprOfField($field, $attr)
    {
        $field_expr = self::getAdapter()->keyQuote($field) .' '. $attr['type'];
        if ($attr['is_nullable'] ?? false) {
            $field_expr .= ' NULL';   
        } else {
            $field_expr .= ' NOT NULL';   
        }
        if(isset($attr['default'])) {
            $field_expr .= ' DEFAULT \''.$attr['default'].'\'';
        }
        return $field_expr;
    }

    public static function getAdapter()
    {
        return Adapter::get();
    }
}
