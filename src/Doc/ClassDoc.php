<?php
namespace Leno\Doc;

use \Leno\Doc\ClassParser;
use \Leno\Doc\Out;

class ClassDoc
{

    protected $out_type;

    protected $out_dir;

    protected $out_name;

    protected $class_parser;

    public function __construct($className)
    {
        if(!class_exists($className)) {
            throw new \Leno\Exception($className . ' Not Found');
        }
        $this->class = new \ReflectionClass($className);
    }

    public function setOutType($out_type)
    {
        $this->out_type = $out_type;
        return $this;
    }

    public function setOutDir($out_dir)
    {
        $this->out_dir = $out_dir;
        return $this;
    }

    public function setOutName($out_name)
    {
        $this->out_name = $out_name;
        return $this;
    }

    public function execute()
    {
        $class = $this->class;
        $out = Out::get($this->out_type)
            ->setDir($this->out_dir)
            ->setFileName($this->out_name)
            ->setClass($class)
            ->execute();
    }
}
