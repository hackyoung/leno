<?php
namespace Leno;

abstract class Shell
{
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
