<?php
namespace Leno\View\Token;

class EndFragment extends \Leno\View\Token
{
    protected $reg = '/\<\/fragment.*\>/U';

    public function replaceMatched($matched) : string
    {
        return '<?php $this->endFragment(); ?>';
    }
}
