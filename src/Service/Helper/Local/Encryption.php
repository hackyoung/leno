<?php
namespace Leno\Service\Local;

use \Leno\Service\Local\Cryptor\Normal as Cryptor;

class Encryption extends \Leno\Service\Local
{
    protected $cryptor;

    protected $source;

    public function __construct()
    {
        $this->cryptor = Cryptor::instance();
    }

    public function execute()
    {
        return $this->cryptor->encode($this->source);
    }
}
