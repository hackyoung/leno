<?php
namespace Test\Service;

class Hello extends \Leno\Service
{
    protected $str;

    public function __construct($str)
    {
        $this->str = $str;
    }

    public function output()
    {
        echo $this->str . "\n";
    }
}
