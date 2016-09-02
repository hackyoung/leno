<?php
namespace Leno\Http;

class Request extends \GuzzleHttp\Psr7\Request
{
    private $attribute = [];

    private $ip;

    public function input()
    {
        return file_get_contents('php://input', 'r');
    }

    public function withAttribute($attr, $val)
    {
        $this->attribute[$attr] = $val;
		return $this;
    }

    public function getAttribute($attr)
    {
        if(!isset($this->attribute[$attr])) {
             throw new AttributeNotFoundException;
        }
        return $this->attribute[$attr];
    }

    public function hasAttribute($attr) {
        return isset($this->attribute[$attr]);
    }

    function getIp()
    {
        if ($this->ip) {
            return $this->ip;
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $this->ip = getenv("HTTP_CLIENT_IP");
        } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
            $this->ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif(getenv("REMOTE_ADDR")) {
            $this->ip = getenv("REMOTE_ADDR");
        }
        return $this->ip;
    }

    public static function getNormal()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'get';
        return new self($method, $uri, getallheaders());
    }
}
