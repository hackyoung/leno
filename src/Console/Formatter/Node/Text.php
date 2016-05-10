<?php
namespace Leno\Console\Formatter\Node;

class Text extends \Leno\Console\Formatter\Node
{
    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    public function format()
    {
        return sprintf($this->format, $this->text);
    }
}
