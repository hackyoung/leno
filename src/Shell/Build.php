<?php
namespace Leno\Shell;

class Build extends \Leno\Shell
{
    protected $needed_args = [
        'main' => [],
        'db' => [
            'entitydir' => [
                'ed',
                'entity-dir'
            ],
            'namespace' => [
                'n',
                'namespace'
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
                $this->notice('找到Entity: <keyword>'.$className.'</keyword>');
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
        $attributes = $Entity::$attributes ?? false;
        if(!$attributes) {
            throw new \Exception($Entity . ' May Be Not A Entity');
        }
        $table = $Entity::$table ?? false;
        if(!$table) {
            throw new \Exception($Entity . ' May Be Not A Entity');
        }
        $table = new \Leno\ORM\Table($table);
        foreach($attributes as $field => $info) {
            $type = $this->getTypeFromRule($info);
            $attr = [];
            if($type instanceof \Leno\Validator\Type\Uuid) {
                $attr['type'] = 'char(36)';
            } elseif($type instanceof \Leno\Validator\Type\Uri) {
                $attr['type'] = 'varchar(1024)';
            } elseif($type instanceof \Leno\Validator\Type\Url) {
                $attr['type'] = 'varchar(1024)';
            } elseif($type instanceof \Leno\Validator\Type\Number) {
                $attr['type'] = 'int(11)';
            } elseif($type instanceof \Leno\Validator\Type\Stringl) {
                $attr['type'] = 'varchar('.$type->getMaxLength().')';
            } elseif($type instanceof \Leno\Validator\Type\Enum) {
                $attr['type'] = 'varchar(32)';
            } else {
                $attr['type'] = $info['type'];
            }
            if($info['required'] ?? true === false) {
                $attr['null'] = 'NULL';
            } else {
                $attr['null'] = 'NOT NULL';
            }
            if($info['default'] ?? false) {
                $attr['default'] = $info['default'];
            }
            $table->setField($field, $attr);
        }
        $table->save();
        if($table->lastSql()) {
            $this->notice('执行SQL：'.$table->lastSql());
        } else {
            $this->notice('不需要更新');
        }
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

    public function help($commend = null)
    {
    }

    public function description()
    {
    }
}
