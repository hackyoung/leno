<?php
namespace Leno;

use \Leno\View\Fragment;
use \Leno\View\Template;

class View 
{
    /**
     *  view 文件的后缀名
     */
    const SUFFIX = '.lpt.php';

    const TYPE_REPLACE = 'replace';

    const TYPE_BEFORE = 'before';

    const TYPE_AFTER  = 'after';

    protected static $global_data = [];

    protected static $singleton = [
        '___js___' => [],
        '___css___' => [],
        '___singleton___' => []
    ];

    protected static $js_src = '';

    /**
     * view 的查找路径, 通过View::addViewDir(); 
     * View::deleteViewDir()两个方法来配置View的搜索路径,
     * View::addViewDir('test');
     * View::$dir = ['test'];
     * View::deleteViewDir('test');
     * View::$dir = [];
     */
    protected static $dir = [
        'leno' => [__DIR__ . '/template'],
    ];

    /**
     * array data View对象可以使用的数据,通过View::set方法来设置它
     */
    public $data = [
        '__head__' => [
            'title' => 'leno',
            'keywords' => 'leno,hackyoung,view',
            'description' => 'a simple framework component',
            'author' => 'hackyoung@163.com',
            'js' => [],
            'css' => [],
        ]
    ];

    protected static $templateClass = '\Leno\View\Template';

    /**
     * Template template 处理该模板文件的Template对象
     */
    protected $template;

    /**
     * array view 组合的View
     */
    protected $view = [];

    /**
     * View parent 该View的父亲View
     */
    protected $parent;

    /**
     * View child 继承该View的View对象
     */
    protected $child;

    /**
     *  array 该View拥有的Fragment
     */
    protected $fragments = [];

    /**
     * string file View对象的模板文件的绝对路径文件
     */
    private $path;

    private $file;

    /**
     * string temp_name start/endFragment的时候用
     */
    private $temp_fragment = [];


    private $temp_view;

    /**
     * 主题
     */
    private $theme = 'default';

    /**
     * 构造函数
     * @param string $view 基于查找路径的view文件
     * @param array $data 模板需要用的参数
     */
    public function __construct($view, array $data = [])
    {
        $this->path = $view;
        if(isset($data['__head__'])) {
            $head = array_merge($this->__head__, $data['__head__']);
        } else {
            $head = $this->__head__;
        }
        $data['__head__'] = $head;
        $this->data = array_merge($this->data, $data);
        $this->template = self::newTemplate($this);
    }

