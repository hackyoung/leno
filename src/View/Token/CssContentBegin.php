<?php
namespace Leno\View\Token;

class CssContentBegin extends \Leno\View\Token
{
    protected $reg = '/\<style.*\>/U';

    protected function replaceMatched($matched) : string
    {
        $link = $this->attrValue('link', $matched);
        if(empty($link)) {
            return '<?php self::beginCssContent(); ?>';
        }
        return $matched;
    }
}
