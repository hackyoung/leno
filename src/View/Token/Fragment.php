<?php
namespace Leno\View\Token;

class Fragment extends \Leno\View\Token
{
    protected $reg = '/\<fragment\s+.*\/\>/U';

    protected function replaceMatched($matched) : string
    {
        $name = $this->attrValue('name', $matched);
        return '<?php $this->startFragment('.$this->right($name).'); $this->endFragment(); $this->getFragment('.$this->right($name).')[\'fragment\']->display() ?>';
            
    }
}
