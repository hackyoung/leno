<?php
define('TEST_MVC', false);

require __DIR__. '/boot.php';

use \Test\TestPromise;

$promise = new TestPromise;

$promise->then(function() use ($promise) {
    echo "父进程执行完毕了，该我了\n";
    $promise->setStatus(TestPromise::REJECTED);
}, function() {
    echo "出错了\n";
})->then(function() {
    sleep(4);
    echo "我是第二个then\n";
})->execute();
