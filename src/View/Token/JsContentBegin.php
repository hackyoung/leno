<?php
namespace Leno\View\Token;

class JsContentBegin extends \Leno\View\Token
{
    protected $reg = '/\<script.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $src = $this->attrValue('src', $matched);
        return '<?php self::beginJsContent('.$this->right($src).'); ?>';
    }
}
