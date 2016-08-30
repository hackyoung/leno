<?php
namespace Leno\View;

/**
 * View的模板处理类，一个View对应一个模板文件
 * Template对象负责将模板文件编译成PHP可执行
 * 的样子
 */
class Template 
{
    /**
     * 编译之后的文件后缀
     */
    const SUFFIX = '.view.php';

    private static $cachedir;

    protected $cachefile;

    protected $view;

    protected $node_token_class = [
        '\Leno\View\Token\SingletonContentBegin',
        '\Leno\View\Token\SingletonContentEnd',
        '\Leno\View\Token\JsContentBegin',
        '\Leno\View\Token\JsContentEnd',
        '\Leno\View\Token\CssContentBegin',
        '\Leno\View\Token\CssContentEnd',
        '\Leno\View\Token\View',
        '\Leno\View\Token\Extend',
        '\Leno\View\Token\ExtendEnd',
        '\Leno\View\Token\Fragment',
        '\Leno\View\Token\StartFragment',
        '\Leno\View\Token\EndFragment',
        '\Leno\View\Token\StartView',
        '\Leno\View\Token\EndView',
        '\Leno\View\Token\EmptyToken',
        '\Leno\View\Token\EmptyEnd',
        '\Leno\View\Token\Llist',
        '\Leno\View\Token\LlistEnd',
        '\Leno\View\Token\In',
        '\Leno\View\Token\InEnd',
        '\Leno\View\Token\Eq',
        '\Leno\View\Token\EqEnd',
        '\Leno\View\Token\Neq',
        '\Leno\View\Token\NeqEnd',
        '\Leno\View\Token\Nin',
        '\Leno\View\Token\NinEnd',
        '\Leno\View\Token\NotEmpty',
        '\Leno\View\Token\NotEmptyEnd',
        '\Leno\View\Token\VarToken',
        '\Leno\View\Token\Func',
        '\Leno\View\Token\StaticMethod',
    ];

    public function __construct($view) 
    {
        $this->view = $view;
        $file = str_replace('/', '_', $view->getFile());
        $this->cachefile = self::$cachedir.'/'.$file.self::SUFFIX;
    }

    public function display() 
    {
        if(!is_file($this->cachefile) || filemtime($this->cachefile) <= filemtime($this->view->getFile())) {
            $this->compile();
        }
        return $this->cachefile;
    }

    private function getContent()
    {
        return file_get_contents($this->view->getFile());
    }

    private function cacheContent($content)
    {
        file_put_contents($this->cachefile, $content);
    }

    protected function compile()
    {
        $content = $this->getContent();
        foreach($this->node_token_class as $class) {
            $node_token = new $class($content);
            $content = $node_token->replace();
        }
        $this->cacheContent($content);
        return $this->cachefile;
    }

    public static function setCacheDir($dir) 
    {
        self::$cachedir = $dir;
    }

    public static function getCacheDir()
    {
        return self::$cachedir;
    }
}
