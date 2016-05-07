<?php
namespace Leno\Service\Remote;

interface ResultInterface
{
    public function getResult(\Leno\Http\Response $response);
}
