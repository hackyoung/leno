<?php
namespace Leno;

use \Leno\Http\Request;
use \Leno\Http\Response;

class Worker
{
    use \Leno\Singleton;

    protected $uri;

    protected $method;

    protected $request;

    protected $response;

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
		self::autoload();
    }

    public function execute()
    {
        try {
            $this->response = (new self::$Router($this->request, $this->response))->route();
        } catch(\Exception $e) {
            $this->response = $this->handleException($e);
        }
        $this->response->send();
    }

    public function getResponse()
    {
        return $this->response;
    }

	public function logger($name = 'default')
	{
		$log = new \Monolog\Logger($name);
		$log->pushHandler(new \Monolog\Handler\StreamHandler(
			self::$log_path . '/' .$name.'.log', \Monolog\Logger::DEBUG
		));
		return $log;
	}

    public static function autoload()
    {
        spl_autoload_register(function($class) {
            $class = preg_replace('/\\$/', '', $class);
            $classFile = strtr($class, '\\', '/') . '.php';
            if(file_exists($classFile)) {
                require_once $classFile;
            }
        });
    }

	public function handleException($e)
	{
		if($e instanceof \Leno\Http\Exception) {
			return $this->response->withStatus($e->getCode())
				->write($e->getMessage());
		}
		throw $e;
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
