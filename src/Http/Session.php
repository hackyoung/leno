<?php
namespace Leno\Http;

class Session
{
    use \Leno\Traits\Magic;
    use \Leno\Traits\Singleton;

    private function __construct() {}

    private function __clone () {}

    public function __call($method, array $args = [])
    {
        return $this->__magic_call($method, $args);
    }

    public function set(string $key, $value)
    {
        session_start();
        $_SESSION[$key] = serialize($value);
    }

    public function get(string $key)
    {
        session_start();
        return unserialize($_SESSION[$key]);
    }
}
