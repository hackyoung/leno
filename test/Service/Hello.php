<?php
namespace Test\Service;

class Hello extends \Leno\Service\Remote
{
    protected $url = 'hello.world';

    protected $method = self::POST;

    public function __construct()
    {
        $this->setParameter((new \Leno\Service\Remote\Parameter)->setData([
            'hello' => 'world'
        ]));
    }
}
