<?php
namespace Leno\Console;

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

    public function getCommand()
    {
        return $this->command;
    }

    public function getSubCommand()
    {
        return $this->sub_command;
    }

    public function getArgs()
    {
        return $this->args;
    }
}
