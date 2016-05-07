<?php
namespace Leno\Service\Remote;

class Result implements \Leno\Service\Remote\ResultInterface
{
    public function getResult(\Leno\Http\Response $response)
    {
        return (string)$response->getBody();
    }
}
