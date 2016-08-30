<?php
namespace Leno\View\Token;

class Extend extends \Leno\View\Token
{
    protected $reg = '/\<extend.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        return '<?php $this->extend('.$this->right($name).'); ?>';
    }
}
