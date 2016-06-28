<?php
namespace Leno\ORM;

/**
 * Data是一个数据集，通过mapper，可以将它持久化存储，所有数据在写入Data的时候会通过
 * 规则验证其数据完整性,其验证操作依赖Validator
 */
class Data implements \JsonSerializable, \Iterator
{
    /**
     *
     * 保存写入Data的值，其结构为 [
     *  'key' => ['value' => '', 'dirty' => '',],
     * ]
     *
     */
    protected $data = [];

    /**
     *
     * 保存Data的配置信息，这些信息用于参数验证，其结构为 [
     *      'value' => [
     *          'type' => '',
     *          'required' => '', 
     *          'allow_empty' => '', 
     *          'extra' => []
     *      ],
     * ];
     *
     * 见Validator
     *
     */
    protected $config = [];

    /**
     * 为实现Iterator接口使用的变量,记录迭代的当前位置
     */
    protected $position = 0;

    /**
     * 构造函数
     *
     * @param array data 当传入该参数，data的所有写入Data的数据都会默认标记为dirty,其余的set操作则默认标记为不是dirty的数据
     * @param array config 如果传递该参数，则Data的config将会设置为传递的config
     *
     */
    public function __construct(array $data = [], array $config = null)
    {
        if(is_array($config)) {
            $this->config = $config;
        }
        foreach($data as $k=>$v) {
            $this->set($k, $v, false);
        }
    }

    /**
     * call魔术方法,该方法使我们方便的使用set和get方法，
     * @sample 
     * $data = new Data([], ['name' => ['type' => 'string']]);
     * $data->setName('young');         
     * $data->getName();
     * // output young
     *
     * @param method string 方法名
     * @param parameters array 参数
     *
     * @return this return this可以让我们像$data->setName('young')->getName();这样调用
     */
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

    /**
     * setter,设置数据,$data->name = 'young' 等价于 $data->set('name', 'young');
     */
    public function __set($key, $val)
    {
        return $this->set($key, $val);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * 向data里设置值,如果值存在且存在的值等于传入的值，则不做修改
     *
     * @param key string 值的索引
     * @param val mixed 保存在data里的值
     * @param dirty bool 标记该数据在数据库里头取出来之后是否被修改过
     */
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

    /**
     * 从Data里取值,取出来的值供保存至数据库使用
     * @param key string 值的索引
     */
    public function forStore($key)
    {
        $data = isset($this->data[$key]) ? $this->data[$key]['value'] : null;
        if($data && isset($this->config[$key])) {
            $Type = \Leno\Validator\Type::get($this->type($key));
            $type = new $Type;
            if($type instanceof \Leno\Validator\TypeStorage) {
                $data = $type->toStore($data);
            }
        }
        return $data;
    }

    /**
     * 从Data里取值，取出来的值供业务逻辑使用
     * @param key string 值的索引
     */
    public function get($key)
    {
        $data = isset($this->data[$key]) ? $this->data[$key]['value'] : null;
        if($data && isset($this->config[$key])) {
            $Type = \Leno\Validator\Type::get($this->type($key));
            $type = new $Type;
            if($type instanceof \Leno\Validator\TypeStorage) {
                $data = $type->fromStore($data);
            }
        }
        return $data;
    }

    /**
     * 判断提供的索引在Data中有没有值
     * @param key string 值的索引
     */
    public function isset($key)
    {
        return $this->get($key) ? true : false;
    }

    /**
     * 判断提供的索引在Data里的值是否是脏的,如果不存在值，则抛异常
     *
     * @param key string 值的索引
     */
    public function isDirty($key)
    {
        if(!isset($this->data[$key])) {
            throw new \Exception($key . ' Not Found');
        }
        return $this->data[$key]['dirty'];
    }

    /**
     * 迭代已经存在的所有值
     *
     * @param callback callable 迭代方法,如果callback返回false，则停止迭代
     */
    public function each($callback)
    {
        foreach($this->data as $key=>$value) {
            if($callback($key, $this) === false) {
                return;
            }
        }
    }

    /**
     * 验证值是否合法
     *
     * @param string key val的索引
     * @param mixed val 待检查的值
     *
     * @return bool
     */
    public function validate($key, $val)
    {
        if(!isset($this->config[$key])) {
            return true;
        }
        $config = $this->config[$key];
        return (new \Leno\Validator($config, $key))->check($val);
    }

    /**
     * 验证所有的值是否合法
     */
    public function validateAll($beforeCheckKey = null)
    {
        foreach($this->config as $k=>$config) {
            $val = $this->get($k);
	        if(is_callable($beforeCheckKey) && call_user_func_array($beforeCheckKey, [$this]) === false) {
                continue;
            }
            // 默认允许为空
            if(!isset($config['allow_empty'])) {
                $config['allow_empty'] = true;
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
            if($type instanceof \Leno\Validator\TypeStorage) {
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
