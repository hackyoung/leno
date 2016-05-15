<?php
namespace Leno\Shell;
use \Leno\Doc\ClassDoc;
use \Leno\Doc\Out;

class Doc extends \Leno\Shell
{
    protected $needed_args = [ 'main' => [
        'inputdir' => [ 'inputdir', 'i'],
        'outputdir' => [ 'outputdir', 'o'],
        'namespace' => [ 'namespace', 'n']
    ]];

    public function main()
    {
        $input = $this->input('inputdir');
        $output = $this->input('outputdir');
        $namespace = $this->input('namespace') ?? "";
        $this->generateDoc($input, $output, $namespace);
    }

    private function generateDoc($input, $output, $namespace = "")
    {
        if(!is_dir($input)) {
            $this->error($input . " Is Not A Directory");
            return;
        }
        $dir_handler = opendir($input);
        if(!is_dir($output)) {
            mkdir($output, 0755, true);
        }
        while($filename = readdir($dir_handler)) {
            if($filename === '.' || $filename === '..') {
                continue;
            }
            $pathfile = $input . '/' .$filename;
            if(is_dir($pathfile)) {
                $outputdir = $output . $filename;
                $namespace = implode('\\', [$namespace, camelCase($filename)]);
                $this->generateDoc($pathfile, $outputdir, $namespace);
                continue;
            }
            if(preg_match('/\.php$/', $filename)) {
                $className = $namespace .'\\'. preg_replace('/\.php$/', '', $filename);
                try {
                    $doc = new ClassDoc($className);
                } catch(\Exception $ex) {
                    continue;
                }
                $doc->setOutType(Out::TYPE_MD)
                    ->setOutDir($output)
                    ->setOutName($filename)
                    ->execute();
            }
        }
    }

    public function help($commend = null)
    {
    }

    public function description()
    {
    }
}
