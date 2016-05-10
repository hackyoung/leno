<?php
namespace Leno\Console\Formatter\Node;

class Error extends \Leno\Console\Formatter\Node
{
    protected $format = "\x1b[0;31m%s\x1b[0m";
}
