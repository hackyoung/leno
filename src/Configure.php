<?php
namespace Leno;

abstract class Configure
{
    const DEFAULT_BASE  = ROOT . '/config';

    const DEFAULT_CONFIG = 'default';

    protected static $instances = [];

    protected static $parser_map = [
        'php' => '\\Leno\\Configure\\PhpConfigure',
        'ini' => '\\Leno\\Configure\\IniConfigure',
        'json' => '\\Leno\\Configure\\JsonConfigure'
    ];

    protected $config;

    protected $base_dir;

    private $pathfile;

    abstract protected function parse($file) : array;

    abstract protected function store() : string;

    public function __construct($pathfile)
    {
        $this->pathfile = $pathfile;
        $this->setBase(self::DEFAULT_BASE);
    }

    public function readKey($key)
    {
        $keys = explode('.', $key);

        $result = $this->config;
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                return null;
            }

            $result = $result[$key];
        }

        return $result;
    }

    public function writeKey($key, $value)
    {
        $keys = explode('.', $key);

        $result = &$this->config;
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result = &$result[$key];
        }
        $result = $value;

        return $this;
    }

    public function setBase($base)
    {
        $this->base_dir = $base;
        $pathfile = $this->base_dir . '/' . $this->pathfile;
        $this->config = $this->parse($pathfile);

        return $this;
    }

    public function save()
    {
        $content = $this->store();
        $file = $this->base_dir . '/' . trim($this->pathfile, '/');

        file_put_contents($file, $content);
    }

    public static function read($file, $key = null)
    {
        if ($key === null) {
            $key = $file;
            $file = self::DEFAULT_CONFIG;
        }
        if ($config = self::getConfig($file)) {
            return $config->readKey($key);
        }
    }

    public static function write($file, $key, $value = null)
    {
        if ($value === null) {
            $value = $key;
            $key = $file;
            $file = self::DEFAULT_CONFIG;
        }
        if ($config = self::getConfig($file)) {
            return $config->writeKey($key, $value);
        }
    }

    public static function getConfig($file)
    {
        return self::$instances[$file];
    }

    public static function init($default_base = null)
    {
        $default_base = $default_base ?? self::DEFAULT_BASE;
        if (!is_dir($default_base)) {
            throw new \Exception('配置目录不是一个目录');
        }
        $dir = dir($default_base);
        $count = 1;
        while ($file = $dir->read()) {
            if ($count++ > 5) {
                break;
            }
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            if (preg_match('/^\./', $file)) {
                continue;
            }
            $pathfile = $default_base . '/' . $file;
            if (is_dir($pathfile)) {
                self::init($pathfile);
                continue;
            }
            self::instance(str_replace(self::DEFAULT_BASE, '', $pathfile));
        }
    }

    public static function instance($pathfile)
    {
        $key = str_replace('/', '.', preg_replace('/\..*$/', '', trim($pathfile, '/')));
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        if (preg_match('/\.php$/', $pathfile)) {
            $class = self::$parser_map['php'];
        } elseif (preg_match('/\.ini/', $pathfile)) {
            $class = self::$parser_map['ini'];
        }
        return self::$instances[$key] = new $class($pathfile);
    }
}
