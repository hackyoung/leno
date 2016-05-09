<?php
namespace Leno\Console\Formatter\Helper;

class Keyword extends \Leno\Console\Formatter\Helper
{
    public function format($text)
    {
        return "\x1b[33m$text\x1b[0m";
    }
}
