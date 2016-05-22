<?php
namespace Leno\Console;

class Commander extends \Leno\Shell
{
    protected $command;

    protected $action;

    protected $args;

    private $can_execute = true;

    public static $namespaces = [
        'leno.shell',
        'shell',
    ];

    public function __construct()
    {
        try {
            $parser = \Leno\Console\ArgParser::instance();
        } catch(\Exception $ex) {
            $this->setExecute(false)->help();
            return;
        }
        $this->command = $parser->getCommand();
        $this->action = $parser->getSubCommand();
        $this->args = $parser->getArgs();
    }

    public function setCommand($command)
    {
        $this->command = $command;
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
        if($this->canExecute()) {
            return;
        }
        try {
            $target = $this->getTarget();
        } catch(\Exception $ex) {
            \Leno\Console\Stdout::error($ex->getMessage());
            return;
        }
        $shell = $target->newInstance();
        $needed = $shell->getArgsNeeded($this->action);
        foreach($needed as $key => $need) {
            foreach($this->args as $arg => $val) {
                if(in_array($arg, $need)) {
                    $shell->setArg($key, $val);
                }
            }
        }
        $target->getMethod($this->action)->invoke($shell);
    }

    public function help($command = null)
    {
        $info = 
<<<EOF
用法: <keyword>leno command [sub-command] [param...]</keyword>
支持的命令<keyword>(command)</keyword>：
<keyword>hello_world</keyword>\t\t初始化项目
<keyword>doc</keyword>\t\t生成文档
<keyword>build</keyword>\t\t建立数据库
EOF;
        (new \Leno\Console\Formatter)->format($info);
        echo "\n";
    }

    public function describe()
    {
        return "";
    }

    private function getTarget()
    {
        foreach(self::$namespaces as $namespace) {
            $class = preg_replace_callback('/^\w|\.\w/', function($matches) {
                return strtoupper(str_replace('.', '\\', $matches[0]));
            }, $namespace.'.'.$this->command);
            if(class_exists($class)) {
                return new \ReflectionClass($class);
            }
        }
        throw new \Exception('target \''.$this->command.'\' not exists');
    }

    private function setExecute($can = false)
    {
        $this->can_execute = $can;
        return $this;
    }

    private function canExecute()
    {
        return !$this->can_execute;
    }

    public static function register($namespace)
    {
        self::$namespaces[] = $namespace;
    }
}
