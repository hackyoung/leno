<?php
namespace Leno\View\Token;

class ExtendEnd extends \Leno\View\Token
{
    protected $reg = '/\<\/extend.*\>/U';

    protected function replaceMatched($matched) : string
    {
		return '<?php $this->parent->render(); ?>';
    }
}
