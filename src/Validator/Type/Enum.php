<?php
namespace Leno\Validator\Type;

class Enum extends \Leno\Validator\Type
{
    protected $val_list;

    protected $allow_empty = false;

    protected $required = true;

    public function __construct($val_list = [])
    {
        $this->val_list = $val_list;
    }

    public function check($val)
    {
        if(!parent::check($val)) {
            return false;
        }
        if(!in_array($val, $this->val_list)) {
            throw new \Exception($this->value_name . ' Not In ['.implode(',', $this->val_list).']');
        }
        return true;
    }
}
