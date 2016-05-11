<?php
namespace Leno\Shell;
use \Leno\Doc\ClassDoc;

class Doc extends \Leno\Shell
{
    public function main($inputdir, $outputdir, $namespace = "")
    {
        $input = realpath($inputdir);
        if(!is_dir($input)) {
            throw new \Exception ($inputdir . " Is Not A Directory");
        }
        $dir_handler = new Dir($input);
        $output = realpath($outputdir);
        if(!is_dir($output)) {
            mkdir($output, 0755, true);
        }
        while($filename = readdir($dir_handler)) {
            if($filename === '.' || $filename === '..') {
                continue;
            }
            $pathfile = $input . '/' .$filename;
            if(is_dir($pathfile)) {
                $outputdir = $output . '/' . $filename;
                $namespace = implode('\\', [$namespace, camelCase($filename)]);
                $this->main($pathfile, $outputdir, $namespace);
                continue;
            }
            if(!preg_match('/\.php$/', $filename)) {
                $className = $namespace .'\\'. preg_replace('/\.php$/', $filename);
                (new ClassDoc($className))
                    ->setOutType(ClassDoc::TYPE_MD)
                    ->setOutDir($output)
                    ->setOutName($filename)
                    ->execute();
            }
        }
    }
}
