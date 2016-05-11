<?php
namespace Leno\Doc\Helper;

class MethodHelper
{
    protected $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function profile($class = null)
    {
        $method = $this->method;
        $class = $class ?? $method->getDeclaringClass()->getName();
        $profile = [
            $method->isAbstract() ? 'abstract' : '',
            $method->isStatic() ? 'static' : '',
            $method->isPublic() ? 'public' : '',
            $method->isProtected() ? 'protected' : '',
            $method->isPrivate() ? 'private' : '',
            $class . "::" . $method->getName(),
            '()',
        ];
        return implode(' ', $profile);
    }
}
