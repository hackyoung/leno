<?php
namespace Leno\View\Token;

class Func extends \Leno\View\Token
{
    protected $reg = '/\{\:.*\}/U';

    protected function replaceMatched($matched) : string
    {
        return '<?php echo ('.$this->funcString(preg_replace('/^(\{[\|\:])|\}$/', '', $matched)) . ') ?? \'\'; ?>';
    }
}
