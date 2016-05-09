<?php
namespace Leno;

abstract class Shell
{
    public function error($msg)
    {
        \Leno\Console\Stdout::error($msg);
        return $this;
    }

    public function warn($msg)
    {
        \Leno\Console\Stdout::warn($msg);
        return $this;
    }

    public function notice($msg)
    {
        \Leno\Console\Stdout::notice($msg);
        return $this;
    }

    public function debug($msg)
    {
        \Leno\Console\Stdout::debug($msg);
        return $this;
    }

    abstract function help($commend = null);
}
