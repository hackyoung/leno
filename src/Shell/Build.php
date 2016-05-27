<?php
namespace Leno\Shell;

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
                $namespace = implode('\\', [$namespace, camelCase($filename)]);
                $this->synDb($pathfile, $namespace);
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
        if(!$Entity::$table || !$Entity::$attributes) {
            $this->warning('Find A Class: ' . $Entity. ' But Maybe Not A Entity');
            logger()->warn('Find A Class: ' . $Entity. ' But Maybe Not A Entity');
            return;
        }
        $table = new \Leno\ORM\Table($Entity::$table);
        foreach($Entity::$attributes as $field => $info) {
            $attr = [
                'type' => $this->getTypeFromInfo($info),
                'null' => 'NOT NULL',
            ];
            if (($info['required'] ?? true) === false) {
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

    protected function getTypeFromInfo($info)
    {
        $maps = [
            '\Leno\Validator\Type\Uuid' => 'char(36)',
            '\Leno\Validator\Type\Uri' => 'varchar(1024)',
            '\Leno\Validator\Type\Url' => 'varchar(1024)',
            '\Leno\Validator\Type\Datetime' => 'datetime',
            '\Leno\Validator\Type\Number' => 'int(11)',
            '\Leno\Validator\Type\Enum' => 'varchar(32)',
            '\Leno\Validator\Type\Stringl' => function($type) {
                if (empty($type->getMaxLength())) {
                    throw new \Leno\Exception(sprintf('%s has no length!', $field));
                }
                return 'varchar('.$type->getMaxLength().')';
            },
        ];
        $type = $this->getTypeFromRule($info);
        foreach($maps as $class => $guess_type) {
            if(is_callable($guess_type)) {
                $guess_type = call_user_func($guess_type, $type);
            }
            if($type instanceof $class) {
                return $guess_type;
            }
        }
        return $info['type'];
    }

    protected function getTypeFromRule($rule)
    {
        extract($rule['extra'] ?? []);
        $Type = \Leno\Validator\Type::get($rule['type']);
        switch($rule['type']) {
            case 'int':
            case 'integer':
            case 'number':
                $type = new $Type($min ?? null, $max ?? null);
                break;
            case 'string':
                $type = new $Type(
                    $regexp ?? null, 
                    $min_length ?? null,
                    $max_length ?? null
                );
                break;
            case 'enum':
                $type = new $Type($enum_list ?? []);
                break;
            default:
                $type = new $Type;
        }
        return $type;
    }

    public function describe()
    {
        return "同步Entity与数据库";
    }
}
