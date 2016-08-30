<?php
namespace Leno\View;

class Fragment 
{
    private $content;

    /**
     * struct  [
     *      'type' => , \Leno\View::TYPE_REPLACE | \Leno\View::TYPE_AFTER | \Leno\View::TYPE_BEFORE
     *      'fragment' => \Leno\View\Fragment
     * ];
     */
    private $child;

    public function __construct($content)  
    {
        $this->content = $content;
    }

    public function setChild($child)
    {
        $this->child = $child;
        return $this;
    }

    public function display() 
    {
        echo $this->getContent();
    }

    /**
     * 获得该Fragment的所有内容（包括孩子的内容）
     */
    public function getContent()
    {
        $content = $this->content;
        if($this->child) {
            $fragment = $this->child['fragment'];
            $type = $this->child['type'];
            if($type === \Leno\View::TYPE_REPLACE) {
                $content = $fragment->getContent();
            } elseif ($type === \Leno\View::TYPE_AFTER) {
                $content .= $fragment->getContent();
            } elseif ($type === \Leno\View::TYPE_BEFORE) {
                $content = $fragment->getContent() . $content;
            }
        }
        return $content;
    }

    public function __toString() {
        return $this->getContent();
    }
}
