<?php
namespace Leno\View\Token;

abstract class NormalEnd extends \Leno\View\Token
{
    protected $reg;

    protected function replaceMatched($matched) : string 
    {
        return $this->normalEnd();
    }
}
