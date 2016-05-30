<?php
namespace Leno\Routing;

use \Leno\Routing\Rule;
use \Leno\Routing\Target;
use \Leno\Traits\Setter as MagicCall;

/**
 * Router 通过一个Uri路由到正确的controller and action
 * 这个Router可以通过规则路由到其他Router，也可以路由到controller
 * ###sample
 */
class Router
{

    use \Leno\Traits\MagicCall;

    /**
     * 通常的路由模式 path类型为namespace/controller/method/{$param1}/{$param2}/...
     */
    const MOD_NORMAL = 0;

    /**
     * restful路由模式 path类型为namespace/controller/{$param1}/{$param2}/...
     */
    const MOD_RESTFUL = 1;

    /**
     * 混合路由模式 Router会先以restful模式查找controller，失败则使用普通模式查找
     * 如果都失败，404
     * 不推荐使用这种模式，性能吃力
     */
    const MOD_MIX = 2;

    /**
     * 传入的request参数
     */
    protected $request;

    /**
     * 传入的response参数
     */
    protected $response;

    /**
     * namespace/class/method/${param1}/${param2}
     */
    protected $path;

    /**
     * [
     *      'regexp' => 'router_class_name|target_path'
     * ]
     */
    protected $rules = [];

    /**
     * 查找类的跟路径
     */
    protected $base = 'controller';

    /**
     * 当前路由器的模式
     */
    protected $mode = self::MOD_RESTFUL;

    /**
     * restful模式下各种http method对应请求的方法
     */
    protected $restful = [
        'GET'       =>  'index',    // 将get提交的路由到index方法
        'POST'      =>  'add',      // 将post提交的路由到add方法
        'DELETE'    =>  'remove',   // 将delete方式提交的路由到remove方法
        'PUT'       =>  'modify',   // 将put方式提交的路由到modify方法
    ];

    /**
     * 构造函数
     * @param \Leno\Http\Request request
     * @param \Leno\Http\Response resonse
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->path = new Path($this);
    }

    public function getActionOfRestful($method)
    {
        return $this->restful[$method] ?? null;
    }

    /**
     * 路由时通过解析path路由到指定的controller，这里没有直接使用request::uri进行路由，
     * 其原因时，如果路由到的是另一个router而不是controller，那
     * 么用户在最终的controller中获得request对象中的uri将不是一个正确的uri
     * @param string path ###sample /cq/blog/index
     * @return this
     */
    public function setPath($path)
    {
        $this->path->set($path);
        return $this;
    }

    /**
     * 执行路由操作，该方法会先查看路由器上面是否设置规则，如有规则则按规则路由，
     * 如果没有设置规则，则根据path路由
     * @return \Leno\Http\Response
     */
    public function route()
    {
        $this->beforeRoute();
        $result = (new Rule($this))->handle();
        if($result instanceof self) {
            return $result->route();
        } 
        if (is_string($result)) {
            $this->path = new Path($result);
        }
        if($this->mode !== self::MOD_MIX) {
            $target = Target::getFromRouter($this);
            $this->invoke($target);
            $this->afterRoute();
            return $this->response;
        }
        $this->setMode(self::MOD_RESTFUL);
        try {
            $target = Target::getFromRouter($this);
            $this->response = $this->invoke($target);
        } catch(\Exception $e) {
            $this->setMode(self::MOD_NORMAL);
            $target = Target::getFromRouter($this);
            $this->invoke($target);
        }
        $this->setMode(self::MOD_MIX);
        $this->afterRoute();
        return $this->response;
    }

    protected function invoke($target)
    {
        $instance = $target->setConstructParameters([
            $this->request, $this->response
        ])->getInstance();
        if($target->hasMethod('beforeExecute')) {
            $target->invoke('beforeExecute', $instance);
        }
        ob_start();
        $response = $target->invoke(null, $instance);
        $content = ob_get_contents();
        ob_end_clean();
        if($this->handleResult($response)) {
            $this->response->write($content);
        }
        if($target->hasMethod('afterExecute')) {
            $this->response = $target->invoke('afterExecute', $instance);
        }
        return $this->response;
    }

    /**
     * 对路由的返回结果包装成一个\Leno\Http\Response对象
     */
    protected function handleResult($response)
    {
        if($response === null) {
            return true;
        } elseif($response instanceof \Leno\Http\Response) {
            $this->response = $response;
        } elseif($response instanceof \Psr\Http\Message\StreamInterface) {
            $this->response = $this->response->withBody($response);
        } elseif(is_string($response)) {
            $this->response->write($response);
        } else {
            throw new \Leno\Exception('Controller returned a "'.gettype($response).'" but not supported.');
        }
        return true;
    }


    protected function beforeRoute()
    {
    }

    protected function afterRoute()
    {
    }
}
