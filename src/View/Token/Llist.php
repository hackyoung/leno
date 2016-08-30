<?php
namespace Leno\View\Token;

class Llist extends \Leno\View\Token
{
    protected $reg = '/\<llist.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        $id = $this->attrValue('id', $matched);
        $var = $this->right($name);
        $ret = '<?php $__number__ = 0; $__list__ = %s ?? []; foreach($__list__ as %s) { ?>';
        return sprintf($ret, $var, $this->varString($id));
    }
}
