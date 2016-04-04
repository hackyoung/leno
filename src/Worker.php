<?php
namespace Leno;

use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;

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
        $this->response = new Response;
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr.'['.$errno.'] in '.$errfile.' line '.$errline);
        });

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

    public static function setRouterClass($routerClass) {
        self::$Router = $routerClass;
    }
}
