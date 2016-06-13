<?php
namespace Leno\Http;

class Cookie
{
    use \Leno\Traits\Magic;

    protected $expire = 0;

    protected $path = '/';

    protected $domain = "";

    protected $secure = false;

    protected $http_only = false;

    protected $key;

    protected $value;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function __call($method, array $args = null)
    {
        return $this->__magic_call($method, $args);
    }

    public function set($value)
    {
        $this->value = $value;
        $this->save();
        return $this;
    }

    public function remove()
    {
        $this->value = null;
        $this->save();
        return $this;
    }

    public function get()
    {
        return $this->value ?? $_COOKIE[$this->key] ?? null;
    }

    protected function save()
    {
        setcookie($this->key, $this->value,
            $this->expire, $this->path, $this->domain,
            $this->secure, $this->http_only
        );
    }
}
