<?php
namespace Leno\Database;

use \Leno\Database\Adapter;

/**
 *
 */
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

    protected $sql;

    protected $unique_keys = [];

    protected $foreign_keys = [];

    protected $primary_key = [];

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
        $dbInfo = self::getAdapter()->describeTable($this->getName());
        if($dbInfo === false) {
            return $this->addTable();
        }
        return $this->alterTable();
        //self::getAdapter()->describeConstraint($this->getName());
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

    public function lastSql()
    {
        return $this->sql;
    }

    protected function alterTable()
    {
        $dbInfo = self::getAdapter()->describeTable($this->getName());
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
        $this->sql = sprintf('ALTER TABLE %s %s', $this->getName(),
            implode(', ', $fixed_part)
        );
        $adapter = self::getAdapter();
        return $adapter->execute($this->sql);
    }

    protected function addTable()
    {
        $tmp = 'CREATE TABLE %s (%s)';
        $fields = [];
        foreach($this->fields as $field => $attr) {
            $fields[] = $this->getExprOfField($field, $attr);
        }
        $this->sql = sprintf($tmp, $this->getName(), implode(', ', $fields));
        $adapter = self::getAdapter();
        return $adapter->execute($this->sql);
    }

    private function isFieldEqual($field1, $field2)
    {
        if($field1['type'] !== $field2['type']) {
            return false;
        }
        $null1 = $field1['null'] ?? 'NOT NULL';
        $null2 = $field2['null'] ?? 'NOT NULL';
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
        if(isset($attr['default'])) {
            $attr['default'] = 'DEFAULT \''.$attr['default'].'\'';
        }
        return self::getAdapter()->keyQuote($field) . ' ' . implode(' ', array_values($attr));
    }

    public static function getAdapter()
    {
        return Adapter::get();
    }
}
