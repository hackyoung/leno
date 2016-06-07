<?php
namespace Leno\Core\Type;

class Uuid extends \Leno\Core\Type
{
    protected $regexp = '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/i';

    public function check($value)
    {
        if(!parent::check($value)) {
            return true;
        }
        if(!preg_match($this->regexp, $value)) {
            throw new \Exception($this->value_name . ' Not A Valid UUID');
        }
        return true;
    }
}
