<?php
namespace Leno;

use \Leno\Http\Request;
use \Leno\Http\Response;

class Worker
{
    use \Leno\Singleton;

    protected $request;

    protected $response;

    protected $exception_handler;

    protected static $Router = '\Leno\Routing\Router';

    protected function __construct()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        $this->request = new Request(
            $method, $uri, getallheaders()
        );
        $this->request->withAttribute('path', strtolower($uri));
        $this->response = new Response;
        \Leno\Configure::init();
        $this->autoload();
        $this->exception_handler = function($e, $request, $response) {
            throw $e;
        };
    }

    public function execute()
    {
        try {
            (new self::$Router($this->request, $this->response))->route();
        } catch(\Exception $e) {
            call_user_func(
                $this->exception_handler, $e,
                $this->request, $this->response
            );
        }
    }

    public function setExceptionHandler(callable $handler)
    {
        $this->exception_handler = $handler;
    }

    private function autoload()
    {
        spl_autoload_register(function($class) {
            $class = preg_replace('/\\$/', '', $class);
            $classFile = strtr($class, '\\', '/') . '.php';
            if(file_exists($classFile)) {
                require_once $classFile;
            }
        });
    }

    public function errorToException()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr.'['.$errno.'] in '.$errfile.' line '.$errline);
        });
    }

    public static function setRouterClass($routerClass) {
        self::$Router = $routerClass;
    }
}
