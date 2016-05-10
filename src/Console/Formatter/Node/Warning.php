<?php
namespace Leno\Console\Formatter\Node;

class Warning extends \Leno\Console\Formatter\Node
{
    protected $format = "\x1b[0;33m%s\x1b[0m";
}
