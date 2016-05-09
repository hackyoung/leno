<?php
namespace Leno\Service\Remote;

class Parameter implements \Leno\Service\Remote\ParameterInterface
{
    protected $data = [];

    public function set($key, $val)
    {
        $this->data[$key] = $val;
        return $this;
    }

    public function setData($data)
    {
        $this->data = array_merge_recursive(
            $this->data, $data
        );
        return $this;
    }

    public function useIt()
    {
        return $this->data;
    }
}
