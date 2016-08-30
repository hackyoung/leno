<?php
namespace Leno\View\Token;

class EndView extends \Leno\View\Token
{
    protected $reg = '/\<\/view\s*\>/U';

    protected function replaceMatched($matched) : string
    {
        return '<?php $this->endView() ?>';
    }
}
