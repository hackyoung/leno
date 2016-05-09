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

    protected $token_tree = [];

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
        $this->getTokenTree($this->token_list);
    }

    protected function getToken()
    {
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
    }

    protected function getTokenTree($list)
    {
        $the_list = [];
        foreach($list as $k => $token) {
            if(true) {
                $name = preg_replace('/<|>/', '', $token);
                if($list[$k + 2] && $list[$k + 2] === '</'.$name.'>') {
                    $the_list[$name] = $list[$k + 1];
                    $list[$k + 1] = false;
                }
            } elseif($token !== false && !preg_match('/^<\/\w+/', $token)) {
                $the_list[] = $list[$k];
            }
        }
        return $the_list;
    }

    protected function getPare($list)
    {
        $ret = [];
        foreach($list as $k => $token) {
            if(preg_match('/^<\w+>/', $token)) {
                $name = preg_replace('/<|>/', '', $token);
                $idx = $this->findInArray($ret, $name);
                if($idx === false) {
                    $ret[] = [
                        'name' => $name,
                        'begin' => $k,
                    ];
                } else {

                }
            }
        }
        if($begin && $end) {
            return array_splice($list, $begin, $end - $begin + 1);
        }
    }

    protected function findInArray($array, $name)
    {
        $idx = false;
        foreach($array as $k=>$val) {
            if($val['name'] === $name) {
                $idx = $name;
            }   
        }
        return $idx;
    }

    protected function getChar()
    {
        return substr($this->input, $this->position++, 1);
    }
}
