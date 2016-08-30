<?php
namespace Leno\View\Token;

class NotEmpty extends \Leno\View\Token
{
    protected $reg = '/\<notempty.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $var = $this->right($name);
        return $this->condition('!empty('.$var.')');
    }
}
