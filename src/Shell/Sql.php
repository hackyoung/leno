<?php
namespace Leno\Shell;

class Sql extends \Leno\Shell
{
    public function main($hello = null)
    {
        echo $hello. "\n";
    }

    public function ech()
    {
        $this->error('hello world');
        $this->debug('hello world');
        $this->notice('hello world');
        $this->warn('hello world');
        $this->info('hello world');
    }

    public function help($commend = null)
    {
    }

    public function description()
    {
    }
}
