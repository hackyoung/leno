<?php
namespace \Leno\Http;

class Exception extends \Leno\Exception
{
    public function __construct($code, $message=null)
    {
        $http_code = array_keys(\Leno\Http::$phrases);
        if(!in_array($code, $http_code)) {
            throw new \Leno\Exception('HTTP CODE Invalid:'.$code);
        }
        parent::__construct($code, $message ?? \Leno\Http::$phrases[$code]);
    }
}
