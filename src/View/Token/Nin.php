<?php
namespace Leno\View\Token;

class Nin extends \Leno\View\Token
{
    protected $reg = '/\<nin.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $var = $this->attrValue('name', $matched);
        $value = $this->attrValue('value', $matched);
        $tmp = '<?php if(!in_array(%s, %s)) { ?>';
        return sprintf($tmp, $this->right($var), $this->right($value));
    }
}
