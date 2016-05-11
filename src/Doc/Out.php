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

    protected $dir;

    protected $name;

    protected $class_info;

    public function __call($method, $arguments = null)
    {
    
    }

    public function setDir()
    {
    }

    public function setName()
    {
    }

    public static function get($type)
    {
        if(!isset(self::$type[$type])) {
            throw new \Leno\Exception($type . ' Not Supported!');
        }
        $class = self::$type[$type];
        return new $class;
    }
}
