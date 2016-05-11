<?php
namespace Leno\Doc;

abstract class Out
{
    const TYPE_MD = 'md';

    const TYPE_HTML = 'html';

    const TYPE_PDF = 'pdf';

    public static $type = [
        self::TYPE_MD => '\\Leno\\Doc\\Out\\Markdown',
        self::TYPE_HTML => '\\Leno\\Doc\\Out\\Html',
        self::TYPE_PDF => '\\Leno\\Doc\\Out\\Pdf',
    ];

    protected $suffix = '.txt';

    protected $dir;

    protected $class;

    protected $file_name;

    protected $namespace;

    protected $template = __DIR__ . '/template/text.txt';

    protected $class_info;

    public function __call($method, $arguments = null)
    {
        $prefix = substr($method, 0, 3);
        switch($prefix) {
            case 'get':
                return $this->class->$method($arguments[0]);
        }
        throw new \Leno\Exception($method . ' Not Defined');
    }

    public function setDir($dir)
    {
        $this->dir = $dir;
        return $this;
    }

    public function setFileName($name)
    {
        $this->file_name = $name;
        return $this;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public static function get($type)
    {
        if(!isset(self::$type[$type])) {
            throw new \Leno\Exception($type . ' Not Supported!');
        }
        $class = self::$type[$type];
        return new $class;
    }

    public function execute()
    {
        $content = include($this->template);
        file_put_contents($this->dir . '/' .  $this->file_name . $this->suffix, $content);
    }
}
