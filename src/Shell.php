<?php
namespace Leno;

abstract class Shell
{
    private $args = [];

    protected $needed_args = [];

    public function setArg($idx, $val)
    {
        $this->args[$idx] = $val;
        return $this;
    }

    public function getArgsNeeded($method)
    {
        $command = $this->needed_args[$method] ?? [];
        $args = $this->getArgsInfo($method);
        $needed = [];
        foreach($args as $key => $arg) {
            $needed[$key] = $arg['looks'] ?? [];
        }
        return $needed;
    }

    public function input($idx)
    {
        return $this->args[$idx] ?? false;
    }

    public function error($msg)
    {
        \Leno\Console\Io\Stdout::error($msg);
        return $this;
    }

    public function warn($msg)
    {
        \Leno\Console\Io\Stdout::warn($msg);
        return $this;
    }

    public function notice($msg)
    {
        \Leno\Console\Io\Stdout::notice($msg);
        return $this;
    }

    public function debug($msg)
    {
        \Leno\Console\Io\Stdout::debug($msg);
        return $this;
    }

    public function info($msg)
    {
        \Leno\Console\Io\Stdout::info($msg);
        return $this;
    }

    public function output($msg)
    {
        \Leno\Console\Io\Stdout::output($msg);
        return $this;
    }

    function help($command = null)
    {
        if($command) {
            $this->handleHelp($command);
            return;
        }
        $this->output("\n".$this->describe()."\n");
        foreach($this->needed_args as $command => $args) {
            $this->handleHelp($command);
        }
    }

    protected function handleHelp($command)
    {
        $this->output(sprintf('用法:<keyword>leno %s %s %s </keyword>',
            strtolower(preg_replace('/^.*\\\/', '', unCamelCase(get_called_class()))),
            $command, '[参数]'
        ));
        $this->output("\n".$this->describeCommand($command)."\n");
        $this->output('支持的参数：');
        $opts = $this->getArgsInfo($command);
        foreach($opts as $opt) {
            $this->output(sprintf("<keyword>  %s\t\t</keyword>%s", 
                implode(",", $opt['looks']), $opt['description']
            ));
        }
    }

    protected function getArgsInfo($command)
    {
        if(!isset($this->needed_args[$command])) {
            return [];
        }
        return $this->needed_args[$command]['args'] ?? [];
    }

    abstract public function describe();

    public function describeCommand($command)
    {
        if(!isset($this->needed_args[$command])) {
            return '';
        }
        return $this->needed_args[$command]['description'] ?? '';
    }
}
