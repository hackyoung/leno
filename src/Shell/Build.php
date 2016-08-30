<?php
namespace Leno\Shell;

use \Leno\Database\Table;
use \Leno\Database\Constraint\Foreign;
use \Leno\Database\Adapter;
use \Leno\Type;

class Build extends \Leno\Shell
{
    protected $entities = [];

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
        $entities = $this->getEntities($entity_dir, $namespace);
        Adapter::get()->beginTransaction();
        try {
            $this->synColumns($entities);
            $this->synForeignKeys($entities);
            Adapter::get()->commitTransaction();
        } catch (\Exception $ex) {
            Adapter::get()->rollback();
            throw $ex;
        }
    }

    protected function synColumns($entities)
    {
        $this->info('---------------------开始同步表结构----------------------------');
        foreach ($entities as $entity_name => $re) {
            $this->info('同步Entity:<keyword>'.$entity_name.'</keyword>');
            $table_name = $re->getMethod('getTableName')->invoke();
            $table = new Table($table_name);
            $attrs = $re->getMethod('getAttributes')->invoke();
            foreach($attrs as $field => $info) {
                $attr = $info;
                $attr['type'] = Type::get($info['type'])->setExtra($info['extra'] ?? [])->toDbType();
                $table->setField($field, $attr);
            }
            $table->setPrimaryKey($re->getStaticPropertyValue('primary'));
            $unique = $re->getMethod('getUnique')->invoke(null);
            if (is_array($unique)) {
                $the_unique = [];
                foreach ($unique as $key => $config) {
                    $the_unique[$key.'_'.$table_name.'_uk'] = $config;
                }
                $table->setUniqueKeys($the_unique);
            }
            $table->save();
        }
    }

    protected function synForeignKeys($entities)
    {
        $this->info('----------------------开始同步外键-----------------------------');
        foreach ($entities as $entity_name=>$re) {
            $foreign = $re->getMethod('getForeign')->invoke();
            $table = $re->getMethod('getTableName')->invoke();
            if (is_array($foreign)) {
                (new Foreign($table, $this->normalizeForeign($foreign, $entity_name)))->save();
                $this->info('为<keyword>'.$entity_name.'</keyword>创建外键');
                continue;
            }
            $this->warn('<keyword>'.$entity_name.'</keyword>没有外键定义');
        }
    }

    public function describe()
    {
        return "同步Entity与数据库";
    }

    private function getEntities($entity_dir, $namespace)
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
                $this->entities += $this->getEntities($pathfile, $the_namespace);
                continue;
            }
            if(preg_match('/\.php$/', $filename)) {
                $className = $namespace .'\\'. preg_replace('/\.php$/', '', $filename);
                if(!class_exists($className)) {
                    continue;
                }
                $re = new \ReflectionClass($className);
                $table = $re->getMethod('getTableName')->invoke();
                $attr = $re->getMethod('getAttributes')->invoke();
                if(!$table || !$attr) {
                    continue;
                }
                $this->entities[$className] = $re;
            }
        }
        return $this->entities;
    }

    private function normalizeForeign($foreign, $entity_class)
    {
        $the_foreign = [];
        foreach ($foreign as $key => $config) {
            if (isset($config['bridge']) || ($config['is_array'] ?? false)) {
                continue;
            }
            $table = $config['entity']::getTableName();
            $relation_foreign = $config['foreign_key'];
            $the_key = $entity_class::getForeignKeyName($key);
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