    public function __toString() 
    {
        ob_start();
        $this->render();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function __set($key, $val)
    {
        $this->set($key, $val);
        return $this;
    }

    public function setGlobal($id, $global)
    {
        \Leno\View::$global_data[$id] = $global;
        return $this;
    }

    public function getGlobal($id)
    {
        return \Leno\View::$global_data[$id] ?? null;
    }

    /**
     * 判断该view是否有名为name的fragment
     * @param string $name 索引的fragment的名字
     * @return boolean
     */
    public function hasFragment($name) 
    {
        return isset($this->fragments[$name]);
    }

    /**
     * 通过name获取view有的一个fragment
     * @param string $name 索引的fragment名字
     */
    public function getFragment($name) 
    {
        if(!$this->hasFragment($name)) {
            throw new \Exception(
                sprintf('fragment %s not found', $name)
            );
        }
        return $this->fragments[$name];
    }

    /**
     * 显示一个view
     */
    public function display() 
    {
        if(!$this->parent instanceof self && gettype($this->data) === 'array') {
            extract($this->data);
        }
        include $this->template->display();
    }

    public function render()
    {
        return $this->display();
    }

    /**
     * 设置一个变量，在模板中使用
     * @param string $var 在模板中使用的变量名
     * @param mixed value 变量的值
     */
    public function set($var, $value) 
    {
        $this->data[$var] = $value;
    }

    /**
     * 获得一个组合的子View
     * @param string $idx self::view定义的索引名
     * @return View 通过$idx返回的view对象
     */
    public function e($idx) 
    {
        return $this->view[$idx];
    }

    /**
     * 添加一个组合的子View
     * @param string $idx 用于索引的名字
     * @param View $view View对象
     * @param boolean $data 是否复制该View的data到子View
     */
    public function view($idx, $view, $data=false) 
    {
        if($data) {
            foreach($this->data as $k=>$v) {
                $view->set($k,$v);
            }
        }
        $this->view[$idx] = $view;
        return $view;
    }

    public function v($idx)
    {
        return $this->view[$idx];
    }

    /**
     * 继承一个View，可以实现或者重写上一个View的fragment
     * @param string $file view的模板文件
     */
    public function extend($file) 
    {
        $this->parent = new View($file, $this->data);
        $this->parent->setChild($this);
    }

    /**
     * 设置孩子，仅仅在self::extend中执行才有效
     */
    public function setChild($child) 
    {
        if($child->parent->equal($this)) {
            $this->child = $child;
        }
        return $this;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
        return $this;
    }

    /**
     * 判断当前view是否和所传view相等
     * @param View $view 待比较的view对象
     */
    public function equal($view)
    {
        return ($this->getFile() === $view->getFile());
    }

    /**
     * 获得该View的模板文件的绝对路径
     */
    public function getFile() 
    {
        if(!$this->file) {
            $this->file = $this->searchFile();
        }
        return $this->file;
    }

    public function addJs($js) {
        if(is_array($js)) {
            $this->data['__head__']['js'] = array_merge(
                $this->__head__['js'], $js
            );
        } else {
            $this->data['__head__']['js'][] = $js;
        }
    }

    public function addCss($css) {
        if(is_array($css)) {
            $this->data['__head__']['css'] = array_merge(
                $this->__head__['css'], $css
            );
        } else {
            $this->data['__head__']['css'][] = $js;
        }
    }


    protected function startView($name, $data = [], $expend_data = false)
    {
        $view = new self($name, $data);
        $this->view($name, $view, $expend_data);
        $this->temp_view = $name;
    }

    protected function endView()
    {
        $name = $this->temp_view;
        $this->v($name)->display();
        $this->temp_view = null;
    }

    /**
     * 在模板文件中使用，标记从该方法之后的内容为一个fragment的内容
     * @param string $name fragment的名字
     */
    public function startFragment($name, $type = self::TYPE_REPLACE, $show = false) 
    {
        $this->temp_fragment[] = [
            'name' => $name,
            'show' => $show,
            'type' => $type
        ];
        ob_start();
    }

    /**
     * 在模板文件中使用，标记一个fragment内容结束的地方
     */
    public function endFragment()
    {
        $spe = ['___js___', '___css___', '___singleton___'];
        $temp = array_pop($this->temp_fragment);
        $name = $temp['name'];
        if(empty($name)) {
            return;
        }
        $content = ob_get_contents();
        ob_end_clean();
        if(in_array($name, $spe)) {
            $fragment = new Fragment(self::showSingleton($name));
        } else {
            $fragment = new Fragment($content);
        }
        if($this->child && $this->child->hasFragment($name)) {
            $fragment->setChild($this->child->getFragment($name));
        }
        $this->fragments[$name] = [
            'type' => $temp['type'],
            'fragment' => $fragment
        ];
        if(!$this->parent instanceof self || $temp['show']) {
            $fragment->display();
        }
    }

    protected function searchFile()
    {
        $path = $this->path;
        $base_dirs = $this->getBaseDirs();
        if(!is_array($base_dirs)) {
            throw new \InvalidArgumentException(
                sprintf("%s is not exists", $path)
            );
        }
        $last = str_replace(".", "/", $this->path) . self::SUFFIX;
        foreach($base_dirs as $dir) {
            $file = preg_replace('/\/$/', '', $dir) . '/' . $last;
            if(is_file($file)) {
                return $file;
            }
        }
        throw new \InvalidArgumentException(
            sprintf("%s is not exists", $path)
        );
    }

    protected function getBaseDirs()
    {
        $path = $this->path;
        foreach(self::$dir as $prefix => $dirs) {
            $rest_path = preg_replace('/^'.$prefix.'\./','', $path);
            if($rest_path === $path) {
                continue;
            }
            $this->path = $rest_path;
            $theme_dirs = array_map(function($dir) {
                return $dir . '/' . $this->theme;
            }, $dirs);
            return array_merge($theme_dirs, $dirs);
        }
        return false;
    }

    public static function beginJsContent($src)
    {
        self::$js_src = $src;
        ob_start();
    }

    public static function endJsContent()
    {
        $content = ob_get_contents();
        ob_end_clean();
        if(empty(self::$js_src)) {
            return self::appendJsContent($content);
        }
        echo '<script src=\''.self::$js_src.'\' type=\'text/javascript\'></script>'."\n";
    }

    public static function appendJsContent($content)
    {
        $content = trim($content) . "\n";
        self::$singleton['___js___'][md5($content)] = $content;
    }

    public static function showJs()
    {
        return "<script type='text/javascript'>\n".implode('', self::$singleton['___js___'])."</script>\n";
    }

    public static function beginCssContent()
    {
        ob_start();
    }

    public static function endCssContent()
    {
        $content = ob_get_contents();
        if(ob_end_clean()) {
            return self::appendCssContent($content);
        }
    }
    public static function showCss()
    {
        return "<style type='text/css' rel='stylesheet'>\n".implode('', self::$singleton['___css___'])."</style>\n";
    }

    public static function appendCssContent($content)
    {
        $content = trim($content) . "\n";
        self::$singleton['___css___'][md5($content)] = $content;
    }

    public static function beginSingletonContent()
    {
        ob_start();
    }

    public static function endSingletonContent()
    {
        $content = ob_get_contents();
        if(ob_end_clean()) {
            return self::appendSingletonContent($content);
        }
    }

    public function showSingleton($name)
    {
        switch($name) {
            case '___singleton___':
                return self::showTheSingleton();
            case '___js___':
                return self::showJs();
            case '___css___':
                return self::showCss();
        }
    }

    public static function showTheSingleton()
    {
        return implode('', self::$singleton['___singleton___']);
    }

    public static function appendSingletonContent($content)
    {
        $content = $content . "\n";
        self::$singleton['___singleton___'][md5($content)] = $content;
    }

    public static function setTemplateClass($templateClass)
    {
        self::$templateClass = $templateClass;
    }

    public static function getTemplateClass()
    {
        return self::$templateClass;
    }

    public static function newTemplate(View $view)
    {
        return new self::$templateClass($view);
    }

    public static function addViewDir($prefix, $dir) 
    {
        if(!is_dir($dir)) {
            return;
        }
        if(!isset(self::$dir[$prefix])) {
            self::$dir[$prefix] = [$dir];
            return;
        }
        if(in_array($dir, self::$dir[$prefix])) {
            return;
        }
        array_unshift(self::$dir[$prefix], $dir);
    }
}
