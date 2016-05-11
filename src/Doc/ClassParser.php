<?php
namespace Leno\Doc;

class ClassParser extends \ReflectionClass
{
    public function getComment()
    {
        return new \Leno\Doc\CommentParser(
            $this->getDocComment()
        );
    }

    public function getMethods()
    {
        $methods = parent::getMethods();
        $list = [];
        foreach($methods as $method) {
            $list[] = new \Leno\Doc\MethodParser($method);
        }
        return $list;
    }
}
