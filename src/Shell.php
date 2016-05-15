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
        return $this->needed_args[$method] ?? [];
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

    abstract function help($commend = null);

    abstract function description();
}
