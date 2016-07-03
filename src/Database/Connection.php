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
        $drivers = array_map(function($driver) {
            $weight = $driver->getWeight();
            return [
                'weight' => $weight,
                'driver' => $driver
            ];
        }, $this->drivers);
        array_multisort(array_column($drivers, 'weight'), SORT_DESC, $drivers);
        foreach($drivers as $driver_info) {
            if ($driver_info['weight'] <= 0) {
                // 如果第一个driver的weight都为0,说明已经没有driver可用
                return $this->drivers[] = $this->newDriver();
            }
            return $driver_info['driver'];
        }
    }

    private function newDriver()
    {
        return Driver::get(($this->config['driver'] ?? 'pdo'), $this->config);   
    }
}
