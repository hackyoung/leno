<?php
namespace Leno;

class Exception extends \Exception
{
    public function __tostring()
    {
        echo "<pre>";
        return parent::__tostring();
    }
}
