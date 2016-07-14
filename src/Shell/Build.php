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
            $attr = $info;
            $attr['type'] = Type::get($info['type'])->setExtra($info['extra'] ?? [])->toDbType();
            $table->setField($field, $attr);
        }
        $foreign = $re->getStaticPropertyValue('foreign');
        if (is_array($foreign)) {
            $table->setForeignKeys($this->normalizeForeign($foreign, $table));
        }
        $unique = $re->getStaticPropertyValue('unique');
        if (is_array($unique)) {
            $the_unqiue = [];
            foreach ($unique as $key => $config) {
                $the_unique[$key .'_'. $table] = $config;
            }
            $table->setUniqueKeys($the_unque);
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

    private function normalizeForeign($foreign, $table)
    {
        $the_foreign = [];
        foreach ($foreign as $key => $config) {
            $table = $config['entity']::$table;
            $relation_foreign = $config['foreign_key'];
            if (isset($config['bridge'])) {
                $table = $config['bridge']['entity']::$table;
                $relation_foreign = $config['bridge']['local'];
            }
            $the_key = $key.'_'.$table.'_fk';
            $the_foreign[$the_key] = [
                'foreign_table' => $table,
                'relation' => []
            ];
            if (!is_array($config['local_key'])) {
               $the_foreign[$the_key]['relation'][$config['local_key']] = $relation_foreign;
               continue;
            }
            foreach ($config['local_key'] as $key => $local_key) {
                $the_foreign[$the_key]['relation'][$local_key] = $relation_foreign[$key];
            }
        }
        return $the_foreign;
    }
}
