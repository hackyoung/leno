<?php
namespace Leno\Validator\Type;

class Number extends \Leno\Validator\Type
{
    protected $max;

    protected $min;

    protected $regexp = '/-?\d+(\.\d+)?/';

    public function __construct($min = null, $max = null)
    {
        $this->max = $max;
        $this->min = $min;
    }

    public function check($val)
    {
        if(!parent::check($val)) {
            return true;
        }
        if(!preg_match($this->regexp, $val)) {
            throw new \Exception($this->value_name . ' Not A Number');
        }
        if($this->min !== null && (float)$val < $this->min) {
            throw new \Exception($this->value_name . ' Less Than '.$this->min);
        }
        if($this->max !== null && (float)$val > $this->max) {
            throw new \Exception($this->value_name . ' Greater Than '.$this->max);
        }
        return true;
    }
}
