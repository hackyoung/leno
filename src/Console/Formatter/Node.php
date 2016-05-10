<?php
namespace Leno\Console\Formatter;

abstract class Node
{
    public static $map = [
        'info' => '\\Leno\\Console\\Formatter\\Node\\Info',
        'notice' => '\\Leno\\Console\\Formatter\\Node\\Notice',
        'error' => '\\Leno\\Console\\Formatter\\Node\\Error',
        'warn' => '\\Leno\\Console\\Formatter\\Node\\Warning',
        'debug' => '\\Leno\\Console\\Formatter\\Node\\Debug',
        'keyword' => '\\Leno\\Console\\Formatter\\Node\\Keyword',
        'text' => '\\Leno\\Console\\Formatter\\Node\\Text',
        'root' => '\\Leno\\Console\\Formatter\\Node\\Root',
    ];

    protected $format = '%s';

    protected $children = [];

    public function format()
    {
        $text = [];
        foreach($this->children as $node) {
            if($node instanceof \Leno\Console\Formatter\Node\Text) {
                $node->setFormat($this->format);
            }
            $text[] = $node->format();
        }
        return implode('', $text);
    }

    public function formatText(\Leno\Console\Formatter\Node\Text $textNode)
    {
        return sprintf($this->format, $textNode->getText());
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild($childNode)
    {
        $this->children[] = $childNode;
        return $this;
    }

    public static function getNode($name)
    {
        if(!isset(self::$map[$name])) {
            throw new \Leno\Exception('Node: '.$name.' Not Found');
        }
        $class = self::$map[$name];
        return new $class;
    }
}
