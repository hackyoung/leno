<?php
namespace Test\Service;

class Hello extends \Leno\Service
{
    protected $str;

    public function setParam($str)
    {
        $this->str = $str;
        return $this;
    }

    public function execute()
    {
        echo $this->str . "\n";
    }
}
