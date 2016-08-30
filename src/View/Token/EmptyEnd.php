<?php
namespace Leno\View\Token;

class EmptyEnd extends NormalEnd
{
    protected $reg = '/\<\/empty.*\>/U';

    protected function replaceMatched($matched) : string
    {
        return $this->normalEnd();
    }
}
