<?php
namespace Leno\View\Token;

class CssContentEnd extends \Leno\View\Token
{
    protected $reg = '/\<\/style\>/';

    protected function replaceMatched($matched) : string
    {
        return '<?php self::endCssContent(); ?>';
    }
}
