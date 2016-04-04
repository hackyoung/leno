<?php
function camelCase($string, $hackFirst=true)
{
    $string = str_replace(' ', '', ucwords(
        str_replace('_', ' ', $string)
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

function unCamelCase($string)
{
    $string = preg_replace('/\B([A-Z])/', '_$1', $string);
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
