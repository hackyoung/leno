<?php
namespace Leno\Console;

class Formatter
{
    protected $output;

    protected $handler;

    protected $position = 0;

    protected $input;

    protected $token;

    protected $token_list = [];

    protected $node_stack = [];

    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    public function format($input = null)
    {
        if($input) {
            $this->setInput($input);
        }
        $this->getToken();
        foreach($this->token_list as $token) {
            if($this->isNodeEnd($token)) {
                $name = $this->getNameFromToken($token);
                $children = [];
                do {
                    $elem = array_pop($this->node_stack);
                    $children[] = $elem;
                } while ( $elem instanceof \Leno\Console\Formatter\Node);
                $begin = array_pop($children);
                if($this->isNodeBegin($begin, $name)) {
                    $node = \Leno\Console\Formatter\Node::getNode($name);
                    while(count($children) > 0) {
                        $node->addChild(array_pop($children));
                    }
                    $this->node_stack[] = $node;
                }
            } elseif($this->isNodeBegin($token)) {
                $this->node_stack[] = $token;
            } else {
                $this->node_stack[] = new \Leno\Console\Formatter\Node\Text($token);
            }
        }
        $text = [];
        foreach($this->node_stack as $node) {
            $text[] = $node->format();
        }
        echo implode('', $text);
    }

    protected function isNodeEnd($token, $name = null)
    {
        if($name) {
            return $token === '</'.$name.'>';
        }
        return preg_match('/^<\/\w+>/', $token);
    }

    protected function isNodeBegin($token, $name = null)
    {
        if($name) {
            return $token === '<'.$name.'>';
        }
        return preg_match('/^<\w+>/', $token);
    }

    protected function getNameFromToken($token)
    {
        return preg_replace('/<|>|\//', '', $token);
    }

    protected function getToken()
    {
        $this->token_list[] = '<root>';
        while(($char = $this->getChar()) !== false) {
            switch($char) {
            case '<':
                $this->token_list[] = $this->token;
                $this->token = '<';
                break;
            case '>':
                $this->token_list[] = $this->token . '>';
                $this->token = '';
                break;
            default:
                $this->token .= $char;
            }
        }
        $this->token_list[] = $this->token;
        $this->token_list[] = '</root>';
    }

    protected function getChar()
    {
        return substr($this->input, $this->position++, 1);
    }
}
