<?php
namespace Leno\View\Token;

class SingletonContentBegin extends \Leno\View\Token
{
    protected $reg = '/\<singleton.*\>/U';

    protected function replaceMatched($matched) : string
    {
        return '<?php self::beginSingletonContent(); ?>';
    }
}
