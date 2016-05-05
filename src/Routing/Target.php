<?php
namespace Leno\Routing;

class Target extends \ReflectionClass
{
    protected $the_instance;

    protected $method;

    protected $parameters = [];

    protected $constructParameters = [];

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setConstructParameters($parameters)
    {
        $this->constructParameters = $parameters;
        return $this;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function invoke($method = null, $instance=null)
    {
        if($method == null) {
            $method = $this->method;
        }
        if($instance == null) {
            $instance = $this->getInstance();
        }
        if(!$this->hasMethod($method)) {
            throw new \Leno\Http\Exception(404);
        }
        return $this->getMethod($method)->invokeArgs(
            $instance, $this->parameters
        );
    }

    public function getInstance()
    {
        if(!$this->the_instance) {
            $this->the_instance = $this->newInstanceArgs($this->constructParameters);
        }
        return $this->the_instance;
    }
}
