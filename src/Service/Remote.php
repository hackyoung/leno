<?php
namespace Leno\Service;

class Remote extends \Leno\Service
{
    protected $url;

    protected $method;

    protected $params;

    protected $resultHandler;

    protected $paramHandler;

    public function setResultHandler($handler)
    {
        $$this->resultHandler = $handler;
    }


    public function execute()
    {
        
    }
}
