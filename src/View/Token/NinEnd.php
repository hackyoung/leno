<?php
namespace Leno\View\Token;

class NinEnd extends \Leno\View\Token\NormalEnd
{
    protected $reg = '/\<\/nin.*\>/U';
}
