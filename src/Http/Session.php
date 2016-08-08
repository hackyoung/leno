<?php
namespace Leno\Http;

class Session
{
    const STARTED = 1;

    use \Leno\Traits\Magic;
    use \Leno\Traits\Singleton;

    private $session_state;

    private function __construct() {
        $this->start();
    }

    private function __clone () {}

    public function __call($method, array $args = [])
    {
        return $this->__magic_call($method, $args);
    }

    public function set(string $key, $value)
    {
        $_SESSION[$key] = serialize($value);
    }

    public function get(string $key)
    {
        return unserialize($_SESSION[$key] ?? null);
    }

    private function start()
    {
        if ($this->session_state == self::STARTED) {
            return;
        }
        session_start();
        $this->session_state = self::STARTED;
    }
}
