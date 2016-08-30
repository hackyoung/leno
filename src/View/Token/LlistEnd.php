<?php
namespace Leno\View\Token;

class LlistEnd extends NormalEnd
{
    protected $reg = '/\<\/llist.*\>/U';

    public function replaceMatched($matched) : string
    {
        return '<?php $__number__++; } ?>';
    }
}
