<?php
namespace Leno\Routing;

use \Leno\Routing\Router;

class Target extends \ReflectionClass
{
    protected $the_instance;

    protected $method;

    protected $parameters = [];

    protected $constructParameters = [];

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setConstructParameters($parameters)
    {
        $this->constructParameters = $parameters;
        return $this;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function invoke($method = null, $instance=null)
    {
        if($method == null) {
            $method = $this->method;
        }
        if($instance == null) {
            $instance = $this->getInstance();
        }
        if(!$this->hasMethod($method)) {
            throw new \Leno\Http\Exception(404);
        }
        return $this->getMethod($method)->invokeArgs(
            $instance, $this->parameters
        );
    }

    public function getInstance()
    {
        if(!$this->the_instance) {
            $this->the_instance = $this->newInstanceArgs($this->constructParameters);
        }
        return $this->the_instance;
    }

    public static function getFromRouter(Router $router)
    {
        $mode = $router->getMode();
        $parameters = [];
        $path = preg_replace_callback('/\/\${.*}/U', 
        function($matches) use (&$parameters) {
            $parameters[] = preg_replace('/\/|\$|\{|\}/', '', $matches[0]);
            return '';
        }, $router->getPath());
        $patharr = array_merge(
            explode('/', $router->getBase()),
            explode('/', $path)
        );
        $path = array_filter(array_map(function($p) {
            return \camelCase($p, true, '-');
        }, $patharr));
        if($mode === Router::MOD_RESTFUL) {
            $request = $router->getRequest();
            $method =strtoupper($_POST['_method'] ?? $request->getMethod());
            $action = $router->getActionOfRestful($method);
            if($action === null) {
                throw new \Leno\Http\Exception(501);
            }
        } else {
            $action = preg_replace_callback('/^[A-Z]/', function($matches) {
                if(isset($matches[0])) {
                    return strtolower($matches[0]);
                }
            }, preg_replace('/\..*$/', '', array_pop($path)));
        }
        try {
            return (new self(implode('\\', $path)))
                ->setMethod($action)
                ->setParameters($parameters);
        } catch(\Exception $ex) {
            throw new \Leno\Http\Exception(404);
        }
    }
}
