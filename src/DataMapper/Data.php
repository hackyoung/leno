<?php
namespace Leno\DataMapper;

class Data implements \JsonSerializable, \Iterator
{
    /**
     * @var ['value' => '', 'dirty' => '',];
     */
    protected $data = [];

    /**
     * @var [
     *      'value' => [
     *          'type' => '',
     *          'required' => '', 
     *          'allow_empty' => '', 
     *          'extra' => []
     *      ],
     * ];
     */
    protected $config = [];

    protected $position = 0;

    public function __construct($data = [], $config = null)
    {
        if(is_array($config)) {
            $this->config = $config;
        }
        foreach($data as $k=>$v) {
            $this->set($k, $v, false);
        }
    }

    public function __call($method, $parameters=null)
    {
        if(preg_match('/^get\w+/', $method)) {
            return $this->get(unComelCase(preg_replace('/^get/', '', $method)));
        }
        if(preg_match('/^set\w+/', $method)) {
            return $this->set(unComelCase(preg_replace('/^set/', '', $method)), $paramters);
        }
        throw new \Exception($method . ' Not Defined');
    }

    public function __set($key, $val)
    {
        return $this->set($key, $val);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function set($key, $val, $dirty = true)
    {
        $data = $this->data[$key] ?? null;
        if($data && $val === $data['value']) {
            return;
        }
        $this->data[$key] = [
            'value' => $val, 'dirty' => $dirty
        ];
    }

    public function forStore($key)
    {
        $data = isset($this->data[$key]) ? $this->data[$key]['value'] : null;
        if($data && isset($this->config[$key])) {
            $Type = \Leno\Validator\Type::get($this->type($key));
            $type = new $Type;
            if($type instanceof \Leno\DataMapper\TypeStorage) {
                $data = $type->toStore($data);
            }
        }
        return $data;
    }

    public function get($key)
    {
        $data = isset($this->data[$key]) ? $this->data[$key]['value'] : null;
        if($data && isset($this->config[$key])) {
            $Type = \Leno\Validator\Type::get($this->type($key));
            $type = new $Type;
            if($type instanceof \Leno\DataMapper\TypeStorage) {
                $data = $type->fromStore($data);
            }
        }
        return $data;
    }

    public function isset($key)
    {
        return $this->get($key) ? true : false;
    }

    public function isDirty($key)
    {
        if(!isset($this->data[$key])) {
            throw new \Exception($key . ' Not Found');
        }
        return $this->data[$key]['dirty'];
    }

    public function each($callback)
    {
        foreach($this->data as $key=>$value) {
            if($callback($key, $this) === false) {
                return;
            }
        }
    }

    public function validate($key, $val)
    {
        if(!isset($this->config[$key])) {
            return true;
        }
        $config = $this->config[$key];
        return (new \Leno\Validator($config, $key))->check($val);
    }

    public function validateAll($beforeCheckKey = null)
    {
        foreach($this->config as $k=>$config) {
            $val = $this->get($k);
	        if(isset($beforeCheckKey) && is_callable($beforeCheckKey) && $beforeCheckKey($k, $this) === false){
                continue;
            }
            if(!(new \Leno\Validator($config, $k))->check($val)) {
                throw new \Exception($k . ' Validate Failed');
            }
        }
        return true;
    }

    public function type($key)
    {
        return $this->config($key, 'type');
    }

    public function config($key, $idx = null)
    {
        $config = $this->config[$key] ?? [];
        if(isset($config[$idx])) {
            $config = $config[$idx];
        }
        return $config;
    }

    public function configs()
    {
        return $this->config;
    }
    
    /**实现json**/
    public function jsonSerialize()
    {
        $data = [];
        foreach($this->data as $k=>$val) {
            $type = $this->type($k);
            if($type === 'array') {
                $data[$k] = $val;
                continue;
            }
            if($type === 'json') {
                if(is_string($val)) {
                    $val = json_decode($val, true);
                }
                $data[$k] = $val;
                continue;
            }
            $type = \Leno\Validator\Type::get($this->type($k));
            if($type instanceof \Leno\DataMapper\TypeStorage) {
                $data[$k] = $type->toStore($val['value']);
                continue;
            }
            $data[$k] = $val['value'];
        }
        return $data;
    }

    /**实现iterator**/
    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        if($k = $this->key()) {
            return $this->isset($k);
        }
        return false;
    }

    public function current()
    {
        if($k = $this->key()) {
            return $this->$k;
        }
    }

    public function key()
    {
        $pos = 0;
        $idx = null;
        foreach($this->data as $k => $val) {
            if($pos == $this->position) {
                break;
            }
            $idx = $k;
            $pos++;
        }
        if($pos < $this->position) {
            return false;
        }
        return $k;
    }

    public function next()
    {
        $this->position++;
        $pos = 0;
        foreach($this->data as $k=>$v) {
            if($pos++ === $this->position) {
                return $v['value'];
            }
        }
    }
}
