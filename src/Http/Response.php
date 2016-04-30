<?php
namespace Leno\Http;

class Response extends \GuzzleHttp\Psr7\Response
{
    public function write($string)
    {
        $this->getBody()->write($string);
        return $this;
    }
}
