<?php
namespace Leno\Routing;

use \Leno\Routing\Router;

class Rule
{
    protected $path;

    protected $rules;

    public function __construct($path, $rules)
    {
        $this->path = $path;
        $this->rules = $rules;
    }

    public function handle(Router $router = null)
    {
        $path = (string)$this->path;
        $rules = $this->rules;
        foreach($rules as $reg => $rule) {
            $regexp = '/^'.str_replace('/','\/', str_replace('\/', '/', $reg)).'$/';
            if(!preg_match($regexp, $path)) {
                continue;
            }
            if(preg_match('/Router$/', $rule)) {
                return $this->resolvRouterRule($rule, $reg, $router);
            }
            return $this->resolvPathRule($reg, $rule);
        }
    }

    private function resolvPathRule($reg, $rule)
    {
        $path = (string)$this->path;
        $reg_arr = explode('/', $reg);
        foreach ($reg_arr as $r) {
            $path = str_replace($r.'/', '', $path);
        }
        $parameters = array_values(array_filter(explode('/', $path)));
        return preg_replace_callback('/\$\{.*\}/U', 
        function($matches) use ($parameters) {
            $idx = (int)preg_replace('/\$|\{|\}/', '', $matches[0]) - 1;
            return '${'.$parameters[$idx].'}';
        }, $rule);
    }

    private function resolvRouterRule($class, $regexp, $router)
    {
        try {
            $rc = new \ReflectionClass($class);
        } catch(\Exception $e) {
            throw new \Leno\Exception(
                'router:'.$class.' not found'
            );
        }
        $request = clone $router->getRequest();
        $request->withAttribute('path', preg_replace(
            '/'.$regexp.'/', '', (string)$this->path
        ));
        $response = $router->getResponse();
        return $rc->newInstance($request, $response);
    }
}
