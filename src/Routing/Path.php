<?php
namespace Leno\Routing;

use \Leno\Routing\Router;

class Path
{
    protected $path;

    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function set($path)
    {
        $this->path = $path;
        return $this;
    }

    public function __tostring()
    {
        return $this->get();
    }

    public function get()
    {
        if($this->path === null) {
            $this->path = self::defaultPath($this->router);
        }
        return $this->path;
    }

    public static function defaultPath(Router $router)
    {
        $request = $router->getRequest();
        if($request->hasAttribute('path')) {
            $tmpath = $request->getAttribute('path');
        } else {
            $tmpath = strtolower((string)$request->getUri());
        }
        $path = trim(preg_replace(
            '/^.*index\.php/U', '', $tmpath
        ), '\/') ?: (
            ($router->getMode() === Router::MOD_RESTFUL) ?
            'index' : 'index/index'
        );
        return $path;
    }
}
