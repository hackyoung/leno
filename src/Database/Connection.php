<?php
namespace Leno\Database;

use \Leno\Database\DriverInterface;
use \Leno\Database\Driver;
use \Leno\Traits\Singleton;
use \Leno\Configure;

/**
 * 这里没有注释^.^
 * if you dont understand chinese.
 * the above mean there is not a comment, so stop looking for^.^
 */
class Connection
{
    use Singleton;

    protected $config;

    protected $drivers = [];

    protected function __construct()
    {
        $this->config = Configure::read('database');
    }

    public function config ($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 通过加权轮询的方式返回一个合适的driver
     * 在一次http请求，同一次执行的请求仅有一条，这么做其实并不合适
     * 次处代码的目的是提供一种想法和灵感，也许就迸发出火花了，呵呵哒
     */
    public function select() : DriverInterface
    {
        if(empty($this->drivers)) {
            return $this->drivers[] = $this->newDriver();
        }
        $weighted_drivers = array_map(function($driver) {
            $weight = $driver->getWeight();
            return [$weight => $driver];
        }, $this->drivers);
        krsort($weighted_drivers);
        foreach($weighted_drivers as $weight=>$driver) {
            if ($weight <= 0) {
                // 如果第一个driver的weight都为0,说明已经没有driver可用
                return $this->drivers[] = $this->newDriver();
            }
            return $driver;
        }
    }

    private function newDriver()
    {
        return Driver::get($this->config['driver'], $this->config);   
    }
}
