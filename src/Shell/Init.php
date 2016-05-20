<?php
namespace Leno\Shell;

class Init extends \Leno\Shell
{
    protected $template_dir = __DIR__ . '/hello_world';

    protected $needed_args = [
        'main' => [
            'description' => '初始化项目',
            'args' => [
                'destination' => [
                    'description' => '项目根目录',
                    'looks' => ['-r', '--root'],
                ],
            ]
        ]
    ];

    public function main()
    {
        $destination = $this->input('destination');
        if(!$destination) {
            $this->help();
            return;
        }
        $this->mvFiles($this->template_dir, $destination);
    }

    public function describe()
    {
        return '初始化环境，生成项目目录树及默认配置';
    }

    private function mvFiles($source, $destination)
    {
        $dir_handler = opendir($source);
        if(!is_dir($destination)) {
            $this->info('创建文件夹：'.$destination);
            mkdir($destination, 0755, true);
        }
        while($filename = readdir($dir_handler)) {
            if($filename == '.' || $filename == '..') {
                continue;
            }
            $sour = $source . '/' . $filename;
            $dest = $destination . '/' . $filename;
            if(is_dir($sour)) {
                $this->mvFiles($sour, $dest);
                continue;
            }
            $this->info('创建文件：'.$dest);
            $content = file_get_contents($sour);
            file_put_contents($dest, $content);
        }
    }
}
