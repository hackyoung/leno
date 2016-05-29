<?php
namespace Leno;

use \Leno\Http\Request;
use \Leno\Http\Response;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * 处理从客户端发上来的Http请求，该类是一个单例，且是应用程序执行的开始
 * 通过调用Worker::execute方法，程序将请求发送到制定的路由器，让路由器
 * 路由到正确的方法
 */
class Worker
{
    /**
     * 使用单例的trait
     */
    use \Leno\Traits\Singleton;

    /**
     * 将http客户端传递上来的信息包装成request对象
     */
    protected $request;

    /**
     * 将需要返回给HTTP客户端的包装成response对象
     */
    protected $response;

    /**
     * worker使用的路由器，可通过Worker::setRouterClass()方法设置
     */
    protected static $Router = '\Leno\Routing\Router';

    /**
     * log文件夹
     */
    protected static $log_path = ROOT . '/tmp/log';

    /**
     * 构造函数，初始化request, response，初始化配置工具,启动自动加载机制
     */
    protected function __construct()
    {
        $this->request = Request::getNormal();
        $this->request->withAttribute(
            'path', strtolower(preg_replace('/\?.*/', '', $uri))
        );
        $this->response = new Response;
        \Leno\Configure::init();
    }

    /**
     * 将response和request传递给Router进行路由，处理Router返回的结果
     */
    public function execute()
    {
        try {
            $this->response = (new self::$Router($this->request, $this->response))->route();
        } catch(\Exception $e) {
            $this->response = $this->handleException($e);
        }
        $this->response->send();
    }

    /**
     * 该方法可以获取到response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 获取一个logger实例
     */
    public function logger($level = Logger::DEBUG, $name = 'default')
    {
        $log = new Logger($name);
        $log->pushHandler(new StreamHandler(
            self::$log_path . '/' .$name.'.log', $level
        ));
        return $log;
    }

    /**
     * 自动加载类,psr4风格
     */
    public static function autoload()
    {
        \Leno\AutoLoader::instance()->execute();
    }

    /**
     * 处理异常信息,子类应该重写该方法
     */
    public function handleException($e)
    {
        if($e instanceof \Leno\Http\Exception) {
            return $this->response->withStatus($e->getCode())
                ->write($e->getMessage());
        }
        throw $e;
    }

    /**
     * 将所有的错误信息转换为异常信息
     */
    public function errorToException()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr.'['.$errno.'] in '.$errfile.' line '.$errline);
        });
    }

    /**
     * 设置Router的class
     */
    public static function setRouterClass($routerClass)
    {
        self::$Router = $routerClass;
    }
}
