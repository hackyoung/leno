<?php
namespace Leno\Console;

/**
 * 解析shell传递的参数，leno [command] [sub-command] [--arg1|-a1] arg1-value [--arg2|-a2] arg2-value ...
 * 其中--arg1/-a1是命令行表示的参数名，--arg1是gnu风格，-a1是unix风格的参数格式还支持 --arg1=arg1-value|-a1=arg1-value的形式
 */
class ArgParser
{
    use \Leno\Traits\Singleton;

    protected $command;

    protected $sub_command = 'main';

    protected $args = [];

    private function __construct() 
    {
        $args = $_SERVER['argv'];
        array_splice($args, 0, 1);
        if(count($args) < 1) {
            throw new \Leno\Exception('No Command Assign');
        }
        $this->command = $args[0];
        array_splice($args, 0, 1);
        if(count($args) > 0 && !preg_match('/^\-{1,2}/', $args[0])) {
            $this->sub_command = $args[0];
            array_splice($args, 0, 1);
        }
        $prev = false;
        foreach($args as $arg) {
            if(preg_match('/=/', $arg)) {
                $arg = explode('=', $arg);
                $this->args[$arg[0]] = $arg[1];
                $prev = false;
                continue;
            }
            if(preg_match('/^\-{1,2}\w/', $arg)) {
                $prev = $arg;
                $this->args[$prev] = true;
                continue;
            }
            if($prev !== false) {
                $this->args[$prev] = $arg;
            }
        }
    }

    /**
     * 获取从shell解析过来的command, commander会将它当作一个类
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * 获取从shell解析过来的sub-command，commander会将它当作类中的一个方法
     * @return string
     */
    public function getSubCommand()
    {
        return $this->sub_command;
    }

    /**
     * 获取从shell传递过来的参数列表
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
}
