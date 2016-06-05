<?php
namespace Leno\Service\Local\Cryptor;

use \Leno\Configure;

class Normal
{
    public function encode($source)
    {
        $salt = Configure::read('salt') ?? self::getDefaultSalt();
    }
}
