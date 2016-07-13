<?php
namespace Test;
class TestPromise extends \Leno\Promise
{
    protected function _execute()
    {
        echo "我开始执行了！\n";
        sleep(5);
        return true;
    }
}
