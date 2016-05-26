<?php
namespace Leno\Http;

class Cookie
{
    use \Leno\Traits\Setter;

    protected $expire = 0;

    protected $path = '/';

    protected $domain = "";

    protected $secure = false;

    protected $http_only = false;

    public function set($key, $value)
    {
        setcookie($key, $value,
            $this->expire, $this->path, $this->domain,
            $this->secure, $this->http_only
        );
        return $this;
    }

    public static function get($key)
    {
        return $_COOKIE[$key] ?? null;
    }
}
