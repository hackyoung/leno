<?php
namespace Leno\Routing;

/**
 * Router 通过一个Uri路由到正确的controller and action
 * 这个Router可以通过规则路由到其他Router，也可以路由到controller
 * ###sample
 */
class Router
{
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
        $this->path = $this->initPath();
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
        $this->path = $path;
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
        $result = $this->handleRule();
        if($result instanceof self) {
            return $result->route();
        }
        $this->path = $result;
        if($this->mode === self::MOD_MIX) {
            $target = $this->getTarget(self::MOD_RESTFUL);
            try {
                $this->response = $this->invoke($target);
            } catch(\Exception $e) {
                $target = $this->getTarget(self::MOD_NORMAL);
                $this->invoke($target);
            }
        } else {
            $target = $this->getTarget();
            $this->invoke($target);
        }
        $this->afterRoute();
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

    /**
     * 初始化path，path是放在request里面的path属性，如果没有该属性，则path=uri
     */
    private function initPath()
    {
        if($this->request->hasAttribute('path')) {
            $tmpath = $this->request->getAttribute('path');
        } else {
            $tmpath = strtolower((string)$this->request->getUri());
        }
        $path = trim(preg_replace(
            '/^.*index\.php/U', '', $tmpath
        ), '\/') ?: (
            ($this->mode === self::MOD_RESTFUL) ?
            'index' : 'index/index'
        );
        return $path;
    }

    /**
     * 解析path，得到一个Target对象，Target包含了将调用的controller，将调用的方法，调用参数，可直接通过Target::invoke调用
     */
    private function getTarget($mode = null)
    {
        $mode = $mode ?? $this->mode;
        $parameters = [];
        $path = preg_replace_callback('/\/\${.*}/U', 
        function($matches) use (&$parameters) {
            $parameters[] = preg_replace('/\/|\$|\{|\}/', '', $matches[0]);
            return '';
        }, $this->path);
        $path = !empty($path) ? $path : (
            ($this->mode === self::MOD_RESTFUL) ?
            'index' : 'index/index'
        );
        $patharr = array_merge(
            explode('/', $this->base),
            explode('/', $path)
        );
        $path = array_filter(array_map(function($p) {
            return \camelCase($p, true, '-');
        }, $patharr));
        if($mode === self::MOD_RESTFUL) {
            $method =strtoupper($_POST['_method'] ?? $this->request->getMethod());
            if(!isset($this->restful[$method])) {
                throw new \Leno\Http\Exception(501);
            }
            $action = $this->restful[$method];
        } else {
            $action = preg_replace_callback('/^[A-Z]/', function($matches) {
                if(isset($matches[0])) {
                    return strtolower($matches[0]);
                }
            }, preg_replace('/\..*$/', '', array_pop($path)));
        }
        try {
            return (new \Leno\Routing\Target(implode('\\', $path)))
                ->setMethod($action)
                ->setParameters($parameters);
        } catch(\Exception $ex) {
            throw new \Leno\Http\Exception(404);
        }
    }

    private function handleRule()
    {
        foreach($this->rules as $reg => $rule) {
            $regexp = preg_replace('/\$\{.*\}/U', '.*', $reg);
            $regexp = preg_replace('/^\/|\/$/', '', $regexp);
            $regexp = '/'.preg_replace('/\//', '\/', $regexp).'/';
            if(!preg_match($regexp, $this->path)) {
                continue;
            }
            if(preg_match('/Router/', $rule)) {
                return $this->resolvRouterRule($rule);
            }
            return $this->resolvPathRule($reg, $rule);
        }
        return $this->path;
    }

    private function resolvPathRule($reg, $rule)
    {
        $reg = preg_replace('/\/{0,1}\$\{\d+\}\/{0,1}/', '|', $reg);
        $reg = '/('.implode('\/)|(', explode('|', $reg)).')/';
        $parameters = explode('/', preg_replace($reg, '', $this->path));
        return preg_replace_callback('/\$\{.*\}/U', 
        function($matches) use (&$idx, $parameters) {
            $idx = (int)preg_replace('/\$|\{|\}/', '', $matches[0]) - 1;
            return '${'.$parameters[$idx].'}';
        }, $rule);
    }

    private function resolvRouterRule($class)
    {
        try {
            $rc = new \ReflectionClass($class);
        } catch(\Exception $e) {
            throw new \Leno\Exception(
                'router:'.$rule.' not found'
            );
        }
        $request = clone $this->request;
        $request->withAttribute('path', preg_replace(
            $regexp, '', $this->path
        ));
        return $rc->newInstance($request, $this->response);
    }

    private function invoke($target)
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

    protected function beforeRoute()
    {
    }

    protected function afterRoute()
    {
    }
}
