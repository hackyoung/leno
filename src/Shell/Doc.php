<?php
namespace Leno\Shell;
use \Leno\Doc\ClassDoc;
use \Leno\Doc\Out;

class Doc extends \Leno\Shell
{
    public function main($inputdir, $outputdir, $namespace = "")
    {
        $input = $inputdir; //realpath($inputdir);
        if(!is_dir($input)) {
            throw new \Exception ($inputdir . " Is Not A Directory");
        }
        $dir_handler = opendir($input);
        $output = $outputdir; //realpath($outputdir) ?? $outputdir;
        $this->debug($output);
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
                $this->debug($outputdir);
                $this->main($pathfile, $outputdir, $namespace);
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
