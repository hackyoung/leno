<?php
namespace Leno\Console;

class Commender extends \Leno\Shell
{
    protected $commend;

    protected $action;

    protected $args;

    public static $namespaces = [
        'leno.shell',
        'shell',
    ];

    public function setCommend($commend)
    {
        $this->commend = $commend;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    public function execute()
    {
        $target = $this->getTarget();
        $target->getMethod($this->action)
            ->invokeArgs($target->newInstance(), $this->args);
    }

    public function help($commend = null)
    {
        $info = "用法: <info><keyword>leno</keyword> <keyword>commend</keyword> <keyword>[sub-commend]</keyword> <keyword>[[param]]</keyword></info>";
        (new \Leno\Console\Formatter)->format($info);
        echo "\n";
    }

    private function getTarget()
    {
        foreach(self::$namespaces as $namespace) {
            $class = preg_replace_callback('/^\w|\.\w/', function($matches) {
                return strtoupper(str_replace('.', '\\', $matches[0]));
            }, $namespace.'.'.$this->commend);
            if(class_exists($class)) {
                return new \ReflectionClass($class);
            }
        }
        throw new \Exception('target \''.$this->commend.'\' not exists');
    }

    public static function register($namespace)
    {
        self::$namespaces[] = $namespace;
    }
}
