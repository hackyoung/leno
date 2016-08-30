<?php
namespace Leno\View\Token;

class NotEmptyEnd extends NormalEnd
{
    protected $reg = '/\<\/notempty.*\>/U';
}
