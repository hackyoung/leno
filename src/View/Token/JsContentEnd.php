<?php
namespace Leno\View\Token;

class JsContentEnd extends \Leno\View\Token
{
    protected $reg = '/\<\/script\>/';

    protected function replaceMatched($matched) : string
    {
        return '<?php self::endJsContent(); ?>';
    }
}
