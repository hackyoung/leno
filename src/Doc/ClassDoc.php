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
        $this->class_parser = new ClassParser($className);
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
        $parser = $this->class_parser;
        $out = Out::get($this->out_type)
            ->setDir($this->out_dir)
            ->setName($this->out_name)
            ->setClassName($parser->getName())
            ->setNamespace($parser->getNamespace())
            ->setClassComment($parser->getComment())
            ->setMethods($parser->getMethods())
            ->execute();
    }
}
