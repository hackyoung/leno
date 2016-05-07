<?php
namespace Leno\Routing;

/**
 * Router 通过一个Uri路由到正确的controller and action
 * 这个Router可以通过规则路由到其他Router，也可以路由到controller
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
     * @var 传入的request参数
     */
    protected $request;

    /**
     * @var 传入的response参数
     */
    protected $response;

    /**
     * namespace/class/method/${param1}/${param2}
     */
    protected $path;

    /**
     * @var [
     *      'regexp' => 'router_class_name|target_path'
     * ]
     */
    protected $rules = [];

    /**
     * @var 查找类的跟路径
     */
    protected $base = 'controller';

    /**
     * @var 当前路由器的模式
     */
    protected $mode = self::MOD_RESTFUL;

    /**
     * @var restful模式下各种http method对应请求的方法
     */
    protected $restful = [
        'GET' => 'index',
        'POST' => 'add',
        'DELETE' => 'delete',
        'PUT' => 'modify',
    ];

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->path = $this->initPath();
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

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

    private function getTarget($mode = null)
    {
        $mode = $mode ?? $this->mode;
        $parameters = [];
        $path = preg_replace_callback('/\/\${.*}/U', 
        function($matches) use (&$parameters) {
            $parameters[] = preg_replace('/\/|\$|\{|\}/', '', $matches[0]);
            return '';
        }, $this->path);
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
