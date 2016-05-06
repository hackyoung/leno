<?php
namespace Leno\Validator\Type;

class Stringl extends \Leno\Validator\Type
{
    protected $max_length;

    protected $min_length;

    protected $regexp;

    public function __construct($regexp=null, $min_length=null, $max_length=null)
    {
        if($min_length !== null) {
            $this->min_length = $min_length;
        }
        if($max_length !== null) {
            $this->max_length = $max_length;
        }
        if($regexp !== null) {
            $this->regexp = $regexp;
        }
    }

    public function getMinLength()
    {
        return $this->min_length;
    }

    public function getMaxLength()
    {
        return $this->max_length;
    }

    public function check($val)
    {
        if(!parent::check($val)) {
            return true;
        }
        if(!is_string($val)) {
            throw new \Exception($this->value_name . ' Not A String');
        }
        if(isset($this->regexp) && !preg_match($this->regexp, $val)) {
            throw new \Exception($this->value_name . ' Not Matched '. $this->regexp);
        }
        $len = strlen($val);
        if($this->max_length && $len > $this->max_length) {
            throw new \Exception($this->value_name . '\'s Length Over '.$this->max_length);
        }
        if($this->min_length && $len < $this->min_length) {
            throw new \Exception($this->value_name . '\'s Length Lower '.$this->min_length);
        }
        return true;
    }
}
