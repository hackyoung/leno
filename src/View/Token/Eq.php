<?php
namespace Leno\View\Token;

class Eq extends \Leno\View\Token
{
    protected $reg = '/\<eq.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $value = $this->attrValue('value', $matched);
        return $this->condition($this->right($name) .'=='. $this->right($value));
    }
}
