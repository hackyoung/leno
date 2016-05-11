<?php
namespace Leno\Doc;

class CommentParser
{
    protected $comment;

    protected $position = 0;

    protected $comment_node = '';

    protected $comment_nodes = [];

    protected $tags = [];

    public function __construct($comment)
    {
        $this->comment = preg_replace(
            "/(\/\*+)|(\*+\/)|\n|\*/", '', $comment
        );
        $this->execute();
    }

    public function __call($method, $arguments=null)
    {
        $prefix = substr($method, 0, 3);
        switch($prefix) {
            case 'get':
                $tag = strtolower(preg_replace('/^get/', '', $method));
                if(isset($this->tags[$tag])) {
                    return $this->tags[$tag];
                }
                throw new \Exception($tag . ' Not Defined');
        }
        throw new \Exception($method . ' Not Defined');
    }

    public function getTags()
    {
        return array_keys($this->tags);
    }

    public function execute()
    {
        $this->getToken();
        $stack = [];
        foreach($this->comment_nodes as $node) {
            if(preg_match('/^@/', $node)) {
                $this->handleStack($stack);
                $stack = [$node];
                continue;
            }
            $stack[] = $node;
        }
        $this->handleStack($stack);
        return $this;
    }

    public function getToken()
    {
        while(($char = $this->getChar()) !== false) {
            switch($char) {
                case "@":
                    $this->comment_nodes[] = $this->comment_node;
                    $this->comment_node = "@";
                    break;
                case " ":
                case "\t":
                case "\n":
                    if(substr($this->comment_node, 0, 1) === '@') {
                        $this->comment_nodes[] = $this->comment_node;
                        $this->comment_node = $char;
                        break;
                    }
                default:
                    $this->comment_node .= $char;
            }
        }
        $this->comment_nodes[] = $this->comment_node;
    }

    public function getChar()
    {
        return substr($this->comment, $this->position++, 1);
    }

    private function handleStack($stack)
    {
        $ds = [];
        do {
            $elem = array_pop($stack);
            if(preg_match('/^@/', $elem)) {
                $name = preg_replace('/^@/', '', $elem);
                break;
            }
            array_unshift($ds, $elem);
        } while(count($stack) > 0);
        $name = $name ?? 'description';
        $this->tags[$name] = implode('', $ds);
    }
}
