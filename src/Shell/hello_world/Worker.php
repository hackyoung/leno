<?php

class Worker extends \Leno\Worker
{
    /**
     * 需要自定义异常处理重写下面的方法
     */
    public function handleException($e)
    {
        // 父类捕获了\Leno\Http\Exception异常，转换为对应的HTTP Response
        try {
            parent::handleException($e);
        } catch(\Exception $e) {
            // 自己的逻辑
        }

        // 如果需要忽略父类对\Leno\Http\Exception的处理则注释掉上面的方法,写自己的逻辑
        throw $e;
    }
}
