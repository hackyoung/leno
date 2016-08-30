<?php
namespace Leno\View\Token;

class SingletonContentEnd extends \Leno\View\Token
{
    protected $reg = '/\<\/singleton\>/';

    protected function replaceMatched($matched) : string
    {
        return '<?php self::endSingletonContent(); ?>';
    }
}
