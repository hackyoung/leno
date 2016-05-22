<?php

function str_has_tags($string)
{
    return is_string($string)
        && strlen($string) > 2
        && $string !== strip_tags($string);
}

function camelCase($string, $hackFirst=true, $limiter='_')
{
    $string = str_replace(' ', '', ucwords(
        str_replace($limiter, ' ', $string)
    ));
    if($hackFirst) {
        return preg_replace_callback('/^\w/', function($matches) {
            if(isset($matches[0])) {
                return strtoupper($matches[0]);
            }
        }, $string);
    }
    return $string;
}

function unCamelCase($string, $limiter='_')
{
    $string = preg_replace('/\B([A-Z])/', $limiter.'$1', $string);
    return strtolower($string);
}

if(!function_exists('getallheaders')) {

    function getallheaders() {
        $headers = array(); 
        foreach ($_SERVER as $key => $value) { 
            if ('HTTP_' == substr($key, 0, 5)) { 
                $headers[str_replace('_', '-', substr($key, 5))] = $value; 
            } 
        }

        if (isset($_SERVER['PHP_AUTH_DIGEST'])) { 
            $header['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST']; 
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { 
            $header['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']); 
        } 
        if (isset($_SERVER['CONTENT_LENGTH'])) { 
            $header['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH']; 
        } 
        if (isset($_SERVER['CONTENT_TYPE'])) { 
            $header['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE']; 
        }

        return $headers;
    }
}

const RAND_MOD_ALL = 'all';
const RAND_MOD_ONLY_NUMBER = 'number';
const RAND_MOD_ONLY_LOWER = 'lower';
const RAND_MOD_ONLY_UPPER = 'upper';
const RAND_MOD_LETTER = 'letter';
const RAND_MOD_LOWER = 'lower_number';
const RAND_MOD_UPPER = 'upper_number';

function randString($len = 32, $mode = RAND_MOD_ALL)
{
    $number = '0123456789';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $upper = strtoupper($lower);
    switch($mode) {
        case RAND_MOD_ONLY_NUMBER:
            $template = $number;
            break;
        case RAND_MOD_ONLY_LOWER:
            $template = $lower;
            break;
        case RAND_MOD_ONLY_UPPER:
            $template = $upper;
            break;
        case RAND_MOD_LETTER:
            $template = $lower . $upper;
            break;
        case RAND_MOD_LOWER:
            $template = $number . $lower;
            break;
        case RAND_MOD_UPPER:
            $template = $number . $upper;
            break;
        case RAND_MOD_ALL:
            $template = $number . $upper . $lower;
            break;
        default:
            $template = $mode;
    }
    $tl = strlen($template);
    $ret = '';
    for($i = 0; $i < $len; ++$i) {
        $ret .= $template[rand(0, $tl-1)];
    }
    return $ret;
}

function uuid()
{
    $template = '0123456789abcdef';
    $arr = [
        randString(8, $template),
        randString(4, $template),
        randString(4, $template),
        randString(4, $template),
        randString(12, $template),
    ];
    return implode('-', $arr);
}
