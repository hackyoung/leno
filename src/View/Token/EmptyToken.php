<?php
namespace Leno\View\Token;

class EmptyToken extends \Leno\View\Token
{
    protected $reg = '/\<empty.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $var = $this->right($name);
        return $this->condition('empty('.$var.' ?? null)');
    }
}
