<?php
namespace Leno\Console\Formatter\Helper;

class Info extends \Leno\Console\Formatter\Helper
{
    public function format($text)
    {
        return "\x1b[32m\t$text\x1b[0m\n";
    }
}
