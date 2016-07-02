<?php
namespace Leno\Shell;

use \Leno\Database\Table;
use \Leno\Type;

class Build extends \Leno\Shell
{
    protected $needed_args = [
        'main' => [],
        'db' => [
            'description' => '通过Entity建立数据库',
            'args' => [
                'entitydir' => [
                    'description' => 'Entity目录',
                    'looks' => [ '-ed', '--entity-dir' ],
                ],
                'namespace' => [
                    'description' => 'Entity的名字空间',
                    'looks' => [ '-n', '--namespace' ],
                ]
            ]
        ]
    ];

    public function main()
    {
        $this->help();
    }

    public function db()
    {
        $entity_dir = $this->input('entitydir');
        if(!$entity_dir) {
            return $this->handleHelp('db');
        }
        $namespace = $this->input('namespace') ?? '';
        $this->synDb($entity_dir, $namespace);
    }

    protected function synDb($entity_dir, $namespace)
    {
        if(!is_dir($entity_dir)) {
            $this->error($entity_dir . ' Is Not A Dir');
            return;
        }
        $dir_handler = opendir($entity_dir);
        while($filename = readdir($dir_handler)) {
            if($filename === '.' || $filename === '..') {
                continue;
            }
            $pathfile = $entity_dir . '/' .$filename;
            if(is_dir($pathfile)) {
                $the_namespace = implode('\\', [$namespace, camelCase($filename)]);
                $this->synDb($pathfile, $the_namespace);
                continue;
            }
            if(preg_match('/\.php$/', $filename)) {
                $className = $namespace .'\\'. preg_replace('/\.php$/', '', $filename);
                $this->info('找到Entity: <keyword>'.$className.'</keyword>');
                if(!class_exists($className)) {
                    $this->warn('class: <error>'.$className.'</error> Not Found');
                    continue;
                }
                try {
                    $this->synTable($className);
                } catch(\Exception $e) {
                    $this->warn($e->getMessage());
                }
            }
        }
    }

    protected function synTable($Entity)
    {
        $re = new \ReflectionClass($Entity);
        if(!$re->hasProperty('table') || !$re->hasProperty('attributes')) {
            $this->warn('Find A Class: ' . $Entity. ' But No Table\attributes Assign Ingnore');
            logger()->warn('Find A Class: ' . $Entity. ' But No Table\attributes Assign Ingnore');
            return;
        }
        $table = new Table($re->getStaticPropertyValue('table'));
        foreach($re->getStaticPropertyValue('attributes') as $field => $info) {
            $attr = [
                'type' => Type::get($info['type'])->setExtra($info['extra'] ?? [])->toType(),
                'null' => 'NOT NULL',
            ];
            if (($info['null'] ?? true) === false) {
                $attr['null'] = 'NULL';
            }
            if($info['default'] ?? false) {
                $attr['default'] = $info['default'];
            }
            $table->setField($field, $attr);
        }
        $table->save();
        if($table->lastSql()) {
            $this->info('执行SQL：'.$table->lastSql());
        } else {
            $this->notice('不需要更新');
        }
    }

    public function describe()
    {
        return "同步Entity与数据库";
    }
}
