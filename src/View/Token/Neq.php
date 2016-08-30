<?php
namespace Leno\View\Token;

class Neq extends \Leno\View\Token
{
    protected $reg = '/\<neq.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $value = $this->attrValue('value', $matched);
        return $this->condition($this->right($name) .'!='. $this->right($value));
    }
}
