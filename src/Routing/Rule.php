<?php
namespace Leno\Routing;

use \Leno\Routing\Router;

class Rule
{

    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function handle()
    {
        $path = (string)$this->router->getPath();
        $rules = $this->router->getRules();
        foreach($rules as $reg => $rule) {
            $regexp = preg_replace('/\$\{.*\}/U', '.*', $reg);
            $regexp = preg_replace('/^\/|\/$/', '', $regexp);
            $regexp = '/'.preg_replace('/\//', '\/', $regexp).'/';
            if(!preg_match($regexp, $path)) {
                continue;
            }
            if(preg_match('/Router$/', $rule)) {
                return $this->resolvRouterRule($rule, $reg);
            }
            return $this->resolvPathRule($reg, $rule);
        }
    }

    private function resolvPathRule($reg, $rule)
    {
        $path = (string)$this->router->getPath();
        $reg = preg_replace('/\/{0,1}\$\{\d+\}\/{0,1}/', '|', $reg);
        $reg = str_replace('|', ')|(', $reg);
        $reg = '/('.str_replace('/', '\/', $reg).')/';
        $parameters = explode('/', preg_replace($reg, '', $path));
        return preg_replace_callback('/\$\{.*\}/U', 
        function($matches) use (&$idx, $parameters) {
            $idx = (int)preg_replace('/\$|\{|\}/', '', $matches[0]) - 1;
            return '${'.$parameters[$idx].'}';
        }, $rule);
    }

    private function resolvRouterRule($class, $regexp)
    {
        try {
            $rc = new \ReflectionClass($class);
        } catch(\Exception $e) {
            throw new \Leno\Exception(
                'router:'.$class.' not found'
            );
        }
        $request = clone $this->router->getRequest();
        $request->withAttribute('path', preg_replace(
            '/'.$regexp.'/', '', (string)$this->router->getPath()
        ));
        $response = $this->router->getResponse();
        return $rc->newInstance($request, $response);
    }
}
