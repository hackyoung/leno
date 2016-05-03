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

	protected static $log_path = ROOT . '/tmp/log';

    protected function __construct()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'get';
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
            $this->response = (new self::$Router($this->request, $this->response))->route();
        } catch(\Exception $e) {
            $this->response = $this->exception_handler($e, $this->response);
        }
        $this->response->send();
    }

	public function logger($name = 'default')
	{
		$log = new \Monolog\Logger($name);
		$log->pushHandler(new \Monolog\Handler\StreamHandler(
			self::$log_path . '/' .$name.'.log', \Monolog\Logger::DEBUG
		));
		return $log;
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
