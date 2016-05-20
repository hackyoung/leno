<?php
namespace Leno\Shell;

class Init extends \Leno\Shell
{
    protected $template_dir = __DIR__ . 'hello_world';

    public function main()
    {
    }

    public function description()
    {
        return '初始化环境，生成项目目录树及默认配置';
    }
}
